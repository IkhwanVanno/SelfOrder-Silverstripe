FROM php:8.2-apache

# Enable apache mod_rewrite
RUN a2enmod rewrite

# Install PHP extensions and dependencies
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    libicu-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    curl wget zip supervisor \
    && docker-php-ext-install pdo_mysql mbstring intl zip gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy entrypoint script
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Expose port
EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
