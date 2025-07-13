FROM php:8.2-fpm

# 시스템 패키지 설치
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev \
    libzip-dev && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer 복사
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 작업 디렉토리
WORKDIR /var/www

# 소스 복사
COPY . .

# 퍼미션 설정
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# 실제 운영에서는 .env.example 말고 .env 직접 복사 필요
# 또는 render.yaml에서 환경변수 주입해야 함
COPY .env.production .env

# Composer install
RUN composer install --no-dev --optimize-autoloader

# Laravel key 생성 및 캐시 처리
RUN php artisan key:generate \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# 마이그레이션 자동 수행 (선택 사항)
# RUN php artisan migrate --force

# 포트 열기
EXPOSE 8000

# 실행 명령
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
