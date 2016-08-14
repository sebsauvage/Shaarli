FROM debian:jessie
MAINTAINER Shaarli Community

ENV TERM dumb
RUN apt-get update \
    && apt-get install --no-install-recommends -y \
       ca-certificates \
       nginx-light \
       php5-curl \
       php5-fpm \
       php5-gd \
       php5-intl \
       supervisor \
       git \
       nano \
    && apt-get clean

COPY nginx.conf /etc/nginx/nginx.conf
COPY supervised.conf /etc/supervisor/conf.d/supervised.conf

ADD https://getcomposer.org/composer.phar /usr/local/bin/composer
RUN chmod 755 /usr/local/bin/composer

WORKDIR /var/www
RUN git clone https://github.com/shaarli/Shaarli.git shaarli \
    && cd shaarli \
    && composer --prefer-dist install
RUN rm -rf html \
    && echo "<?php phpinfo(); ?>" > index.php \
    && chown -R www-data:www-data .

VOLUME /var/www/shaarli/data

EXPOSE 80

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
