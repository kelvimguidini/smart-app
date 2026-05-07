# Stage 1: Builder
FROM php:8.1 as builder

ENV COMPOSER_ALLOW_SUPERUSER=1

# Instale dependências PHP
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    zip \
    unzip \
    --no-install-recommends \
    && docker-php-ext-install zip pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instale Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /build

# Copie apenas composer.json e package.json
COPY composer.json composer.lock* ./
COPY package.json package-lock.json* ./

# Instale dependências PHP
RUN composer install --prefer-dist --no-progress --no-interaction --no-dev --no-scripts

# Instale Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs --no-install-recommends && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instale dependências NPM
RUN npm install --legacy-peer-deps

# ---
# Stage 2: Runtime
FROM php:8.1

ENV COMPOSER_ALLOW_SUPERUSER=1

# Instale apenas extensões necessárias
RUN apt-get update && apt-get install -y \
    curl \
    git \
    libzip-dev \
    --no-install-recommends \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs --no-install-recommends \
    && docker-php-ext-install zip pdo_mysql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configuração do Xdebug
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Instale Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copie vendor e node_modules do builder
COPY --from=builder /build/vendor ./vendor
COPY --from=builder /build/node_modules ./node_modules

# Copie o restante do projeto
COPY . .

# Instale dependências de produção (após copiar tudo)
RUN composer install --prefer-dist --no-progress --no-interaction --no-dev && \
    npm install --legacy-peer-deps && \
    npm run build && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

