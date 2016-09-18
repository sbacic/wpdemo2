FROM wordpress:latest
MAINTAINER Slaven Bacic

ADD assets/uploads /var/www/html/wp-content
RUN rm /usr/local/etc/php/conf.d/opcache-recommended.ini && \
chmod +777 -Rf /var/www/html && \
docker-php-ext-install pdo_mysql
