FROM php:8.2-apache
RUN docker-php-ext-install pdo_mysql
ADD ../ /var/www/html/api/
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer
RUN apt-get update
RUN apt-get --assume-yes install git
WORKDIR /var/www/html/api
RUN composer install --no-dev
RUN chown -R www-data:www-data .
RUN chmod -R 775 .
ADD ./api.conf /etc/apache2/sites-available/api.conf
RUN a2ensite api.conf
RUN a2enmod rewrite
