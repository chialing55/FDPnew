# 第一步：取得 Composer
FROM composer:2 AS composer

# 第二步：主容器使用 PHP 8.2 FPM
FROM php:8.3-fpm

# 安裝系統套件 + PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    ca-certificates \
    curl \
    unzip \
    zip \
    nodejs \
    npm \
    r-base \
    fonts-noto-cjk \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        zip \
        gd \
        mbstring \
        xml \
        intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN Rscript -e "install.packages(c('showtext','sysfonts','jsonlite'), repos='https://cloud.r-project.org')"



# 設定工作目錄
WORKDIR /app

# 複製 Composer（從第一階段 composer image 複製進來）
COPY --from=composer /usr/bin/composer /usr/bin/composer

# # 複製 composer.json 和 composer.lock
COPY composer.json composer.lock ./

# 安裝 Composer 套件
RUN composer install --no-interaction --prefer-dist --no-scripts 

# 複製整個專案
COPY . .

# 預設啟動指令（含 sleep 讓 db 準備好）
CMD ["sh", "-c", "sleep 5 && bash init.sh & exec php-fpm"]