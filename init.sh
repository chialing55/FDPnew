#!/bin/sh
set -e

# 統一把 log 寫到這裡
LOG_FILE=/tmp/init.log

echo "==== init.sh start at $(date) ====" >> "$LOG_FILE"

# 移到專案目錄
cd /app || {
  echo "cd /app failed" >> "$LOG_FILE"
  exit 0
}

echo "APP_ENV=$APP_ENV" >> "$LOG_FILE"
echo "TAILWIND_WATCH=$TAILWIND_WATCH" >> "$LOG_FILE"

# 只在非 production，且設定有啟用時才開 watch
if [ "$APP_ENV" = "local" ] && [ "$TAILWIND_WATCH" = "1" ]; then
  echo "Starting Tailwind watcher..." >> /tmp/init.log
  nohup npm run watch:css >> /tmp/tailwind-watch.log 2>&1 &
else
  echo "Tailwind watcher disabled." >> /tmp/init.log
fi


# 1. 建立 .env 檔案（若不存在）
if [ ! -f .env ]; then
  echo "📄 建立 .env 檔案..."
  cp .env.example .env
fi

# 2. 產生 APP Key
echo "🔑 產生 APP Key..."
php artisan key:generate || true

# 3. 設定目錄權限（storage、bootstrap/cache）
echo "🛠️ 設定 storage 和 cache 權限..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

# 4. 等待資料庫啟動
echo "⏳ 等待 MySQL 資料庫啟動..."
until php artisan migrate --pretend > /dev/null 2>&1; do
  echo "⌛ MySQL 尚未就緒，稍等 3 秒..."
  sleep 3
done

# 5. 執行 migrate
echo "🗂️ 開始正式 migrate..."
php artisan migrate

# 6. 清除 Laravel 快取
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "✅ Laravel 專案初始化完成！"