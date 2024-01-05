# Use an official PHP runtime as a parent image
FROM php:8.2-apache

# Set the working directory to /app
WORKDIR /app

# Copy the current directory contents into the container at /app
COPY . /app

# Install any dependencies your app might have
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt-get install -y symfony-cli

# Expose port 80 for Apache
EXPOSE 80

# Run your Symfony app when the container starts
# CMD ["symfony", "server:start", "--no-tls", "--allow-http", "--port=80"]
# (Not needed, relying on entrypoint.sh)

# Set execute permissions for entrypoint.sh
RUN chmod +x entrypoint.sh