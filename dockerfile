FROM php:8.2-apache

# Instala as extensões PHP necessárias para conectar ao MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli \
    && docker-php-ext-enable pdo_mysql mysqli

# Habilita o mod_rewrite do Apache (útil para URLs amigáveis no futuro)
RUN a2enmod rewrite

# (Opcional) Configura o timezone do PHP para São Paulo
RUN echo "date.timezone = America/Sao_Paulo" > /usr/local/etc/php/conf.d/timezone.ini