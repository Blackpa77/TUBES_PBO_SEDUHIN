FROM php:8.1-apache

# Install ekstensi MySQL & zip
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Aktifkan mod_rewrite
RUN a2enmod rewrite

# Arahkan Apache ke folder public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Copy file project
COPY . /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# Hak akses
RUN chown -R www-data:www-data /var/www/html