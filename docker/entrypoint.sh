#!/bin/sh
set -eu

cd /var/www/html
umask 027
mkdir -p storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs

# Secrets and SQLite live outside the image and survive container replacement.
if [ ! -s storage/app/private/app-key ]; then
    php -r 'echo "base64:".base64_encode(random_bytes(32));' > storage/app/private/app-key
fi
export APP_KEY="$(cat storage/app/private/app-key)"
export DB_CONNECTION=sqlite
export DB_DATABASE=/var/www/html/storage/app/private/database.sqlite
touch "$DB_DATABASE"
chown -R www-data:www-data storage bootstrap/cache
chmod 640 storage/app/private/app-key "$DB_DATABASE"

# Run initialization as the same user that handles HTTP requests.
su -s /bin/sh www-data -c 'php artisan migrate --force --no-interaction'
if [ ! -f storage/app/private/demo-seeded ]; then
    su -s /bin/sh www-data -c 'php artisan db:seed --force --no-interaction'
    touch storage/app/private/demo-seeded
    chown www-data:www-data storage/app/private/demo-seeded
fi
su -s /bin/sh www-data -c 'php artisan config:cache && php artisan route:cache && php artisan view:cache'

exec docker-php-entrypoint "$@"
