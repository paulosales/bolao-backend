FROM php:8.2-alpine

RUN docker-php-ext-install pdo pdo_mysql

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
  && php composer-setup.php \
  && php -r "unlink('composer-setup.php');"

RUN php composer.phar install

WORKDIR /var/www/html

CMD ["sh", "-c", "php -S 0.0.0.0:80 router.php"]
