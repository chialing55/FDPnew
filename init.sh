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

# 2. 只在 APP_KEY 尚未設定時產生，避免重啟容器後讓既有 session / CSRF token 失效
if ! grep -Eq '^APP_KEY=base64:.+' .env; then
  echo "🔑 產生 APP Key..."
  php artisan key:generate || true
else
  echo "🔑 APP Key already exists, skip key generation."
fi

# 3. 設定目錄權限（storage、bootstrap/cache、植物照片）
echo "🛠️ 設定 storage 和 cache 權限..."
mkdir -p storage/framework/livewire-tmp storage/app/public/content-images storage/app/public/hero storage/app/public/plot-cards public/FDPfiles/splist/photo
if [ -d public/images/hero ]; then
  cp -n public/images/hero/. storage/app/public/hero/
fi
for image in public/images/plots/*_thumb.jpg; do
  [ -f "$image" ] || continue
  cp -n "$image" storage/app/public/plot-cards/
done
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chown -R www-data:www-data public/FDPfiles/splist/photo || true
chmod -R 775 public/FDPfiles/splist/photo || true

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
