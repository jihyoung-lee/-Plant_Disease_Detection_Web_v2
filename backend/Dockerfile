# PHP FPM 베이스 이미지
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
RUN chown -R www-data:www-data /var/www && \
    chmod -R 755 storage bootstrap/cache

# Composer install
RUN composer install --no-dev --optimize-autoloader

# start.sh 복사
COPY start.sh /start.sh
RUN chmod +x /start.sh

# 포트 열기
EXPOSE 8000

# 시작 스크립트
CMD ["/start.sh"]
