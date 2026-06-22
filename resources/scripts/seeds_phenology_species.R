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
axis_text_family <- "cjk"
showtext::showtext_opts(dpi = 300)
showtext::showtext_auto()

num <- function(x) {
  if (is.null(x) || length(x) == 0 || is.na(x)) 0 else as.numeric(x)
}


label_value <- function(value, fallback) {
  if (is.null(value) || length(value) == 0 || is.na(value)) {
    return(enc2utf8(fallback))
  }

  enc2utf8(as.character(value))
}

y_label <- label_value(payload$y_label, "species count")
empty_label <- label_value(payload$empty_label, "no data")
legend_flower <- label_value(payload$legend$flower, "flower")
legend_fruit <- label_value(payload$legend$fruit, "fruit")

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
  par(family = axis_text_family, mar = c(6, 4, 2, 1))

  if (nrow(dat) == 0) {
    plot.new()
    text(0.5, 0.5, empty_label, family = axis_text_family)
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
  axis(2, las = 1, family = axis_text_family)
  usr <- par("usr")
  x_span <- usr[2] - usr[1]
  y_span <- usr[4] - usr[3]
  text(usr[1] - 0.11 * x_span, mean(usr[3:4]), labels = y_label, srt = 90, xpd = NA, family = axis_text_family)
  box()

  legend_y <- usr[4] + 0.07 * y_span
  flower_x1 <- usr[1] + 0.38 * x_span
  flower_x2 <- usr[1] + 0.44 * x_span
  fruit_x1 <- usr[1] + 0.50 * x_span
  fruit_x2 <- usr[1] + 0.56 * x_span
  segments(flower_x1, legend_y, flower_x2, legend_y, lty = 1, xpd = NA)
  points(mean(c(flower_x1, flower_x2)), legend_y, pch = 16, xpd = NA)
  text(flower_x2 + 0.025 * x_span, legend_y, labels = legend_flower, adj = 0, xpd = NA, family = axis_text_family)
  segments(fruit_x1, legend_y, fruit_x2, legend_y, lty = 2, xpd = NA)
  points(mean(c(fruit_x1, fruit_x2)), legend_y, pch = 1, xpd = NA)
  text(fruit_x2 + 0.025 * x_span, legend_y, labels = legend_fruit, adj = 0, xpd = NA, family = axis_text_family)

  dates <- month_ticks(dat$date)
  axis(1, at = dates, labels = format(dates, "%Y-%m-%d"), las = 2, family = axis_text_family)
}

draw_output <- function() {
  showtext::showtext_begin()
  plot_all()
  showtext::showtext_end()
}

grDevices::pdf(pdf_path, width = 5.9, height = 4, onefile = TRUE, family = "sans")
draw_output()
dev.off()

png(png_path, width = 1770, height = 1200, res = 300, type = "cairo")
draw_output()
dev.off()
