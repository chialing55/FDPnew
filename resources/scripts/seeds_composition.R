#!/usr/bin/env Rscript

args <- commandArgs(trailingOnly = TRUE)
arg_value <- function(name) {
  key <- paste0("--", name)
  idx <- match(key, args)
  if (is.na(idx) || idx == length(args)) {
    stop(paste("Missing argument", key), call. = FALSE)
  }
  args[[idx + 1]]
}

input_path <- arg_value("input")
pdf_path <- arg_value("pdf")
png_path <- arg_value("png")
font_path <- if ("--font" %in% args) arg_value("font") else ""
times_path <- if ("--times" %in% args) arg_value("times") else ""

if (!requireNamespace("jsonlite", quietly = TRUE)) {
  stop("R package jsonlite is required", call. = FALSE)
}
if (!requireNamespace("showtext", quietly = TRUE)) {
  stop("R package showtext is required", call. = FALSE)
}
if (!requireNamespace("sysfonts", quietly = TRUE)) {
  stop("R package sysfonts is required", call. = FALSE)
}

payload <- jsonlite::fromJSON(input_path, simplifyVector = FALSE)

if (!nzchar(font_path) || !file.exists(font_path)) {
  stop("Chinese font file is required for chart output.", call. = FALSE)
}

sysfonts::font_add("cjk", regular = font_path)
if (nzchar(times_path) && file.exists(times_path)) {
  sysfonts::font_add("times", regular = times_path)
  number_family <- "cjk"
} else {
  number_family <- "cjk"
}
axis_text_family <- "cjk"
showtext::showtext_opts(dpi = 300)
showtext::showtext_auto()

num <- function(x) {
  if (is.null(x) || length(x) == 0 || is.na(x)) 0 else as.numeric(x)
}

panel_data <- function(panel) {
  rows <- panel$rows
  if (length(rows) == 0) {
    return(data.frame(label = character(), value = numeric()))
  }
  dat <- data.frame(
    label = vapply(rows, function(row) as.character(row$label), character(1)),
    value = vapply(rows, function(row) num(row$value), numeric(1)),
    stringsAsFactors = FALSE
  )

  pad_to <- if (!is.null(panel$pad_to)) as.integer(panel$pad_to) else nrow(dat)
  if (!is.na(pad_to) && pad_to > nrow(dat)) {
    dat <- rbind(
      dat,
      data.frame(label = rep("", pad_to - nrow(dat)), value = rep(0, pad_to - nrow(dat)), stringsAsFactors = FALSE)
    )
  }

  dat
}

flower_limit <- function(max_value) {
  upper <- ceiling(max_value / 100) * 100
  max(upper, 100)
}

log_limit <- function(max_value) {
  max(1500, 10 ^ ceiling(log10(max(max_value, 1))))
}

log_ticks <- function(limit) {
  if (limit <= 1500) {
    return(c(1, 5, 10, 50, 100, 500, 1000))
  }

  10 ^ seq(0, ceiling(log10(limit)))
}

line_ymax <- function(n) {
  # Matches the long left-axis line in the original plotting script.
  max(61, n * 2 + 1)
}

plot_panel <- function(panel, log_axis = FALSE, flower_axis = FALSE, title_at = NULL, title_adj = NA, title_offset = 0, title_offset_ratio = 0, mar = c(4, 6, 2, 1)) {
  par(mar = mar)
  dat <- panel_data(panel)
  tag <- if (!is.null(panel$title)) as.character(panel$title) else ""
  x_label <- if (!is.null(panel$x_label)) as.character(panel$x_label) else ""

  if (nrow(dat) == 0) {
    plot.new()
    mtext(tag, 3, at = par("usr")[1], line = 0.5, family = axis_text_family)
    text(0.5, 0.5, "無資料", family = axis_text_family)
    return(invisible(NULL))
  }

  values <- rev(as.numeric(dat$value))
  labels <- rev(as.character(dat$label))

  if (log_axis) {
    plot_values <- pmax(values, 1)
    log_x_max <- log_limit(max(values))
    barplot(
      plot_values,
      space = 1,
      beside = FALSE,
      horiz = TRUE,
      las = 1,
      log = "x",
      xlim = c(1, log_x_max),
      xlab = "",
      names.arg = labels,
      axes = FALSE,
      col = "#c9c9c9",
      border = "#222222",
      family = axis_text_family
    )
    ticks <- log_ticks(log_x_max)
    axis(1, at = ticks, labels = format(ticks, scientific = FALSE), line = -0.5, cex.axis = 1, family = axis_text_family)
    lines(rbind(c(1, -0.5), c(1, 61)))
    mtext(x_label, 1, line = 2.5, family = axis_text_family)
    title_pos <- (if (is.null(title_at)) 0.2 else title_at) + title_offset
    mtext(tag, 3, at = title_pos, line = 0.5, adj = title_adj, family = axis_text_family)
  } else {
    x_max <- if (flower_axis) flower_limit(max(values)) else max(pretty(c(0, max(values))))
    barplot(
      values,
      space = 1,
      beside = FALSE,
      horiz = TRUE,
      las = 1,
      xlim = c(0, x_max),
      xlab = "",
      names.arg = labels,
      axes = FALSE,
      col = "#c9c9c9",
      border = "#222222",
      family = axis_text_family
    )
    axis(1, line = -0.5, cex.axis = 1, family = axis_text_family)
    lines(rbind(c(0, -0.5), c(0, line_ymax(length(values)))))
    mtext(x_label, 1, line = 2.5, family = axis_text_family)
    title_pos <- (if (is.null(title_at)) -0.13 * x_max else title_at) + title_offset + title_offset_ratio * x_max
    mtext(tag, 3, at = title_pos, line = 0.5, adj = title_adj, family = axis_text_family)
  }
}

plot_all <- function() {
  par(family = axis_text_family)
  layout(matrix(c(1, 1, 2, 3), 2, 2, byrow = TRUE))
  plot_panel(payload$flower, FALSE, TRUE, mar = c(4, 6, 2, 1))
  plot_panel(payload$fruit, TRUE, FALSE, mar = c(4, 6, 2, 1))
  plot_panel(payload$seed, FALSE, FALSE, title_offset_ratio = -0.18, mar = c(4, 6, 2, 1))
}

draw_output <- function() {
  showtext::showtext_begin()
  plot_all()
  showtext::showtext_end()
}

grDevices::pdf(pdf_path, width = 5.9, height = 8, onefile = TRUE, family = "sans")
draw_output()
dev.off()

png(png_path, width = 1770, height = 2400, res = 300, type = "cairo")
draw_output()
dev.off()
