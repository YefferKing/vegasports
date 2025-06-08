#!/bin/bash
set -e # Detiene el script si hay un error

# Instalar phpbrew
curl -s https://raw.githubusercontent.com/phpbrew/phpbrew/master/phpbrew.sh  | bash

# Agregar phpbrew al PATH
source ~/.phpbrew/bashrc

# Seleccionar versión de PHP
phpbrew switch 8.2

# Descargar Composer
php -r "copy('https://getcomposer.org/installer',  'composer-setup.php');"
php composer-setup.php --install-dir=. --filename=composer

# Eliminar setup después de instalar
rm -rf composer-setup.php

# Instalar dependencias
php composer install --no-dev --optimize-autoloader