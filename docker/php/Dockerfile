# Use the official PHP image with FPM support
FROM php:8.3-fpm

# Update the repositories and install the necessary dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    libzip-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    vim \
    redis-tools \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    opcache \
    && cp "/usr/local/etc/php/php.ini-development" "/usr/local/etc/php/php.ini" \
    && docker-php-ext-enable opcache \
    && pecl install redis xdebug \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy the custom PHP configuration file
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

RUN echo "extension=redis.so" >> /usr/local/etc/php/php.ini \
    && echo "extension=pdo_mysql.so" >> /usr/local/etc/php/php.ini \
    && echo "extension=sodium" >> /usr/local/etc/php/php.ini \
    && echo "extension=zip" >> /usr/local/etc/php/php.ini \
    && echo "zend_extension=xdebug.so" >> /usr/local/etc/php/php.ini \
    && echo "xdebug.mode=coverage" >> /usr/local/etc/php/php.ini

# Install Composer (stable version)
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Set the working directory for Symfony
WORKDIR /var/www/html

# Copy the project code to the container
COPY . .

# Assign necessary permissions for Symfony
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/logs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/var

# Documents the internal port used by PHP-FPM
EXPOSE 9000

# Default command when starting the container
CMD ["php-fpm"]