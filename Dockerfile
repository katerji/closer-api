FROM php:8.2-apache
RUN apt-get update
RUN apt-get install -y \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libfreetype6-dev
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype
RUN docker-php-ext-install gd
RUN apt-get install -y git
RUN docker-php-ext-install pdo_mysql

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
RUN sed -i 's/= 128M/= 256M/' /usr/local/etc/php/php.ini

ADD ../ /var/www/html/api/
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer
RUN cp:
WORKDIR /var/www/html/api
RUN composer install --no-dev
RUN chown -R www-data:www-data .
RUN chmod -R 775 .
ADD ./api.conf /etc/apache2/sites-available/api.conf
RUN a2ensite api.conf
RUN a2enmod rewrite
