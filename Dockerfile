FROM php:8.1-apache

# Instalăm extensiile PHP (opțional, le poți lăsa pentru compatibilitate)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Activăm mod_rewrite pentru Apache
RUN a2enmod rewrite

# Copiem tot proiectul în container
COPY . /var/www/html/

# Permisiuni corecte
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configurăm Apache
RUN echo '<Directory /var/www/html/>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/docker-php.conf \
    && a2enconf docker-php

# Elimină warning-ul cu ServerName
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Setăm pagina principală (dacă vrei login.php prima, o poți adăuga aici)
RUN echo 'DirectoryIndex index.php index.html login.php' >> /etc/apache2/apache2.conf

EXPOSE 80
