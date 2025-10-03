
README

Sistema de gestion de usuarios desarrollado con Laravel 12 como parte de la prueba tecnica para EML S.A.S. Este backend proporciona una API RESTful completa con autenticacion OAuth2 mediante Laravel Passport.

Funcionalidades Principales

 Autenticacion OAuth2 con Laravel Passport
 CRUD Completo de usuarios
 Validaciones robustas con expresiones regulares
 Ordenamiento alfabetico automatico (A-Z)
 Gestion de fechas (registro y ultima modificacion)
 Prevencion de emails duplicados
 Respuestas JSON estandarizadas.

Requisitos

PHP >= 8.1
Composer
MySQL >= 5.7
Git

Instalacion
1 Clonar el repositorio
git clone https://github.com/daymer1264-web/PruebaDaymerBackedn-eml.git
cd PruebaDaymer

2 Instalar dependencias
composer install

3 Configurar entorno

cp .env.example .env
php artisan key:generate
Editar .env con tus credenciales:
envDB_DATABASE=eml_users
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
