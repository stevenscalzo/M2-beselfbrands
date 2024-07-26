FROM php:7.4-fpm

# install libraries required by the extensions
RUN apt-get update \
  && apt-get install -y \
    cron \
    libicu-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libxslt1-dev \
    libfreetype6-dev \
    libzip-dev \
    gzip \
    libcurl4-openssl-dev \
    unzip

# install extensions
RUN docker-php-ext-configure gd --with-jpeg --with-freetype
RUN docker-php-ext-install \
      bcmath \
      gd \
      intl \
      pdo_mysql \
      soap \
      sockets \
      xsl \
      zip \
      opcache

RUN pecl install mcrypt-1.0.3 \
    && docker-php-ext-enable mcrypt

RUN useradd -G www-data,root -u 1000 -d /home/devuser devuser
RUN mkdir -p /home/devuser/.composer && chown -R devuser:devuser /home/devuser

#COMPOSER
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --version=2.1.3 && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer

# creating php.ini file
COPY php.ini /usr/local/etc/php/php.ini

#Configuración XDEBug
#RUN pecl install xdebug-3.0.4
#RUN echo 'zend_extension=xdebug.so' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo 'xdebug.mode=debug' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo 'xdebug.start_with_request=yes' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo 'xdebug.discover_client_host=1' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo 'xdebug.client_host=127.0.0.1' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo 'xdebug.idekey = PHPSTORM' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo 'xdebug.client_port=9003' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini


RUN printf "\n" | pecl install apcu

RUN apt-get update --allow-releaseinfo-change \
  && apt-get install -y \
    procps git

#NODE, NPM Y GULP
RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -
RUN apt-get install -y build-essential nodejs
RUN npm install npm@6 -g
RUN npm install --global gulp-cli

#INTEGRATION TEST
RUN apt-get update && apt-get install -y default-mysql-client

RUN rm -rf /etc/localtime && ln -s /usr/share/zoneinfo/Europe/Madrid /etc/localtime

COPY startup.sh /bin/startup.sh

WORKDIR /var/www/html

#RUN docker-php-ext-enable xdebug

ENTRYPOINT "/bin/startup.sh"

