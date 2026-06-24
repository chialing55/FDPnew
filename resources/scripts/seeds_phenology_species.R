#!/usr/bin/env Rscript

args <- commandArgs(trailingOnly = TRUE)
script_arg <- grep("^--file=", commandArgs(FALSE), value = TRUE)
script_path <- if (length(script_arg) > 0) sub("^--file=", "", script_arg[[1]]) else "resources/scripts/seeds_phenology_species.R"
source(file.path(dirname(normalizePath(script_path, mustWork = FALSE)), "research_chart_runtime.R"))

input_path <- research_arg_value(args, "input", required = TRUE)
pdf_path <- research_arg_value(args, "pdf", required = TRUE)
png_path <- research_arg_value(args, "png", required = TRUE)
font_path <- research_arg_value(args, "font", "")
research_load_style(
  "resources/scripts/seeds_phenology_species.R",
  list(cex = 0.9, axis = 0.9, axis_title = 0.9, legend = 0.78),
  list(portrait_width = 5.9, half_height = 4, png_res = 300)
)
research_require_packages(c("jsonlite", "showtext", "sysfonts"))

payload <- jsonlite::fromJSON(input_path, simplifyVector = FALSE)

axis_text_family <- research_setup_cjk_font(font_path)

num <- function(x) {
  if (is.null(x) || length(x) == 0 || is.na(x)) 0 else as.numeric(x)
}


y_label <- research_label_value(payload$y_label, "species count")
empty_label <- research_label_value(payload$empty_label, "no data")
legend_flower <- research_label_value(payload$legend$flower, "flower")
legend_fruit <- research_label_value(payload$legend$fruit, "fruit")

rows <- payload$rows
if (length(rows) == 0) {
  dat <- data.frame(date = as.Date(character()), flower = numeric(), fruit = numeric())
} else {
  dat <- data.frame(
    date = as.Date(vapply(rows, function(row) as.character(row$date), character(1))),
    flower = vapply(rows, function(row) num(row$flower), numeric(1)),
    fruit = vapply(rows, function(row) num(row$fruit), numeric(1)),
    stringsAsFactors = FALSE
  )
  dat <- dat[order(dat$date), ]
}

month_ticks <- function(dates) {
  start_date <- as.Date(format(min(dates, na.rm = TRUE), "%Y-%m-01"))
  end_date <- as.Date(format(max(dates, na.rm = TRUE), "%Y-%m-01"))
  seq(start_date, end_date, by = "months")
}

plot_all <- function() {
  par(family = axis_text_family, mar = c(6, 4, 2, 1), cex = research_chart_style$cex)

  if (nrow(dat) == 0) {
    plot.new()
    text(0.5, 0.5, empty_label, cex = research_chart_style$cex, family = axis_text_family)
    return(invisible(NULL))
  }

  y_values <- c(dat$flower, dat$fruit)
  y_min <- min(y_values, na.rm = TRUE)
  y_max <- max(y_values, na.rm = TRUE)
  if (!is.finite(y_min) || !is.finite(y_max)) {
    y_min <- 0
    y_max <- 1
  }
  if (y_min == y_max) {
    y_min <- max(0, y_min - 1)
    y_max <- y_max + 1
  }

  plot(
    flower ~ date,
    data = dat,
    type = "o",
    las = 1,
    pch = 16,
    axes = FALSE,
    xlim = c(min(dat$date) - 10, max(dat$date) + 10),
    ylim = c(y_min, y_max),
    ylab = "",
    xlab = "",
    family = axis_text_family
  )
  lines(fruit ~ date, data = dat, lty = 2, type = "o", pch = 1)
  axis(2, las = 1, cex.axis = research_chart_style$axis, family = axis_text_family)
  usr <- par("usr")
  x_span <- usr[2] - usr[1]
  y_span <- usr[4] - usr[3]
  text(usr[1] - 0.11 * x_span, mean(usr[3:4]), labels = y_label, srt = 90, xpd = NA, cex = research_chart_style$axis_title, family = axis_text_family)
  box()

  legend_y <- usr[4] + 0.07 * y_span
  flower_x1 <- usr[1] + 0.38 * x_span
  flower_x2 <- usr[1] + 0.44 * x_span
  fruit_x1 <- usr[1] + 0.50 * x_span
  fruit_x2 <- usr[1] + 0.56 * x_span
  segments(flower_x1, legend_y, flower_x2, legend_y, lty = 1, xpd = NA)
  points(mean(c(flower_x1, flower_x2)), legend_y, pch = 16, xpd = NA)
  text(flower_x2 + 0.025 * x_span, legend_y, labels = legend_flower, adj = 0, xpd = NA, cex = research_chart_style$legend, family = axis_text_family)
  segments(fruit_x1, legend_y, fruit_x2, legend_y, lty = 2, xpd = NA)
  points(mean(c(fruit_x1, fruit_x2)), legend_y, pch = 1, xpd = NA)
  text(fruit_x2 + 0.025 * x_span, legend_y, labels = legend_fruit, adj = 0, xpd = NA, cex = research_chart_style$legend, family = axis_text_family)

  dates <- month_ticks(dat$date)
  axis(1, at = dates, labels = format(dates, "%Y-%m-%d"), las = 2, cex.axis = research_chart_style$axis, family = axis_text_family)
}

draw_output <- function() {
  showtext::showtext_begin()
  plot_all()
  showtext::showtext_end()
}

grDevices::pdf(pdf_path, width = research_chart_device$portrait_width, height = research_chart_device$half_height, onefile = TRUE, family = "sans")
draw_output()
dev.off()

png(png_path, width = research_chart_device$portrait_width * research_chart_device$png_res, height = research_chart_device$half_height * research_chart_device$png_res, res = research_chart_device$png_res, type = "cairo")
draw_output()
dev.off()
