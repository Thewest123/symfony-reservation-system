#!/bin/bash

# Function to check if PostgreSQL is ready
wait_for_postgres() {
  until timeout 1 bash -c 'echo > /dev/tcp/postgres/5432' 2>/dev/null; do
    echo "Waiting for PostgreSQL to be ready..."
    sleep 1
  done
}

# Wait for the database to be ready
wait_for_postgres

# Run Symfony commands
#php bin/console doctrine:schema:create
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
php bin/console lexik:jwt:generate-keypair --no-interaction

# Start Symfony server
symfony server:start --no-tls --allow-http --port=80
