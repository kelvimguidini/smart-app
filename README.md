# SMART
## _Sistema de controle de orçamento para eventos_


Esse documento visa descrever brevemente o sistema nos seguintes termos:

- Funcionalidades
- Tecnologias
- Instalação

## Funcionalidades

Lista de funcionalidades a ser encontrada nesse sistema

- Acesso a suas funcionalidades apenas aos usuários autenticados e autorizados.
- Manter os dados de login e criar novos logins e novos perfis de acesso (recuperação de senha, criação de perfil, criação/edição de usuário, deleção/inativação de usuário).


## Tech

Sistema está estruturado com as seguintes tecnologias:

backend
- [Laravel](https://laravel.com/docs) - Framework PHP
- PHP 8
- MySQL

frontend
- [vuejs](https://learnvue.co/) - Framework JS.


## Instalação

Descrição do processo de instalação/configuração 

Instalar as dependencias do projeto.

```sh
composer install
```

Criar base vazia em banco MySQL e configurar arquivo `.env` com os dados da conexão ao banco de dados

```sh
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apismart
DB_USERNAME=apismart
DB_PASSWORD=casanova
```

Executar migration para criação das tabelas do projeto

```sh
php artisan migrate:fresh --seed
```

Usuário inicial: 
admin@admin.com'
Admin



####### Executar ao commitar


php artisan db:seed --class=CitySeeder
php artisan db:seed --class=VehicleSeeder
php artisan db:seed --class=DatabaseSeeder
php artisan db:seed --class=StatusHistorySeeder
php artisan db:seed --class=IncrementalPermissionsSeeder
php artisan db:seed --class=TransportSeeder




enviar node_module
