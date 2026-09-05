# SAPA — image produksi untuk Railway / host Docker apa pun.
# Aset (public/build) sudah di-commit, jadi TIDAK butuh Node/npm saat build.
FROM php:8.3-cli

# Ekstensi PHP yang dibutuhkan Laravel + Filament.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_sqlite mbstring gd zip bcmath exif \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Dependensi PHP produksi + izin folder.
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache database

ENV PORT=8080

# Saat container start: siapkan DB SQLite + data demo, link storage, lalu serve.
# migrate:fresh dipakai agar selalu bersih & tidak pernah error saat container restart.
CMD touch database/database.sqlite \
    && php artisan migrate:fresh --force --seed \
    && (php artisan storage:link || true) \
    && php artisan serve --host=0.0.0.0 --port=${PORT}
