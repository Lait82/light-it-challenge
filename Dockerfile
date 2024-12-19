# Usamos PHP 8.2 con FPM (FastCGI Process Manager)
FROM php:8.2-fpm

# Copiar los archivos composer.lock y composer.json al contenedor
COPY composer.lock composer.json /var/www/

# Establecer el directorio de trabajo dentro del contenedor
WORKDIR /var/www

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    nano \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP necesarias para Laravel
RUN docker-php-ext-install pdo_mysql zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Instalar Composer para la gestión de dependencias PHP
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Crear un usuario para la aplicación Laravel
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copiar todo el código de la aplicación al contenedor
COPY . /var/www

# Establecer los permisos apropiados para los archivos de la aplicación
COPY --chown=www:www . /var/www

# Cambiar al usuario www
USER www

# Exponer el puerto 9000 para PHP-FPM
EXPOSE 9000

# Comando para iniciar PHP-FPM
CMD ["php-fpm"]