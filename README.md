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
php artisan migrate --path=database/migrations/2023_04_13_011959_add_columns_to_provider_event_table.php

php artisan migrate --path=database/migrations/2023_04_13_012038_add_columns_to_email_log_table.php

php artisan migrate --path=database/migrations/2023_04_15_235223_create_provider_budget_table.php

php artisan migrate --path=database/migrations/2023_04_19_005915_create_provider_budget_item_table.php

php artisan migrate --path=database/migrations/2023_04_23_113450_add_columns_acepted.php

php artisan migrate --path=database/migrations/2023_05_01_182535_add_evaluated_and_approved_to_provider_budget_table.php

php artisan db:seed --class=IncrementalPermissionsSeeder

enviar node_module
