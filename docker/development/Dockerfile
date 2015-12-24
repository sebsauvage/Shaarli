FROM debian:jessie
MAINTAINER Shaarli Community

RUN apt-get update \
    && apt-get install -y \
       nginx-light php5-fpm php5-gd supervisor \
       git nano

ADD https://getcomposer.org/composer.phar /usr/local/bin/composer
RUN chmod 755 /usr/local/bin/composer

COPY nginx.conf /etc/nginx/nginx.conf
COPY supervised.conf /etc/supervisor/conf.d/supervised.conf
RUN echo "<?php phpinfo(); ?>" > /var/www/index.php

WORKDIR /var/www
RUN rm -rf html \
    && git clone https://github.com/shaarli/Shaarli.git shaarli \
    && chown -R www-data:www-data .

WORKDIR /var/www/shaarli
RUN composer install

VOLUME /var/www/shaarli/data

EXPOSE 80

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
