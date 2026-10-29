# ── Base image ──────────────────────────────────────────────────
FROM php:8.2-apache

# ── Layer 1: System packages (slow — cached unless this block changes) ──
# Install system-level libraries needed by PHP extensions.
# This layer is rebuilt only when you add/remove system packages.
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    zip \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

# ── Layer 2: PHP extensions (slow — cached unless extensions change) ──
# Compiled PHP extensions. Rebuilt only when you add/remove extensions.
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip

# ── Layer 3: Apache config (cached unless rewrite rules change) ──
# Enable mod_rewrite for .htaccess URL routing (required by UniHelp)
RUN a2enmod rewrite \
    && sed -i 's|AllowOverride None|AllowOverride All|g' \
       /etc/apache2/apache2.conf

# ── Layer 4: PHP ini overrides (cached unless php.ini changes) ──
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# ── Layer 5: App code (fast — only layer that changes often) ────
# In development: overridden by volume mount in docker-compose.yml
# In production:  this bakes the code into the image
COPY . /var/www/html/unicopy

EXPOSE 80
