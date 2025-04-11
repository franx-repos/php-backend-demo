FROM php:8.2-apache

# Installiere PHP-Erweiterungen und Tools
RUN apt-get update && apt-get install -y unzip curl git && \
    docker-php-ext-install mysqli pdo pdo_mysql && \
    a2enmod rewrite

# Composer installieren
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setze das Arbeitsverzeichnis
WORKDIR /var/www/html

# Kopiere nur Composer-Dateien und installiere Dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Danach gesamtes Projekt kopieren (inkl. src, public, etc.)
COPY . .

# Setze korrekte Berechtigungen
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Exponiere Port 80
EXPOSE 80

# Starte Apache
CMD ["apache2-foreground"]
