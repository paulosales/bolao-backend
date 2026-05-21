FROM php:8.2-alpine

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

CMD ["sh", "-c", "php -S 0.0.0.0:80 router.php"]
