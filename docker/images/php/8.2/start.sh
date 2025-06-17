#!/bin/bash

cd /app

# Установка зависимостей, если нет vendor
if [ ! -d "vendor" ]; then
  composer install --no-interaction --prefer-dist
fi

# Ждём, пока MySQL будет доступен (через netcat)
echo "Waiting for MySQL at $DB_HOST:$DB_PORT..."
while ! nc -z $DB_HOST $DB_PORT; do
  echo "MySQL is unavailable - sleeping"
  sleep 3
done
echo "MySQL is up!"

php artisan key:generate

php artisan migrate --force

php artisan schedule:work --verbose --no-interaction
