<?php

// HABILITA EXIBIÇÃO DE ERROS COMPLETA
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('X-SmartApp-Version: antigo');

// Normalizar REQUEST_URI se contiver /public/ devido à reescrita do .htaccess do Apache
if (isset($_SERVER['REQUEST_URI'])) {
    if (strpos($_SERVER['REQUEST_URI'], '/antigo/public/') === 0) {
        $_SERVER['REQUEST_URI'] = str_replace('/antigo/public/', '/antigo/', $_SERVER['REQUEST_URI']);
    } elseif ($_SERVER['REQUEST_URI'] === '/antigo/public') {
        $_SERVER['REQUEST_URI'] = '/antigo/';
    }
}

// ==========================================
// CHECKPOINT 1: Início absoluto do arquivo
// Descomente a linha abaixo para testar
echo "1. Entrou no index.php do antigo";
die();
// ==========================================



use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Ajuste para rodar na subpasta /antigo corretamente (corrige roteamento e geração de URLs/redirects)
$_SERVER['SCRIPT_NAME'] = '/antigo/index.php';
$_SERVER['PHP_SELF'] = '/antigo/index.php';

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an error.
|
*/
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// ==========================================
// CHECKPOINT 2: Antes do autoload.php
// echo "2. Vai carregar autoload.php"; die();
// ==========================================
require __DIR__ . '/../vendor/autoload.php';

// ==========================================
// CHECKPOINT 3: Autoload carregado com sucesso
echo "3. Autoload carregado";
die();
// ==========================================

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request through
| the kernel, and send the associated response back to the client's browser
| allowing them to enjoy the creative and wonderful application we have prepared!
|
*/

$app = require_once __DIR__ . '/../bootstrap/app.php';

// ==========================================
// CHECKPOINT 4: App carregado (IoC instanciado)
echo "4. Instanciou App Laravel";
die();
// ==========================================

// Forçar o URL base para o subfolder /antigo (garante que redirect('/') vá para /antigo/)
$app->booted(function () {
    // ==========================================
    // CHECKPOINT 5: Bootstrap completo e executando boot
    echo "5. Executou booted callback";
    die();
    // ==========================================
    url()->forceRootUrl(rtrim(config('app.url'), '/') . '/antigo');
});

$kernel = $app->make(Kernel::class);

// ==========================================
// CHECKPOINT 6: Kernel instanciado, pronto para tratar a request
echo "6. Kernel instanciado. Tentando tratar request.";
die();
// ==========================================

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
