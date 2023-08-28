#


FROM php:7.0-cli

MAINTAINER Dongasai 1514582970@qq.com


ENV REFRESH_NUMBER 2
RUN apt-get update
RUN apt-get install -y vim wget zip zlib1g-dev

RUN docker-php-ext-install bcmath mbstring pdo pdo_mysql zip;docker-php-ext-enable pdo pdo_mysql;

RUN apt-get install -y \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
	&& docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
	&& docker-php-ext-install gd

# 安装composer
RUN curl -sS https://getcomposer.org/installer | php;mv composer.phar /usr/local/bin/composer;composer config -g repo.packagist composer https://packagist.phpcomposer.com

RUN pecl install swoole-1.9.18 --with-php-config=/usr/local/bin/php-config --enable-swoole-debug=yes --enable-sockets=yes --enable-openssl=yes --enable-http2=yes --enable-async-redis=yes --enable-mysqlnd=yes

RUN docker-php-ext-enable swoole
RUN composer config -g repos.packagist composer https://mirrors.cloud.tencent.com/composer/

WORKDIR /var/www/html
COPY . /var/www/html
RUN composer install
CMD bash
