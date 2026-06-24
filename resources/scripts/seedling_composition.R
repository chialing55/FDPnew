#!/usr/bin/env Rscript
suppressPackageStartupMessages({
  library(jsonlite)
  library(showtext)
  library(sysfonts)
})

args <- commandArgs(trailingOnly = TRUE)
arg_value <- function(name, default = NULL) {
  hit <- which(args == name)
  if (length(hit) == 0 || hit[1] == length(args)) return(default)
  args[hit[1] + 1]
}

input <- arg_value('--input')
outdir <- arg_value('--outdir')
font_path <- arg_value('--font')
times_path <- arg_value('--times')

if (is.null(input) || is.null(outdir)) stop('Missing --input or --outdir')
if (!dir.exists(outdir)) dir.create(outdir, recursive = TRUE, showWarnings = FALSE)

if (!is.null(font_path) && file.exists(font_path)) {
  font_add('msjh', regular = font_path)
}
if (!is.null(times_path) && file.exists(times_path)) {
  font_add('times', regular = times_path)
}
showtext_auto(FALSE)

payload <- fromJSON(input, simplifyVector = FALSE)
plots <- payload$plots

plot_one <- function(plot, file, device = c('png', 'pdf')) {
  device <- match.arg(device)
  rows <- plot$rows
  if (length(rows) == 0) return(FALSE)

  data <- data.frame(
    csp = vapply(rows, function(x) x$csp, character(1)),
    alive = vapply(rows, function(x) as.numeric(x$alive), numeric(1)),
    survive = vapply(rows, function(x) as.numeric(x$survive), numeric(1)),
    recruit = vapply(rows, function(x) as.numeric(x$recruit), numeric(1)),
    dead = vapply(rows, function(x) as.numeric(x$dead), numeric(1)),
    stringsAsFactors = FALSE
  )
  data <- data[!toupper(trimws(data$csp)) %in% c('UNK', ''), , drop = FALSE]
  data <- data[order(data$alive), , drop = FALSE]
  data$zero <- 0

  # Keep the high-count dominant species readable by truncating only the plotted bar.
  max_positive <- 150
  data$survive_plot <- pmin(data$survive, max_positive)
  data$recruit_plot <- pmin(data$recruit, pmax(0, max_positive - data$survive_plot))
  dead_cap <- 55
  data$dead_plot <- -pmin(data$dead, dead_cap)
  data$positive_truncated <- (data$survive + data$recruit) > max_positive
  data$dead_truncated <- data$dead > dead_cap


  if (!is.null(plot$layout) && plot$layout %in% c('long', 'focus-standard')) {
    focus_name <- plot$focus_species %||% '大明橘'
    focus <- data[data$csp == focus_name, , drop = FALSE]
    main <- data[data$csp != focus_name, , drop = FALSE]
    if (nrow(main) == 0) main <- data

    labels <- plot$legend %||% list(survive = '存活舊苗', recruit = '新增苗', dead = '死亡苗')
    font_family <- if (!is.null(font_path) && file.exists(font_path)) 'msjh' else ''

    xlim_for <- function(panel_data, min_positive = 150) {
      max_dead <- max(panel_data$dead, 50, na.rm = TRUE)
      max_positive_value <- max(panel_data$survive + panel_data$recruit, min_positive, na.rm = TRUE)
      c(-ceiling(max_dead / 50) * 50, ceiling(max_positive_value / 50) * 50)
    }

    draw_legend <- function(usr, y_fraction = 0.58) {
      legend_x <- usr[2] - 45
      legend_y <- usr[3] + (usr[4] - usr[3]) * y_fraction
      box_h <- 1.15
      box_w <- 6
      text_x <- legend_x + 8
      text_cex <- 0.78
      rect(legend_x, legend_y - box_h / 2, legend_x + box_w, legend_y + box_h / 2, col = 'white', border = 'black')
      text(text_x, legend_y, labels$survive %||% '存活舊苗', adj = c(0, 0.5), cex = text_cex, family = font_family)
      legend_y <- legend_y - 2.4
      rect(legend_x, legend_y - box_h / 2, legend_x + box_w, legend_y + box_h / 2, col = 'grey80', border = 'black', density = 500)
      text(text_x, legend_y, labels$recruit %||% '新增苗', adj = c(0, 0.5), cex = text_cex, family = font_family)
      legend_y <- legend_y - 2.4
      rect(legend_x, legend_y - box_h / 2, legend_x + box_w, legend_y + box_h / 2, col = 'grey90', border = 'black', density = 30)
      text(text_x, legend_y, labels$dead %||% '死亡苗', adj = c(0, 0.5), cex = text_cex, family = font_family)
    }

    draw_panel <- function(panel_data, xlim, ylim = NULL, axis_line = -0.5, show_axis = TRUE, show_x_label = TRUE, show_legend = FALSE, show_panel_label = FALSE, cex_names = 0.78) {
      bp <- barplot(t(as.matrix(panel_data[, c('survive', 'recruit', 'zero')])),
              density = c(0, 500, 30), space = 1, beside = FALSE, horiz = TRUE, las = 1,
              xlim = xlim, ylim = ylim, xlab = '', names.arg = panel_data$csp, axes = FALSE, cex.names = cex_names,
              col = c('white', 'grey80', 'white'), border = 'black')
      barplot(-panel_data$dead, density = 30, space = 1, horiz = TRUE, add = TRUE,
              xlim = xlim, ylim = ylim, axes = FALSE, col = 'grey90', border = 'black')
      if (show_axis) axis(1, line = axis_line, cex.axis = 0.9)
      lines(rbind(c(0, -0.5), c(0, max(bp) + 1)))
      if (show_x_label) mtext(plot$x_label %||% '小苗個體數', 1, line = 2, cex = 0.9)
      if (show_panel_label && !is.null(plot$panel_label) && nzchar(plot$panel_label)) {
        usr <- par('usr')
        label_y <- usr[4] - 0.08 * (usr[4] - usr[3])
        text(grconvertX(0.06, from = 'ndc', to = 'user'), grconvertY(grconvertY(label_y, from = 'user', to = 'ndc') - 0.03, from = 'ndc', to = 'user'), labels = plot$panel_label, adj = c(0, 1), cex = 0.9, xpd = NA, family = font_family)
      }
      if (show_legend) draw_legend(par('usr'))
    }

    is_standard_size <- plot$layout == 'focus-standard'
    if (device == 'png') {
      png(file, width = if (is_standard_size) 8 else 6, height = if (is_standard_size) 6 else 8, units = 'in', res = 180, type = 'cairo')
    } else {
      pdf(file, width = if (is_standard_size) 8 else 6, height = if (is_standard_size) 6 else 8)
    }
    showtext_begin()
    par(family = font_family, cex = 0.9, xpd = FALSE)
    layout(matrix(c(1, 2), ncol = 1), heights = if (is_standard_size) c(1, 4) else c(1, 7))

    if (nrow(focus) > 0) {
      par(mar = c(1.8, 7, 1, 1))
      focus_ylim <- if (is_standard_size) c(0, max(4, (nrow(main) * 1.2 + 1) / 4)) else c(0, 4)
      draw_panel(focus, xlim_for(focus), ylim = focus_ylim, axis_line = 0.5, show_axis = TRUE, show_x_label = FALSE, show_legend = FALSE, show_panel_label = TRUE, cex_names = 0.72)
    } else {
      par(mar = c(0, 7, 1, 1))
      plot.new()
    }

    par(mar = c(4, 7, 0.5, 1))
    draw_panel(main, xlim_for(main), show_axis = TRUE, show_x_label = TRUE, show_legend = TRUE, cex_names = 0.72)

    showtext_end()
    dev.off()
    return(TRUE)
  }

  if (device == 'png') {
    png(file, width = 8, height = 6, units = 'in', res = 180, type = 'cairo')
  } else {
    pdf(file, width = 8, height = 6)
  }
  showtext_begin()
  par(family = if (!is.null(font_path) && file.exists(font_path)) 'msjh' else '', mar = c(4, 7, 1, 1), cex = 0.9, xpd = FALSE)

  bp <- barplot(t(as.matrix(data[, c('survive_plot', 'recruit_plot', 'zero')])),
          density = c(0, 500, 30), space = 1, beside = FALSE, horiz = TRUE, las = 1,
          xlim = c(-50, 150), xlab = '', names.arg = data$csp, axes = FALSE, cex.names = 0.72,
          col = c('white', 'grey80', 'white'), border = 'black')
  barplot(data$dead_plot, density = 30, space = 1, horiz = TRUE, add = TRUE,
          xlim = c(-50, 150), axes = FALSE, col = 'grey90', border = 'black')
  if (any(data$positive_truncated)) {
    segments(max_positive, bp[data$positive_truncated] - 0.5, max_positive, bp[data$positive_truncated] + 0.5, col = 'white', lwd = 2, xpd = FALSE)
  }
  if (any(data$dead_truncated)) {
    segments(-dead_cap, bp[data$dead_truncated] - 0.5, -dead_cap, bp[data$dead_truncated] + 0.5, col = 'white', lwd = 2, xpd = FALSE)
  }
  axis(1, line = -0.5, cex.axis = 0.9)
  lines(rbind(c(0, -0.5), c(0, max(bp) + 1)))
  labels <- plot$legend %||% list(survive = '存活舊苗', recruit = '新增苗', dead = '死亡苗')
  mtext(plot$x_label %||% '小苗個體數', 1, line = 2, cex = 0.9)
  mtext(plot$panel_label, 3, at = par('usr')[1] - 25, line = -0.1, adj = 0, cex = 0.9)

  usr <- par('usr')
  legend_x <- usr[2] - 25
  legend_y <- usr[3] + (usr[4] - usr[3]) * 0.58
  box_h <- 1.15
  box_w <- 6
  text_x <- legend_x + 8
  text_cex <- 0.78
  rect(legend_x, legend_y - box_h / 2, legend_x + box_w, legend_y + box_h / 2, col = 'white', border = 'black')
  text(text_x, legend_y, labels$survive %||% '存活舊苗', adj = c(0, 0.5), cex = text_cex, family = if (!is.null(font_path) && file.exists(font_path)) 'msjh' else '')
  legend_y <- legend_y - 2.4
  rect(legend_x, legend_y - box_h / 2, legend_x + box_w, legend_y + box_h / 2, col = 'grey80', border = 'black', density = 500)
  text(text_x, legend_y, labels$recruit %||% '新增苗', adj = c(0, 0.5), cex = text_cex, family = if (!is.null(font_path) && file.exists(font_path)) 'msjh' else '')
  legend_y <- legend_y - 2.4
  rect(legend_x, legend_y - box_h / 2, legend_x + box_w, legend_y + box_h / 2, col = 'grey90', border = 'black', density = 30)
  text(text_x, legend_y, labels$dead %||% '死亡苗', adj = c(0, 0.5), cex = text_cex, family = if (!is.null(font_path) && file.exists(font_path)) 'msjh' else '')
  showtext_end()
  dev.off()
  TRUE
}

`%||%` <- function(x, y) if (is.null(x)) y else x

for (plot in plots) {
  plot_one(plot, file.path(outdir, paste0(plot$file_base, '.png')), 'png')
  plot_one(plot, file.path(outdir, paste0(plot$file_base, '.pdf')), 'pdf')
}
