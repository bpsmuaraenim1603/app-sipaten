# =========================
# 1) Node builder (Vite)
# =========================
FROM node:20-alpine AS node_builder
WORKDIR /app

COPY package.json package-lock.json* yarn.lock* pnpm-lock.yaml* ./
RUN \
  if [ -f package-lock.json ]; then npm ci; \
  elif [ -f yarn.lock ]; then yarn install --frozen-lockfile; \
  elif [ -f pnpm-lock.yaml ]; then corepack enable && pnpm install --frozen-lockfile; \
  else npm install; fi

COPY . .
RUN npm run build


FROM php:8.2-cli-alpine AS composer_builder
WORKDIR /app

# deps + ext
RUN apk add --no-cache \
    git curl unzip \
    icu-dev libzip-dev oniguruma-dev \
    freetype-dev libjpeg-turbo-dev libpng-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) mbstring zip intl gd pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ✅ copy semuanya dulu biar artisan ada
COPY . .

# ✅ baru install
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --no-scripts


# =========================
# 3) Runtime: PHP-FPM 8.2
# =========================
FROM php:8.2-fpm-alpine AS app

WORKDIR /var/www/html

# System deps + PHP extensions yang umum dipakai Laravel + maatwebsite/excel
RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    zip \
    unzip \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    zip \
    intl \
    gd \
    opcache \
  && rm -rf /tmp/*

# Copy project
COPY . .

# Copy vendor from composer stage
COPY --from=composer_builder /app/vendor ./vendor

# Copy built assets from node stage (umumnya output ke public/build)
COPY --from=node_builder /app/public/build ./public/build

# Permission: storage & cache writable
RUN chown -R www-data:www-data storage bootstrap/cache \
  && chmod -R 775 storage bootstrap/cache

# Copy entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
