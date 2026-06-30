INSERT INTO fs_geo_tree_survey.census5_part (
    stemid, tag, branch, dbh, h1, h2, code, status, pom, note, date,
    confirm, tofix, alternote, updated_at, updated_id, spcode, csp,
    qx, qy, sqx, sqy, `show`
)
SELECT
    census5.stemid, census5.tag, census5.branch, census5.dbh,
    census5.h1, census5.h2, census5.code, census5.status,
    census5.pom, census5.note, census5.date, census5.confirm,
    census5.tofix, census5.alternote, '', '', base.spcode,
    tree_splist.csp, base.qx, base.qy, base.sqx, base.sqy, 0
FROM (
    SELECT qualifying_stem.stemid
    FROM fs_tree.base AS qualifying_base
    INNER JOIN fs_tree.census5 AS qualifying_stem
        ON qualifying_stem.tag = qualifying_base.tag
    WHERE (qualifying_base.deleted_at IS NULL OR qualifying_base.deleted_at = '')
      AND qualifying_base.tag NOT LIKE 'G%'
      AND qualifying_stem.dbh >= 9.5
      AND (
          FLOOR(qualifying_base.plotx / 100) * 5
          + CEIL(qualifying_base.ploty / 100)
      ) IN (4, 7, 8, 12, 16, 17, 20, 21, 22, 25)

    UNION

    SELECT main_stem.stemid
    FROM fs_tree.base AS qualifying_base
    INNER JOIN fs_tree.census5 AS qualifying_stem
        ON qualifying_stem.tag = qualifying_base.tag
       AND qualifying_stem.dbh >= 9.5
    INNER JOIN fs_tree.census5 AS main_stem
        ON main_stem.tag = qualifying_base.tag
       AND main_stem.branch = 0
    WHERE (qualifying_base.deleted_at IS NULL OR qualifying_base.deleted_at = '')
      AND qualifying_base.tag NOT LIKE 'G%'
      AND (
          FLOOR(qualifying_base.plotx / 100) * 5
          + CEIL(qualifying_base.ploty / 100)
      ) IN (4, 7, 8, 12, 16, 17, 20, 21, 22, 25)
) AS selected_stems
INNER JOIN fs_tree.census5 AS census5
    ON census5.stemid = selected_stems.stemid
INNER JOIN fs_tree.base AS base
    ON base.tag = census5.tag
INNER JOIN fs_base.tree_splist AS tree_splist
    ON tree_splist.spcode = base.spcode;

SELECT
    COUNT(*) AS total_rows,
    COUNT(DISTINCT tag) AS distinct_tags,
    COUNT(DISTINCT stemid) AS distinct_stems,
    SUM(`show` <> 0) AS nonblank_show,
    SUM(updated_id <> '') AS nonblank_updated_id,
    SUM(updated_at <> '') AS nonblank_updated_at,
    SUM(tag LIKE 'G%') AS g_tags
FROM fs_geo_tree_survey.census5_part;
