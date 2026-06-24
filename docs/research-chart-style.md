# Research Chart Style

This note records the current R chart sizes and text scaling used by seed and seedling research outputs. Base R text sizes are relative `cex` values. When a script does not set a value explicitly, base R uses `cex = 1.0`; if `par(cex = ...)` is set, later text is scaled by that base value.

## Shared Target

Use `resources/scripts/research_chart_style.R` for new charts. Script bootstrap helpers live in `resources/scripts/research_chart_runtime.R`.

| Element | Target `cex` |
| --- | ---: |
| Base text | 0.9 |
| Axis tick labels | 0.9 |
| Axis titles | 0.9 |
| Panel labels, such as `(a)` | 0.9 |
| Left-aligned panel label x position | 0.06 NDC |
| Species names | 0.72 |
| Dense species names | 0.58 |
| Legend text | 0.78 |
| Small-multiple axis labels | 0.9 |
| Small-multiple outer axis titles | 0.9 |
| Small-multiple panel species title | 0.9 |

## Device Sizes

| Chart | PDF size | PNG size |
| --- | --- | --- |
| Seeds composition | 5.9 x 8 in | 1770 x 2400 px, 300 dpi |
| Seeds phenology | 5.9 x 4 in | 1770 x 1200 px, 300 dpi |
| Seeds spatial distribution | 5.9 x 8 in | 1770 x 2400 px, 300 dpi |
| Seeds spatial species traps | 5.5 x 8.66 in | 1650 x 2598 px, 300 dpi |
| Seedling composition, standard/focus | 8 x 6 in | 8 x 6 in, 180 dpi |
| Seedling composition, long | 6 x 8 in | 6 x 8 in, 180 dpi |
| Seedling growth histograms | 8 x dynamic height in | 8 x dynamic height in, 180 dpi |

## Current Text Sizes

| Chart | Base | X axis numbers | X axis title | Y axis numbers/species | Y axis title | Panel label/title | Legend |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| Seeds composition | 0.9 | 0.9 | 0.9 | species 0.72 | none | 0.9 | none |
| Seeds phenology | 0.9 | 0.9 | none | 0.9 | 0.9 via `text()` | none | 0.78 |
| Seeds spatial distribution | 0.9 | 0.9 | 0.9 | 0.9 | 0.9 | 0.9, left aligned at 0.06 NDC | none |
| Seeds spatial species traps | 0.9 | 0.9 | 0.9 | dense species 0.58 | none | none | 0.78 |
| Seedling composition, standard | 0.9 | 0.9 | 0.9 | species 0.72 | none | 0.9 | 0.78 |
| Seedling composition, focus/long | 0.9 | 0.9 | 0.9 on main panel only | species 0.72 | none | 0.9 when present | 0.78 |
| Seedling growth histograms | 0.9 | 0.9 | 0.9 outer title | 0.9 | 0.9 outer title | species name 0.9 | none |

## Notes

- Seed and seedling research charts now use `research_chart_style.R` for shared text sizes and device sizes.
- New R research charts should source `research_chart_runtime.R`, read arguments with `research_arg_value()`, load defaults with `research_load_style()`, and initialize fonts with `research_setup_cjk_font()` or `research_setup_msjh_font()`.
- Seeds spatial distribution panel labels use `panel_label_ndc_x = 0.06` so `(a)` and `(b)` align near the left side of the full device, matching the seedling composition style more closely.
- For dense horizontal species lists, prefer `species_name_dense = 0.58`; for ordinary species names, prefer `species_name = 0.72`.

## New Chart Flow

Start new scripts with this shared flow:

```r
args <- commandArgs(trailingOnly = TRUE)
script_arg <- grep("^--file=", commandArgs(FALSE), value = TRUE)
script_path <- if (length(script_arg) > 0) sub("^--file=", "", script_arg[[1]]) else "resources/scripts/your_script.R"
source(file.path(dirname(normalizePath(script_path, mustWork = FALSE)), "research_chart_runtime.R"))

input_path <- research_arg_value(args, "input", required = TRUE)
pdf_path <- research_arg_value(args, "pdf", required = TRUE)
png_path <- research_arg_value(args, "png", required = TRUE)
font_path <- research_arg_value(args, "font", "")

research_load_style(
  "resources/scripts/your_script.R",
  list(cex = 0.9, axis = 0.9, axis_title = 0.9),
  list(portrait_width = 5.9, portrait_height = 8, png_res = 300)
)
research_require_packages(c("jsonlite", "showtext", "sysfonts"))
axis_text_family <- research_setup_cjk_font(font_path)
```

For scripts that use optional Microsoft JhengHei / Times font paths, use `research_setup_msjh_font(font_path, times_path)` instead of `research_setup_cjk_font()`.
