FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
       libzip-dev \
       libsqlite3-dev \
       zip \
      wget \
     && docker-php-ext-install \
       pdo pdo_sqlite zip

# Installer Composer
ADD ./docker/install-composer.sh /install-composer.sh
RUN chmod +x /install-composer.sh && /install-composer.sh && rm -f /install-composer.sh
RUN composer self-update

# Installer Symfony CLI
RUN wget https://get.symfony.com/cli/installer -O - | bash
RUN export PATH="$HOME/.symfony5/bin:$PATH"

WORKDIR /var/www

COPY . /var/www

RUN chown -R www-data:www-data /var/www
