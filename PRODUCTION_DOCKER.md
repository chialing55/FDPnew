# Production Docker Deployment

This production setup is separate from the local development files. Keep using
`Dockerfile` and `docker-compose.yml` for local development. Use these files on
the server:

- `Dockerfile.prod`
- `docker-compose.prod.yml`
- `.env.production`
- `.dockerignore`
- `docker/app/prod-entrypoint.sh`
- `docker/nginx/default.prod.conf`
- `docker/php/prod.ini`
- `docker/mysql/init/00-create-production-databases.sh`

## 1. Create the production environment file

Copy the example file and edit passwords, URL, and file paths:

```bash
cp .env.production.example .env.production
php artisan key:generate --show
```

Put the generated key into `APP_KEY`.

For Docker production, database hosts should normally be `db`, not an external
IP address:

```env
DB_HOST=db
DB_HOST_FIRST=db
DB_HOST_SECOND=db
DB_HOST_THIRD=db
DB_HOST_FORTH=db
DB_HOST_FIFTH=db
DB_HOST_SIXTH=db
```

## 2. Prepare uploaded/static data directories

The compose file mounts large public data and MySQL's persistent data outside
the image. On Linode, prefer absolute paths so the database is not tied to the
project folder:

```bash
mkdir -p /srv/fdp/FDPfiles/recordpdf /srv/fdp/SSPfiles /srv/fdp/mysql /srv/fdp/initdb
```

Then set the paths in `.env.production`:

```env
FDP_FILES_PATH=/srv/fdp/FDPfiles
SSP_FILES_PATH=/srv/fdp/SSPfiles
MYSQL_DATA_PATH=/srv/fdp/mysql
MYSQL_IMPORT_PATH=/srv/fdp/initdb
```

Tree record PDFs live under `FDP_FILES_PATH/recordpdf`, which is served as
`/FDPfiles/recordpdf/...`.

`MYSQL_DATA_PATH` is the real long-term database storage directory. Back it up
and keep it when rebuilding the app.

`MYSQL_IMPORT_PATH` is only for SQL dump files used during the first MySQL
startup. MySQL's official entrypoint auto-imports files only when
`MYSQL_DATA_PATH` is empty.

## 3. Build and start

Always pass the env file explicitly so Compose can use the same variables for
container environments and path interpolation:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml build
docker compose --env-file .env.production -f docker-compose.prod.yml up -d
```

Run Laravel migrations manually after the database is ready:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan migrate --force
```

The production entrypoint caches config, routes, and views. It does not generate
a new app key and it does not run migrations automatically.

## 4. Useful commands

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml ps
docker compose --env-file .env.production -f docker-compose.prod.yml logs -f app
docker compose --env-file .env.production -f docker-compose.prod.yml logs -f db
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan optimize:clear
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan config:cache
```

phpMyAdmin is bound to the server's localhost by default:

```env
PHPMYADMIN_BIND=127.0.0.1
PHPMYADMIN_PORT=8081
```

Use an SSH tunnel from your own computer:

```bash
ssh -L 8081:127.0.0.1:8081 chialing@139.162.91.125
```

Then open `http://127.0.0.1:8081` locally.

## 5. Backup before resize or rebuild

For the automated Linode-to-Synology backup, retention, verification, and
restore procedure, see [`docs/production/backup.md`](docs/production/backup.md).

Before resizing Linode, replacing volumes, or re-importing data, back up MySQL:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml exec db mysqldump -uroot -p --all-databases > backup.sql
```
