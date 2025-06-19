FROM php:8.2-fpm

RUN apt-get update \
 && apt-get install -y \
      libsqlite3-dev \
      libpq-dev \
      libssl-dev \
      zip unzip \
 && docker-php-ext-install \
      pdo \
      pdo_sqlite \
      pdo_pgsql \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && docker-php-ext-install opcache \
 && { \
      echo 'opcache.memory_consumption=128'; \
      echo 'opcache.interned_strings_buffer=8'; \
      echo 'opcache.max_accelerated_files=10000'; \
      echo 'opcache.validate_timestamps=1'; \
      echo 'opcache.revalidate_freq=2'; \
    } >> /usr/local/etc/php/conf.d/opcache.ini \
 && rm -rf /var/lib/apt/lists/*

 COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
