FROM php:8.1-apache

# Instalăm extensiile necesare PHP pentru MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Activăm mod_rewrite pentru Apache
RUN a2enmod rewrite

# Copiem proiectul în container
COPY . /var/www/html/

# Setăm permisiuni corecte
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configurăm Apache să permită .htaccess și indexurile
RUN echo '<Directory /var/www/html/>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/docker-php.conf \
    && a2enconf docker-php

# Setăm ordinea fișierelor implicite
RUN echo 'DirectoryIndex login.php index.php index.html' >> /etc/apache2/apache2.conf

# Evităm eroarea de ServerName
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Expunem portul 80
EXPOSE 80
