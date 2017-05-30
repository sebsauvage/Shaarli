FROM alpine:3.6
MAINTAINER Shaarli Community

RUN apk --update --no-cache add \
        ca-certificates \
        curl \
        make \
        php7 \
        php7-ctype \
        php7-curl \
        php7-dom \
        php7-gd \
        php7-iconv \
        php7-intl \
        php7-json \
        php7-mbstring \
        php7-openssl \
        php7-phar \
        php7-session \
        php7-simplexml \
        php7-tokenizer \
        php7-xdebug \
        php7-xml \
        php7-zlib \
        rsync

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mkdir /shaarli
WORKDIR /shaarli
VOLUME /shaarli

ENTRYPOINT ["make"]
CMD []
