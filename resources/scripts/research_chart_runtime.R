`%||%` <- function(x, y) if (is.null(x)) y else x

research_arg_value <- function(args, name, default = NULL, required = FALSE) {
  keys <- unique(c(name, paste0("--", sub("^--", "", name))))
  idx <- match(keys, args)
  idx <- idx[!is.na(idx)]

  if (length(idx) == 0 || idx[[1]] == length(args)) {
    if (required) {
      stop(paste("Missing argument", keys[[length(keys)]]), call. = FALSE)
    }

    return(default)
  }

  args[[idx[[1]] + 1]]
}

research_script_path <- function(default) {
  script_arg <- grep("^--file=", commandArgs(FALSE), value = TRUE)

  if (length(script_arg) > 0) {
    return(sub("^--file=", "", script_arg[[1]]))
  }

  default
}

research_load_style <- function(default_script, default_style = list(), default_device = list()) {
  script_path <- research_script_path(default_script)
  style_path <- file.path(dirname(normalizePath(script_path, mustWork = FALSE)), "research_chart_style.R")

  if (file.exists(style_path)) {
    source(style_path, local = FALSE)
  }

  if (!exists("research_chart_style", envir = .GlobalEnv)) {
    assign("research_chart_style", default_style, envir = .GlobalEnv)
  } else {
    assign("research_chart_style", modifyList(default_style, get("research_chart_style", envir = .GlobalEnv)), envir = .GlobalEnv)
  }

  if (!exists("research_chart_device", envir = .GlobalEnv)) {
    assign("research_chart_device", default_device, envir = .GlobalEnv)
  } else {
    assign("research_chart_device", modifyList(default_device, get("research_chart_device", envir = .GlobalEnv)), envir = .GlobalEnv)
  }
}

research_require_packages <- function(packages) {
  for (package in packages) {
    if (!requireNamespace(package, quietly = TRUE)) {
      stop(paste("R package", package, "is required"), call. = FALSE)
    }
  }
}

research_setup_cjk_font <- function(font_path, family = "cjk", dpi = 300) {
  if (is.null(font_path) || !nzchar(font_path) || !file.exists(font_path)) {
    stop("Chinese font file is required for chart output.", call. = FALSE)
  }

  sysfonts::font_add(family, regular = font_path)
  showtext::showtext_opts(dpi = dpi)
  showtext::showtext_auto()

  family
}

research_setup_msjh_font <- function(font_path, times_path = NULL) {
  if (!is.null(font_path) && file.exists(font_path)) {
    sysfonts::font_add("msjh", regular = font_path)
  }

  if (!is.null(times_path) && file.exists(times_path)) {
    sysfonts::font_add("times", regular = times_path)
  }

  showtext::showtext_auto(FALSE)

  if (!is.null(font_path) && file.exists(font_path)) "msjh" else ""
}

research_label_value <- function(value, fallback) {
  if (is.null(value) || length(value) == 0 || is.na(value)) {
    return(enc2utf8(fallback))
  }

  enc2utf8(as.character(value))
}
