FROM php:8.2-apache

# Install required PHP extensions and dependencies
RUN apt-get update && apt-get install -y \
    libgd-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    zlib1g-dev \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers env dir mime

# Set working directory
WORKDIR /var/www/html

# Download and install PrivateBin
ARG PRIVATEBIN_VERSION=2.0.3
RUN curl -L https://github.com/PrivateBin/PrivateBin/archive/${PRIVATEBIN_VERSION}.tar.gz | tar xz --strip-components=1 \
    && rm -rf .git* .dockerignore .scrutinizer.yml .styleci.yml .travis.yml phpunit.xml Dockerfile \
    && chown -R www-data:www-data /var/www/html

# Copy custom Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Create volume mount points
VOLUME ["/var/www/html/data", "/var/www/html/cfg"]

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
