#!/usr/bin/env Rscript
suppressPackageStartupMessages({
  library(jsonlite)
  library(showtext)
  library(sysfonts)
})
args <- commandArgs(trailingOnly = TRUE)
script_arg <- grep("^--file=", commandArgs(FALSE), value = TRUE)
script_path <- if (length(script_arg) > 0) sub("^--file=", "", script_arg[[1]]) else "resources/scripts/seedling_growth_histograms.R"
source(file.path(dirname(normalizePath(script_path, mustWork = FALSE)), "research_chart_runtime.R"))

input <- research_arg_value(args, "input", required = TRUE)
outdir <- research_arg_value(args, "outdir", required = TRUE)
font_path <- research_arg_value(args, "font")
times_path <- research_arg_value(args, "times")

research_load_style(
  "resources/scripts/seedling_growth_histograms.R",
  list(cex = 0.9, small_multiple_axis = 0.9, small_multiple_label = 0.9, small_multiple_title = 0.9),
  list(wide_width = 8)
)

if (!dir.exists(outdir)) dir.create(outdir, recursive = TRUE, showWarnings = FALSE)
research_setup_msjh_font(font_path, times_path)
payload <- fromJSON(input, simplifyVector = FALSE)
plots <- payload$plots
plot_one <- function(plot, file, device = c('png','pdf')) {
  device <- match.arg(device)
  panels <- plot$panels
  if (length(panels) == 0) return(FALSE)
  panel_count <- length(panels)
  columns <- if (panel_count == 1) 1 else 2
  rows <- min(8, ceiling(panel_count / columns))
  device_height <- if (rows >= 8) 12 else max(3.2, rows * 1.35 + 1.3)
  if (device == 'png') {
    png(file, width = research_chart_device$wide_width, height = device_height, units = 'in', res = 180, type = 'cairo')
  } else {
    pdf(file, width = research_chart_device$wide_width, height = device_height)
  }
  font_family <- if (!is.null(font_path) && file.exists(font_path)) 'msjh' else ''
  showtext_begin()
  par(family = font_family, mfrow = c(rows, columns), mar = c(2, 3, 1, 1), oma = c(3, 3, 1, 1), las = 1, cex = research_chart_style$cex)
  for (i in seq_along(panels)) {
    panel <- panels[[i]]
    values <- as.numeric(unlist(panel$values))
    values <- values[!is.na(values)]
    x_min <- plot$x_min %||% -60
    x_max <- plot$x_max %||% 80
    x_tick_min <- plot$x_tick_min %||% -60
    x_tick_by <- plot$x_tick_by %||% 20
    break_by <- plot$break_by %||% 5
    breaks <- seq(x_min, x_max, break_by)
    plot_values <- values[values >= x_min & values <= x_max]
    if (length(plot_values) > 0) {
      hist(plot_values, axes = FALSE, ylab = '', xlab = '', main = '',
           xlim = c(x_min, x_max), breaks = breaks,
           col = 'white', border = 'black')
    } else {
      plot.new()
      plot.window(xlim = c(x_min, x_max), ylim = c(0, 1))
    }
    legend('topright', panel$csp, bty = 'n', cex = research_chart_style$small_multiple_title)
    ticks <- seq(x_tick_min, x_max, x_tick_by)
    axis(1, at = ticks, labels = ticks, cex.axis = research_chart_style$small_multiple_axis, family = font_family)
    axis(2, las = 1, cex.axis = research_chart_style$small_multiple_axis, family = font_family)
  }
  empty_panels <- rows * columns - panel_count
  if (empty_panels > 0) {
    for (i in seq_len(empty_panels)) plot.new()
  }
  mtext(plot$y_label %||% '個體數', 2, line = 1, cex = research_chart_style$small_multiple_label, outer = TRUE, las = 0, family = font_family)
  mtext(plot$x_label %||% '生長率 (cm/year)', 1, line = 1, cex = research_chart_style$small_multiple_label, outer = TRUE, family = font_family)
  showtext_end()
  dev.off()
  TRUE
}
for (plot in plots) {
  plot_one(plot, file.path(outdir, paste0(plot$file_base, '.png')), 'png')
  plot_one(plot, file.path(outdir, paste0(plot$file_base, '.pdf')), 'pdf')
}
