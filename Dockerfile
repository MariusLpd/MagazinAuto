# Folosim Apache simplu
FROM httpd:2.4

# Copiem tot proiectul în container
COPY . /usr/local/apache2/htdocs/

# Expunem portul 80
EXPOSE 80
