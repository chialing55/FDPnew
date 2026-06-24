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

x_label <- label_value(payload$labels$x, "trap count")
fruit_label <- label_value(payload$labels$fruit, "fruit and seed")
flower_label <- label_value(payload$labels$flower, "flower")
empty_label <- label_value(payload$labels$empty, "no data")
rows <- payload$rows

if (length(rows) == 0) {
  dat <- data.frame(csp = character(), fruit = numeric(), flower = numeric())
} else {
  dat <- data.frame(
    csp = vapply(rows, function(row) label_value(row$csp, ""), character(1)),
    fruit = vapply(rows, function(row) num(row$fruit), numeric(1)),
    flower = vapply(rows, function(row) num(row$flower), numeric(1)),
    stringsAsFactors = FALSE
  )
}

plot_all <- function() {
  par(family = axis_text_family, mar = c(3, 6, 2, 1), xpd = NA)

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
    cex.names = 0.58,
    family = axis_text_family
  )
  lines(rbind(c(0, min(mids) - 0.7), c(0, max(mids) + 0.7)))
  axis(1, line = -1.2, family = axis_text_family)
  mtext(x_label, 1, line = 1, family = axis_text_family)

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
    cex = 0.82
  )
}

draw_output <- function() {
  showtext::showtext_begin()
  plot_all()
  showtext::showtext_end()
}

grDevices::pdf(pdf_path, width = 5.5, height = 8.66, onefile = TRUE, family = "sans")
draw_output()
dev.off()

png(png_path, width = 1650, height = 2598, res = 300, type = "cairo")
draw_output()
dev.off()
