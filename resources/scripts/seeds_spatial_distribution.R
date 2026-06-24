#!/usr/bin/env Rscript

args <- commandArgs(trailingOnly = TRUE)
script_arg <- grep("^--file=", commandArgs(FALSE), value = TRUE)
script_path <- if (length(script_arg) > 0) sub("^--file=", "", script_arg[[1]]) else "resources/scripts/seeds_spatial_distribution.R"
source(file.path(dirname(normalizePath(script_path, mustWork = FALSE)), "research_chart_runtime.R"))

input_path <- research_arg_value(args, "input", required = TRUE)
pdf_path <- research_arg_value(args, "pdf", required = TRUE)
png_path <- research_arg_value(args, "png", required = TRUE)
font_path <- research_arg_value(args, "font", "")
research_load_style(
  "resources/scripts/seeds_spatial_distribution.R",
  list(cex = 0.9, axis = 0.9, axis_title = 0.9, panel_label = 0.9, panel_label_ndc_x = 0.06, panel_label_top_offset = 0.08),
  list(portrait_width = 5.9, portrait_height = 8, png_res = 300)
)
research_require_packages(c("jsonlite", "showtext", "sysfonts"))

payload <- jsonlite::fromJSON(input_path, simplifyVector = FALSE)

axis_text_family <- research_setup_cjk_font(font_path)

num <- function(x) {
  if (is.null(x) || length(x) == 0 || is.na(x)) 0 else as.numeric(x)
}

y_label <- research_label_value(payload$labels$y, "trap count")
x_label <- research_label_value(payload$labels$x, "species count")
flower_title <- research_label_value(payload$labels$flower_title, "(a) flower")
fruit_title <- research_label_value(payload$labels$fruit_title, "(b) fruit and seed")
empty_label <- research_label_value(payload$labels$empty, "no data")
trap_total <- as.integer(num(payload$trap_total))

trap_counts <- function(rows) {
  counts <- integer(0)
  if (length(rows) > 0) {
    counts <- vapply(rows, function(row) as.integer(num(row$species_count)), integer(1))
  }

  missing <- max(trap_total - length(counts), 0)
  c(counts, rep(0L, missing))
}

frequency_table <- function(counts) {
  if (length(counts) == 0) {
    return(c(`0` = 0))
  }

  max_count <- max(counts, na.rm = TRUE)
  tab <- tabulate(counts + 1L, nbins = max_count + 1L)
  names(tab) <- as.character(0:max_count)
  tab
}

plot_panel <- function(counts, title) {
  tab <- frequency_table(counts)
  if (sum(tab) == 0) {
    plot.new()
    text(0.5, 0.5, empty_label, cex = research_chart_style$cex, family = axis_text_family)
    return(invisible(NULL))
  }

  barplot(
    tab,
    space = 1,
    las = 1,
    ylab = "",
    xlab = "",
    col = "#c9c9c9",
    border = "#222222",
    cex.axis = research_chart_style$axis,
    cex.names = research_chart_style$axis,
    family = axis_text_family
  )
  abline(0, 0)
  mtext(y_label, 2, line = 2.5, cex = research_chart_style$axis_title, family = axis_text_family)
  mtext(x_label, 1, line = 2.8, cex = research_chart_style$axis_title, family = axis_text_family)
  usr <- par("usr")
  label_x <- grconvertX(research_chart_style$panel_label_ndc_x, from = "ndc", to = "user")
  label_y <- usr[4] + research_chart_style$panel_label_top_offset * (usr[4] - usr[3])
  text(label_x, label_y, labels = title, adj = 0, xpd = NA, cex = research_chart_style$panel_label, family = axis_text_family)
}

plot_all <- function() {
  par(family = axis_text_family, mfcol = c(2, 1), mar = c(5, 4, 2, 1), cex = research_chart_style$cex)
  plot_panel(trap_counts(payload$flower_traps), flower_title)
  plot_panel(trap_counts(payload$fruit_traps), fruit_title)
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
