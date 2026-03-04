#!/bin/bash
set -e

# Wait for the database to be ready
echo "Waiting for database..."
until php -r "new PDO('mysql:host=db;port=3306;dbname=swiftflydb', 'swiftfly', 'password');" >/dev/null 2>&1; do
  sleep 1
done

# Check if the initialization flag file exists
if [ ! -f /var/www/.docker_setup_completed ]; then
  echo "First-time initialization: setting up Swiftfly..."
  
  if [ ! -f .env ]; then
    echo "Copying .env.example file..."
    cp .env.example .env
  fi

  echo "Setting up all the project dependencies..."
  composer install --no-interaction --prefer-dist --optimize-autoloader

  echo "Setting up the project key..."
  php artisan key:generate

  # Only run JWT secret generation if the package is installed
  if grep -q "tymon/jwt-auth" composer.json; then
    echo "Setting up the JWT Secret..."
    php artisan jwt:secret
  fi

  echo "Running migrations and seeding the database..."
  php artisan migrate --seed --force

  # Adjust permissions for storage (ignore errors if folder doesn't exist yet)
  chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

  # Create a flag file to avoid re-running the setup
  touch /var/www/.docker_setup_completed
  echo "Initialization complete!"
else
  echo "Container already initialized, skipping one-time setup."
fi

# Execute the main process (e.g., php-fpm)
exec "$@"
