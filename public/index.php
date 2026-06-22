<?php

header('X-SmartApp-Version: antigo');

// DEBUG DE ROTEAMENTO (TEMPORÁRIO)
echo "<h1>Conexao com o index.php do antigo bem-sucedida!</h1>";
echo "URI requisitada: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "PHP Self: " . $_SERVER['PHP_SELF'] . "<br>";
die();


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
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this package. We just need to utilize it! We'll simply require it into
| the script here so we don't need to manually load our classes.
|
*/
require __DIR__.'/../vendor/autoload.php';

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

$app = require_once __DIR__.'/../bootstrap/app.php';

// Forçar o URL base para o subfolder /antigo (garante que redirect('/') vá para /antigo/)
$app->booted(function () {
    url()->forceRootUrl(rtrim(config('app.url'), '/') . '/antigo');
});

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
