#!/bin/bash

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! mysqladmin ping -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent; do
    sleep 1
done

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
echo "Installing Node.js dependencies..."
npm install

# Generate application key if not exists
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Create storage link
echo "Creating storage link..."
php artisan storage:link

# Build frontend assets
echo "Building frontend assets..."
npm run build

# Clear and cache configuration
echo "Optimizing application..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Start the application
echo "Starting application..."
exec start-container 