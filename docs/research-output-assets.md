# Research Output Asset Flow

Use this flow for report pages that generate temporary charts or PDFs.

1. Controller uses `ManagesResearchOutputAssets`.
2. Generated files are written under a page-specific directory in `sys_get_temp_dir()`.
3. Each generated file is registered in session under an asset prefix, such as `seedling_research_output_assets_` or `seeds_research_output_assets_`.
4. Preview and download links use `/research-output/asset/{token}.{extension}` routes instead of public storage paths.
5. The clear-session endpoint calls `forgetResearchOutputSessionAssets()`.
6. `forgetResearchOutputSessionAssets()` removes cached HTML session keys, deletes registered asset files, removes their temporary directories, and forgets asset session keys.

To add another page:

1. Add an asset route with `whereIn('extension', ['png', 'pdf'])`.
2. Add a controller method that returns `researchOutputAssetFromSession($request, $token, $extension, $assetPrefix)`.
3. Define allowed temporary path prefixes for that page.
4. Generate assets into a temp directory using one of those prefixes.
5. Store session records with `path`, `extension`, `mime`, and `download`.
6. Clear the page's HTML cache and asset session keys with `forgetResearchOutputSessionAssets()`.
