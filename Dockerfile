FROM docker.io/php:7.4-apache

RUN docker-php-ext-install pdo pdo_mysql
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html
