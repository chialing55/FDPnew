# 第一步：取得 Composer
FROM composer:2 AS composer

# 第二步：主容器使用 PHP 8.2 FPM
FROM php:8.2-fpm

# 安裝系統套件
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    gd \
    mbstring \
    xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 設定工作目錄
WORKDIR /app

# 複製 Composer（從第一階段 composer image 複製進來）
COPY --from=composer /usr/bin/composer /usr/bin/composer

# # 複製 composer.json 和 composer.lock
COPY composer.json composer.lock ./

# 安裝 Composer 套件
RUN composer install --no-interaction --prefer-dist --no-scripts --ignore-platform-reqs

# 複製整個專案
COPY . .

# 預設啟動指令（含 sleep 讓 db 準備好）
CMD ["sh", "-c", "sleep 5 && bash init.sh & exec php-fpm"]