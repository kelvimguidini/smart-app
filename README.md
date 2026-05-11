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
- [Angular](https://angular.dev/) - Framework JS (SPA).
- Tailwind CSS & Bootstrap.
- (Legado: Vue.js)


## Instalação

Descrição do processo de instalação/configuração 

O projeto está configurado para rodar facilmente com **Docker Compose**, servindo tanto o backend Laravel (porta 8000) quanto o novo frontend Angular (porta 4201).

```sh
# Sobe o banco de dados, o backend e compila o Angular no modo watch (live-reload)
docker-compose up -d --build
```

O Frontend Angular estará disponível em: `http://localhost:4201`
O Backend Laravel estará disponível em: `http://localhost:8000`

Se precisar instalar dependências manualmente sem Docker:
```sh
composer install
cd frontend && npm install
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
php artisan migrate:fresh 
php artisan db:seed --class=CitySeeder
php artisan db:seed --class=VehicleSeeder
php artisan db:seed --class=DatabaseSeeder
php artisan db:seed --class=StatusHistorySeeder
php artisan db:seed --class=IncrementalPermissionsSeeder
php artisan db:seed --class=TransportSeeder

```

Usuário inicial: 
admin@admin.com'
Admin

