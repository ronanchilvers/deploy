FROM php:7.1-apache

MAINTAINER Ronan Chilvers <ronan@thelittledot.com>

COPY . /srv/app
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/local.yaml /srv/app/local.yaml
COPY .docker/htaccess /srv/app/web/.htaccess

RUN mkdir /srv/sites && chown -R www-data:www-data /srv/sites
RUN chown -R www-data:www-data /srv/app
RUN a2enmod rewrite
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'a5c698ffe4b8e849a443b120cd5ba38043260d5c4023dbf93e1558871f1f07f58274fc6f4c93bcfd858c6bd0775cd8d1') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); exit(1); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer
RUN chmod a+x /usr/local/bin/composer

RUN cd /srv/app && php /usr/local/bin/composer install

RUN docker-php-ext-install mbstring pdo pdo_mysql

WORKDIR /srv/app

EXPOSE 80
