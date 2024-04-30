# Use a imagem oficial do PHP 8.1
FROM php:8.1

ENV COMPOSER_ALLOW_SUPERUSER=1

# Instale as dependências do Laravel e a extensão PDO MySQL
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install zip pdo_mysql

# Instale o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Defina o diretório de trabalho como o diretório do aplicativo Laravel
WORKDIR /var/www/html

# Copie os arquivos do aplicativo para o contêiner
COPY . .

# Instale as dependências do Composer
RUN composer update

# Defina as permissões adequadas
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Exponha a porta 8000 (ou a porta que sua aplicação utiliza)
EXPOSE 8000

# Instale o Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Configuração do Xdebug
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Comando padrão para executar a aplicação Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000
