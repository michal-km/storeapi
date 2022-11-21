FROM php:8.1-apache
ENV APACHE_DOCUMENT_ROOT /var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN echo "realpath_cache_size = 4096k" > /usr/local/etc/php/php.ini-development
RUN echo "realpath_cache_size = 4096k" > /usr/local/etc/php/php.ini-production
RUN echo "realpath_cache_ttl = 7200" > /usr/local/etc/php/php.ini-development
RUN echo "realpath_cache_ttl = 7200" > /usr/local/etc/php/php.ini-production
RUN a2enmod rewrite
RUN docker-php-ext-install pdo pdo_mysql opcache
RUN apache2ctl restart

