FROM dunglas/frankenphp:php8.2.30-bookworm AS base

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions exif intl pcntl bcmath gd pdo_mysql zip fileinfo

WORKDIR /app
COPY php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/Caddyfile /etc/caddy/Caddyfile

FROM base AS vendor
WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

FROM node:22-bookworm-slim AS assets
WORKDIR /app

COPY package.json package-lock.json ./
COPY resources/plugins/jquery-validation-1.16.0/package.json resources/plugins/jquery-validation-1.16.0/package.json
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY webpack.mix.js ./
RUN npm run production && npm prune --omit=dev --ignore-scripts

FROM base AS app
WORKDIR /app

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public ./public
COPY docker/start.sh /usr/local/bin/start.sh

RUN rm -rf node_modules \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x /usr/local/bin/start.sh

ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]
