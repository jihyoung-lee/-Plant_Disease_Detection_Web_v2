#!/bin/sh
set -eu

cd /var/www

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

if ! grep -Eq '^APP_KEY=base64:.+' .env; then
    php artisan key:generate --force
fi

if ! grep -Eq '^JWT_SECRET=.+' .env; then
    grep -q '^JWT_SECRET=' .env || printf '\nJWT_SECRET=\n' >> .env
    php artisan jwt:secret --force
fi

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chmod -R 775 storage bootstrap/cache

if [ ! -e public/storage ]; then
    php artisan storage:link
fi

php artisan optimize:clear

if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"
