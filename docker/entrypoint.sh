#!/bin/sh
set -eu

mkdir -p \
    /var/www/html/storage/app/public \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/testing \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    /srv/public

cp -a /var/www/html/public/. /srv/public/
rm -rf /srv/public/storage

if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link || true
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /srv/public || true

# app e queue sobem da mesma imagem/entrypoint. So o app roda migration para
# evitar migrations concorrentes contra o mesmo banco; queue seta RUN_MIGRATIONS=false.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"
