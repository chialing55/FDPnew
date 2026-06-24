#!/usr/bin/env Rscript

args <- commandArgs(trailingOnly = TRUE)
script_arg <- grep("^--file=", commandArgs(FALSE), value = TRUE)
script_path <- if (length(script_arg) > 0) sub("^--file=", "", script_arg[[1]]) else "resources/scripts/seeds_spatial_species_traps.R"
source(file.path(dirname(normalizePath(script_path, mustWork = FALSE)), "research_chart_runtime.R"))

input_path <- research_arg_value(args, "input", required = TRUE)
pdf_path <- research_arg_value(args, "pdf", required = TRUE)
png_path <- research_arg_value(args, "png", required = TRUE)
font_path <- research_arg_value(args, "font", "")
research_load_style(
  "resources/scripts/seeds_spatial_species_traps.R",
  list(cex = 0.9, axis = 0.9, axis_title = 0.9, species_name_dense = 0.58, dense_axis = 0.78, dense_axis_title = 0.78, legend = 0.78),
  list(portrait_width = 5.9, portrait_height = 8, png_res = 300)
)
research_require_packages(c("jsonlite", "showtext", "sysfonts"))

payload <- jsonlite::fromJSON(input_path, simplifyVector = FALSE)

axis_text_family <- research_setup_cjk_font(font_path)

num <- function(x) {
  if (is.null(x) || length(x) == 0 || is.na(x)) 0 else as.numeric(x)
}

x_label <- research_label_value(payload$labels$x, "trap count")
fruit_label <- research_label_value(payload$labels$fruit, "fruit and seed")
flower_label <- research_label_value(payload$labels$flower, "flower")
empty_label <- research_label_value(payload$labels$empty, "no data")
rows <- payload$rows

if (length(rows) == 0) {
  dat <- data.frame(csp = character(), fruit = numeric(), flower = numeric())
} else {
  dat <- data.frame(
    csp = vapply(rows, function(row) research_label_value(row$csp, ""), character(1)),
    fruit = vapply(rows, function(row) num(row$fruit), numeric(1)),
    flower = vapply(rows, function(row) num(row$flower), numeric(1)),
    stringsAsFactors = FALSE
  )
}

plot_all <- function() {
  par(family = axis_text_family, mar = c(3, 6, 2, 1), cex = research_chart_style$cex, xpd = NA)

  if (nrow(dat) == 0) {
    plot.new()
    text(0.5, 0.5, empty_label, family = axis_text_family)
    return(invisible(NULL))
  }

  values <- t(as.matrix(dat[, c("fruit", "flower")]))
  max_value <- max(values, na.rm = TRUE)
  x_ticks <- if (is.finite(max_value) && max_value > 0) pretty(c(0, max_value * 1.05)) else c(0, 1)
  x_ticks <- x_ticks[x_ticks >= 0]
  x_max <- max(x_ticks)

  mids <- barplot(
    values,
    beside = TRUE,
    horiz = TRUE,
    las = 1,
    xlim = c(0, x_max),
    col = c("grey", "black"),
    xaxt = "n",
    names.arg = dat$csp,
    cex.names = research_chart_style$species_name_dense,
    family = axis_text_family
  )
  lines(rbind(c(0, min(mids) - 0.7), c(0, max(mids) + 0.7)))
  axis(1, line = -1.2, cex.axis = research_chart_style$dense_axis, family = axis_text_family)
  mtext(x_label, 1, line = 1, cex = research_chart_style$dense_axis_title, family = axis_text_family)

  usr <- par("usr")
  legend_x <- usr[1] + 0.60 * (usr[2] - usr[1])
  legend_y <- usr[3] + 0.48 * (usr[4] - usr[3])
  legend(
    legend_x,
    legend_y,
    legend = c(fruit_label, flower_label),
    fill = c("grey", "black"),
    bty = "n",
    xpd = NA,
    cex = research_chart_style$legend
  )
}

draw_output <- function() {
  showtext::showtext_begin()
  plot_all()
  showtext::showtext_end()
}

grDevices::pdf(pdf_path, width = research_chart_device$portrait_width, height = research_chart_device$portrait_height, onefile = TRUE, family = "sans")
draw_output()
dev.off()

png(png_path, width = research_chart_device$portrait_width * research_chart_device$png_res, height = research_chart_device$portrait_height * research_chart_device$png_res, res = research_chart_device$png_res, type = "cairo")
draw_output()
dev.off()
