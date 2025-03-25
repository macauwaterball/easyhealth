FROM php:8.2-apache

# 安装PHP扩展和依赖
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql

# 启用Apache模块
RUN a2enmod rewrite

# 设置工作目录
WORKDIR /var/www/html

# 复制项目文件
COPY . /var/www/html/

# 设置目录权限
RUN chown -R www-data:www-data /var/www/html

# 设置Apache配置
COPY apache.conf /etc/apache2/sites-available/000-default.conf