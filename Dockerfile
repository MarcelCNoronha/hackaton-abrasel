FROM php:8.4-cli-alpine AS composer_deps
WORKDIR /app

RUN apk add --no-cache \
        freetype-dev \
        icu-dev \
        jpeg-dev \
        libpng-dev \
        libzip-dev \
        unzip \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        gd \
        intl \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader

FROM node:24-alpine AS frontend_build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY --from=composer_deps /app/vendor ./vendor
COPY postcss.config.js tailwind.config.js jsconfig.json* ./
COPY vite.config.js ./
# Vite inlines import.meta.env.VITE_* at build time, not runtime -- .env (with the real
# VITE_APP_NAME) is never copied into this stage, so without this every build silently baked
# in app.js's 'Laravel' fallback (visible as "Entrar - Laravel" in every page title).
ENV VITE_APP_NAME=VicosaFood
RUN npm run build

FROM php:8.4-fpm-alpine AS app
WORKDIR /var/www/html

RUN apk add --no-cache \
        $PHPIZE_DEPS \
        bash \
        curl \
        freetype-dev \
        icu-dev \
        jpeg-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
        oniguruma-dev \
        unzip \
        zip \
    && curl -fsSL https://github.com/phpredis/phpredis/archive/refs/tags/6.3.0.tar.gz \
        | tar xz -C /tmp \
    && cd /tmp/phpredis-6.3.0 && phpize && ./configure && make -j$(nproc) && make install \
    && docker-php-ext-enable redis \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        gd \
        intl \
        pdo_pgsql \
        zip \
    && apk del $PHPIZE_DEPS \
    && rm -rf /tmp/phpredis-6.3.0

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .
COPY --from=composer_deps /app/vendor ./vendor
COPY --from=frontend_build /app/public/build ./public/build
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
