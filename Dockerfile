FROM php:8.2-apache

# Install system dependencies & PHP extensions needed for CodeIgniter 4 + SQLite + MySQL
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libsqlite3-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    default-mysql-client \
    && docker-php-ext-install \
    intl \
    zip \
    mysqli \
    pdo_mysql \
    calendar \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite module & prefork MPM
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork rewrite

# Set system & PHP timezone to Asia/Jakarta (WIB)
ENV TZ="Asia/Jakarta"
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone \
    && echo "date.timezone=Asia/Jakarta" > /usr/local/etc/php/conf.d/timezone.ini

# Copy custom Apache virtual host
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy all application code
COPY . /var/www/html

# Install Composer dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions for writable folder and entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
