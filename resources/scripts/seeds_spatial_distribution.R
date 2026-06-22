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

y_label <- label_value(payload$labels$y, "trap count")
x_label <- label_value(payload$labels$x, "species count")
flower_title <- label_value(payload$labels$flower_title, "(a) flower")
fruit_title <- label_value(payload$labels$fruit_title, "(b) fruit and seed")
empty_label <- label_value(payload$labels$empty, "no data")
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
    text(0.5, 0.5, empty_label, family = axis_text_family)
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
    family = axis_text_family
  )
  abline(0, 0)
  mtext(y_label, 2, line = 2.5, family = axis_text_family)
  mtext(x_label, 1, line = 2.8, family = axis_text_family)
  usr <- par("usr")
  text(usr[1], usr[4] + 0.08 * (usr[4] - usr[3]), labels = title, adj = 0, xpd = NA, family = axis_text_family)
}

plot_all <- function() {
  par(family = axis_text_family, mfcol = c(2, 1), mar = c(5, 4, 2, 1))
  plot_panel(trap_counts(payload$flower_traps), flower_title)
  plot_panel(trap_counts(payload$fruit_traps), fruit_title)
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
