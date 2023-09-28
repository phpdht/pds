#


FROM php:7.1.33-cli
COPY sources.list /etc/apt/sources.list
ENV REFRESH_NUMBER 2


RUN apt-get update
RUN apt-get install -y vim wget zip zlib1g-dev inetutils-ping htop

RUN docker-php-ext-install bcmath mbstring pdo pdo_mysql zip mysqli;docker-php-ext-enable pdo pdo_mysql mysqli;


# 安装composer
RUN curl -sS https://getcomposer.org/installer | php;mv composer.phar /usr/local/bin/composer;composer config -g repo.packagist composer https://packagist.phpcomposer.com

RUN pecl install swoole-1.9.18 && docker-php-ext-enable swoole


WORKDIR /var/www/html
COPY . /var/www/html
# RUN composer install
CMD bash
