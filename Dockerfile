#


FROM php:7.1.33-cli
COPY sources.list /etc/apt/sources.list
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

RUN pecl install swoole-1.10.5 && docker-php-ext-enable swoole

RUN composer config -g repos.packagist composer https://mirrors.cloud.tencent.com/composer/

WORKDIR /var/www/html
COPY . /var/www/html
# RUN composer install
CMD bash
