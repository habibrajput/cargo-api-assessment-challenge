# Laravel on Octane (FrankenPHP): one long-running worker keeps prices in memory.
FROM dunglas/frankenphp:1-php8.3-alpine

RUN install-php-extensions @composer pcntl opcache

WORKDIR /app

# Install dependencies first so Docker can cache this step.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative \
    && cp .env.example .env \
    && php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache

EXPOSE 3142

# --workers=1         one shared copy of the prices
# --max-requests=0    never restart the worker, so the prices are kept
CMD ["php", "artisan", "octane:start", \
     "--server=frankenphp", \
     "--host=0.0.0.0", \
     "--port=3142", \
     "--workers=1", \
     "--max-requests=0"]
