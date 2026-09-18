# Builds the PHP website (talks to the "pocketbase" service over HTTP).
FROM php:8.2-apache

# cURL + its PHP extension are what our PocketBase.php client needs.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

EXPOSE 80
