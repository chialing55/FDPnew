#!/usr/bin/env bash
set -euo pipefail

mysql=(mysql -uroot -p"${MYSQL_ROOT_PASSWORD}")

create_database() {
    local database="$1"

    if [ -z "$database" ]; then
        return
    fi

    "${mysql[@]}" <<SQL
CREATE DATABASE IF NOT EXISTS \`${database}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON \`${database}\`.* TO '${MYSQL_USER}'@'%';
SQL
}

create_database "${MYSQL_DATABASE:-}"
create_database "${DB_DATABASE_FIRST:-}"
create_database "${DB_DATABASE_SECOND:-}"
create_database "${DB_DATABASE_THIRD:-}"
create_database "${DB_DATABASE_FORTH:-}"
create_database "${DB_DATABASE_FIFTH:-}"
create_database "${DB_DATABASE_SIXTH:-}"
create_database "${DB_DATABASE_WEB:-}"
create_database "${DB_DATABASE_FS_MORTALITY:-}"
create_database "${DB_DATABASE_NJS_SEEDLING:-}"

"${mysql[@]}" <<SQL
FLUSH PRIVILEGES;
SQL

database_for_import() {
    local file
    file="$(basename "$1")"

    case "$file" in
        aa-*.sql|zz-*.sql|*.all.sql|*.all.sql.gz)
            printf ''
            ;;
        laravel.sql|laravel.sql.gz)
            printf '%s' "${MYSQL_DATABASE:-laravel}"
            ;;
        web.sql|web.sql.gz)
            printf '%s' "${DB_DATABASE_WEB:-web}"
            ;;
        fs_tree.sql|fs_tree.sql.gz)
            printf '%s' "${DB_DATABASE_FIRST:-fs_tree}"
            ;;
        fs_seeds.sql|fs_seeds.sql.gz)
            printf '%s' "${DB_DATABASE_SECOND:-fs_seeds}"
            ;;
        fs_seedling.sql|fs_seedling.sql.gz)
            printf '%s' "${DB_DATABASE_THIRD:-fs_seedling}"
            ;;
        fs_base.sql|fs_base.sql.gz)
            printf '%s' "${DB_DATABASE_FORTH:-fs_base}"
            ;;
        ss_plot.sql|ss_plot.sql.gz)
            printf '%s' "${DB_DATABASE_FIFTH:-ss_plot}"
            ;;
        fs_web.sql|fs_web.sql.gz)
            printf '%s' "${DB_DATABASE_SIXTH:-fs_web}"
            ;;
        fs_mortality.sql|fs_mortality.sql.gz)
            printf '%s' "${DB_DATABASE_FS_MORTALITY:-fs_mortality}"
            ;;
        njs_seedling.sql|njs_seedling.sql.gz)
            printf '%s' "${DB_DATABASE_NJS_SEEDLING:-njs_seedling}"
            ;;
        *.sql|*.sql.gz)
            printf '%s' "${file%%.sql*}"
            ;;
    esac
}

if [ -d /import ]; then
    for file in /import/*.sql /import/*.sql.gz; do
        [ -e "$file" ] || continue
        database="$(database_for_import "$file")"
        mysql_args=("${mysql[@]}")

        if [ -n "$database" ]; then
            mysql_args+=("$database")
        fi

        case "$file" in
            *.sql)
                echo "Importing $file ${database:+into $database}"
                "${mysql_args[@]}" < "$file"
                ;;
            *.sql.gz)
                echo "Importing $file ${database:+into $database}"
                gunzip -c "$file" | "${mysql_args[@]}"
                ;;
        esac
    done
fi
