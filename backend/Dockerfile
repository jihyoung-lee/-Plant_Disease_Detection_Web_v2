FROM php:8.2-fpm

# 시스템 패키지 설치
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev \
    libzip-dev && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer 설치
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 워크디렉토리 설정
WORKDIR /var/www

# 앱 복사
COPY . /var/www

# 권한 설정
RUN chown -R www-data:www-data /var/www

# .env 파일 준비
RUN cp .env.example .env

# Laravel 설정
RUN composer install --no-dev --optimize-autoloader && \
    php artisan key:generate && \
    php artisan config:cache

# 포트 설정
EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
