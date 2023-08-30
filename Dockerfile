#


FROM php:7.1.33-cli

ENV REFRESH_NUMBER 2

RUN  sed -i 's/deb http:\/\/deb.debian.org\/debian /deb https:\/\/mirrors.aliyun.com\/debian /g' /etc/apt/sources.list
RUN  sed -i 's/deb http:\/\/security.debian.org\/debian-security /deb https:\/\/mirrors.aliyun.com\/debian-security /g' /etc/apt/sources.list

#    # deb http://snapshot.debian.org/archive/debian/20191118T000000Z buster main
     #deb http://deb.debian.org/debian buster main
     ## deb http://snapshot.debian.org/archive/debian-security/20191118T000000Z buster/updates main
     #deb http://security.debian.org/debian-security buster/updates main
     ## deb http://snapshot.debian.org/archive/debian/20191118T000000Z buster-updates main
     #deb http://deb.debian.org/debian buster-updates main


#deb http://mirrors.aliyun.com/debian/ buster main contrib non-free
#deb-src http://mirrors.aliyun.com/debian/ buster main contrib non-free
#
#deb http://mirrors.aliyun.com/debian-security/ buster/updates main contrib non-free
#deb-src http://mirrors.aliyun.com/debian-security/ buster/updates main contrib non-free
#
#deb http://mirrors.aliyun.com/debian/ buster-updates main contrib non-free
#deb-src http://mirrors.aliyun.com/debian/ buster-updates main contrib non-free
#
#deb http://mirrors.aliyun.com/debian/ buster-backports main contrib non-free
#deb-src http://mirrors.aliyun.com/debian/ buster-backports main contrib non-free
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
