FROM php:8.2-apache

# Install PHP extensions and download PrivateBin in a single layer
ARG PRIVATEBIN_VERSION=2.0.3
RUN apt-get update && apt-get install -y --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && apt-get install -y --no-install-recommends \
    libfreetype6 \
    libjpeg62-turbo \
    libpng16-16 \
    && a2enmod rewrite headers env dir mime alias \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && mkdir -p /tmp/privatebin \
    && curl -fsSL https://github.com/PrivateBin/PrivateBin/archive/${PRIVATEBIN_VERSION}.tar.gz | tar xz --strip-components=1 -C /tmp/privatebin \
    && mkdir -p /srv/privatebin /var/www/html \
    && mv /tmp/privatebin/bin /tmp/privatebin/cfg /tmp/privatebin/lib /tmp/privatebin/tpl /tmp/privatebin/vendor /srv/privatebin/ \
    && mkdir -p /srv/privatebin/data \
    && cp -r /tmp/privatebin/* /var/www/html/ 2>/dev/null || true \
    && rm -rf /tmp/privatebin \
    && sed -i "s|define('PATH', '');|define('PATH', '/srv/privatebin/');|" /var/www/html/index.php \
    && chown -R www-data:www-data /var/www/html /srv/privatebin \
    && chmod -R 755 /srv/privatebin \
    && apt-get purge -y --auto-remove libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Set working directory
WORKDIR /var/www/html

# Copy custom Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copy Prometheus metrics endpoint
COPY metrics.php /var/www/html/metrics.php
RUN chown www-data:www-data /var/www/html/metrics.php

# Copy entrypoint script for metrics access control
COPY metrics-entrypoint.sh /usr/local/bin/metrics-entrypoint.sh
RUN chmod +x /usr/local/bin/metrics-entrypoint.sh

# Create volume mount point for data only
VOLUME ["/srv/privatebin/data"]

# Expose port 80
EXPOSE 80

# Start Apache with metrics access control
CMD ["/usr/local/bin/metrics-entrypoint.sh"]
