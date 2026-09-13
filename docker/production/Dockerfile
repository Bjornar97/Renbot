# Builder Stage
FROM dunglas/frankenphp:php8.4 AS builder

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    gnupg \
    postgresql-client \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    && install-php-extensions \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    opcache \
    intl \
    zip \
    bcmath \
    soap \
    redis \
    pcntl

# Install Node.js 24
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y nodejs

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install PHP dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

RUN npm ci
RUN npm run build


# Final Stage
FROM dunglas/frankenphp:php8.4

# Install runtime dependencies
RUN apt-get update && apt-get install -y \
    gosu \
    postgresql-client \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    && install-php-extensions \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    opcache \
    intl \
    zip \
    bcmath \
    soap \
    redis \
    pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Enable PHP production settings
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# FrankenPHP runs as www-data, so Caddy must own its certificate and config state.
RUN mkdir -p /data/caddy /config/caddy \
    && chown -R www-data:www-data /data /config

# Copy application from builder
# We copy the entire directory to ensure we have everything, 
# then remove node_modules which isn't needed in prod.
COPY --from=builder --chown=www-data:www-data /app /app

RUN rm -rf node_modules

# Storage template used when the Docker volume is empty
RUN cp -a storage storage-init

RUN chmod -R ug+rwX storage storage-init bootstrap/cache

# Entrypoint setup
COPY docker/production/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

# Environment variables
ENV SERVER_NAME=:80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
