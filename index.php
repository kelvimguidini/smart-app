<?php
ob_start();
session_start();

setlocale(LC_ALL, 'pt_BR.utf-8', 'pt_BR', 'Portuguese_Brazil');
error_reporting(0);

require __DIR__ . "/vendor/autoload.php";

use CoffeeCode\Router\Router;

$router = new Router(site());

$router->namespace("Source\Controllers");

// web pages
$router->group(null);
$router->get("/", "Web:home", "web.home");

$router->group('ecommerce');
$router->get("/{folder}/checkout/hotel/{idHotel}/{idQuarto}/{idTarifa}/{qtdQuartos}/{qtdNoites}/{qtdPax}/{checkin}/{checkout}/{qtdCrianca}", "Hotel:only", "hotel.only");
$router->get("/{folder}/checkout/hoteis", "Hotel:multiples", "hotel.multiples");

$router->post("/{folder}/checkout/hotel/gerapedido", "Hotel:process", "hotel.process");

/*
$router->get("/{folder}/checkout/servico/{evento}/{box}/{data}/{pax}", "Ecommerce:servico", "ecommerce.servico");
$router->get("/{folder}/checkout", "Ecommerce:checkout", "ecommerce.checkout");
*/
//web ajax
$router->group('ajax');
$router->get("/{method}", "Ajax:router", "ajax.router");
$router->post("/{method}", "Ajax:router", "ajax.router");

//web errors
$router->group("ops");
$router->get("/{errcode}", "Web:error", "web.error");

/**
 * API
 */
$router->namespace("Source\Controllers\aereo");
$router->group("api");
$router->get("/aereo/aeroportos/{pesquisa}/direcao/{direcao}", "Aereo:aeroportos", "aereo.aeroportos");
$router->post("/aereo/disponibilidade", "Aereo:disponibilidade", "aereo.disponibilidade");
$router->post("/aereo/regras", "Aereo:regras", "aereo.regras");

$router->namespace("Source\Controllers\booking");

$router->get("/aereo/listar", "Booking:list", "booking.list");
$router->get("/aereo/listar/reservas", "Booking:reservations", "booking.reservations");
//$router->post("/detalhes", "Booking:details", "booking.details");

$router->post("/aereo/tarifar", "Booking:toTariff", "booking.toTariff");
$router->post("/aereo/salvar", "Booking:save", "booking.save");
$router->post("/aereo/emissao/{idAuth}/{idvoo}", "Booking:toIssue", "booking.toIssue");

/*** ROUTER PROCESS*/
$router->dispatch();

/*** ERRORS PROCESS */
if ($router->error()) {
    $router->redirect("web.error", ["errcode" => $router->error()]);
}

ob_end_flush();
