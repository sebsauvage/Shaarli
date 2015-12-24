FROM debian:jessie
MAINTAINER Shaarli Community

RUN apt-get update \
    && apt-get install -y curl nginx-light php5-fpm php5-gd supervisor

COPY nginx.conf /etc/nginx/nginx.conf
COPY supervised.conf /etc/supervisor/conf.d/supervised.conf

WORKDIR /var/www
RUN rm -rf html \
    && curl -L https://github.com/shaarli/Shaarli/archive/master.tar.gz | tar xvzf - \
    && mv Shaarli-master shaarli \
    && chown -R www-data:www-data shaarli

VOLUME /var/www/shaarli/data

EXPOSE 80

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
