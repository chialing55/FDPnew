#!/bin/bash

echo "🚀 [Deploy] Laravel 專案部署開始..."

cd /volume1/web/FDPnew || exit 1

echo "🔄 拉取 Git 最新程式碼..."
git pull origin main || { echo "❌ Git 更新失敗"; exit 1; }

echo "📦 Composer 安裝依賴..."
composer install --no-dev --optimize-autoloader || { echo "❌ Composer 安裝失敗"; exit 1; }

if [ ! -f ".env" ]; then
  echo "🔧 複製 .env 設定檔..."
  cp .env.example .env
  php artisan key:generate
fi

echo "🧠 快取設定與路由..."
php artisan config:cache
php artisan route:cache

echo "📂 確保權限正確..."
chown -R http:http storage bootstrap/cache

echo "✅ [Deploy] 部署完成！"
