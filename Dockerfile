FROM php:8.3-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar AllowOverride para .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copiar archivos del proyecto
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

# Puerto (Railway usa PORT env var)
ENV PORT=80
EXPOSE 80

# Usar el puerto de Railway si existe
RUN echo '#!/bin/bash\n\
if [ ! -z "$PORT" ]; then\n\
  sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf\n\
  sed -i "s/:80/:$PORT/" /etc/apache2/sites-available/000-default.conf\n\
fi\n\
apache2-foreground' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
