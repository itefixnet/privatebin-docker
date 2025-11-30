FROM php:8.2-apache-alpine

# Install PHP extensions and download PrivateBin in a single layer
ARG PRIVATEBIN_VERSION=2.0.3
RUN apk add --no-cache --virtual .build-deps \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    && apk add --no-cache \
    freetype \
    libjpeg-turbo \
    libpng \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && a2enmod rewrite headers env dir mime \
    && curl -fsSL https://github.com/PrivateBin/PrivateBin/archive/${PRIVATEBIN_VERSION}.tar.gz | tar xz --strip-components=1 -C /var/www/html \
    && cd /var/www/html \
    && rm -rf .git* .dockerignore .scrutinizer.yml .styleci.yml .travis.yml phpunit.xml Dockerfile doc tst \
    && chown -R www-data:www-data /var/www/html \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/tmp/*

# Set working directory
WORKDIR /var/www/html

# Copy custom Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Create volume mount points
VOLUME ["/var/www/html/data", "/var/www/html/cfg"]

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
