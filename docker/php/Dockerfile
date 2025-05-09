# docker/php/Dockerfile
FROM php:8.2-fpm

# Install SQLite dev libs and the PDO SQLite extension
RUN apt-get update \
 && apt-get install -y libsqlite3-dev \
 && docker-php-ext-install pdo pdo_sqlite \
 && rm -rf /var/lib/apt/lists/*

# (Optional) Enable OPcache for better performance
RUN docker-php-ext-install opcache \
 && { \
      echo 'opcache.memory_consumption=128'; \
      echo 'opcache.interned_strings_buffer=8'; \
      echo 'opcache.max_accelerated_files=10000'; \
      echo 'opcache.validate_timestamps=1'; \
      echo 'opcache.revalidate_freq=2'; \
    } >> /usr/local/etc/php/conf.d/opcache.ini
