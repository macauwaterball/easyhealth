FROM php:8.2-fpm

# 安装基础依赖
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install zip pdo_mysql

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 设置适当的权限
RUN chown -R www-data:www-data /var/www/html