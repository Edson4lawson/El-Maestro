FROM php:8.2-apache

# Installer les dépendances pour PostgreSQL et MySQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    gd \
    fileinfo \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Activer les modules Apache requis
RUN a2enmod rewrite headers

# Configurer le DocumentRoot d'Apache vers le dossier /api
ENV APACHE_DOCUMENT_ROOT /var/www/html/api
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configurer Directory Index et AllowOverride
RUN echo '<Directory /var/www/html/api>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/override.conf \
    && a2enconf override

# Copier le code source
WORKDIR /var/www/html
COPY . /var/www/html

# Permissions
RUN chown -R www-data:www-data /var/www/html

# Port Render (par défaut 80 ou PORT)
EXPOSE 80

CMD ["apache2-foreground"]
