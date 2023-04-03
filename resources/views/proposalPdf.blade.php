<?php
$props = [
    'event' => $event,
    'provider' => $provider,
    'primaryColor' => '#000000',
    'secondaryColor' => '#FFFFFF',
    'accentColor' => '#FF0000'
];

function daysBetween($date1, $date2)
{
    // Convert both dates to UNIX timestamp
    $one = strtotime($date1);
    $two = strtotime($date2);
    // Calculate the difference in seconds
    $difference = abs($one - $two);
    // Convert back to days and return
    return ceil($difference / (60 * 60 * 24));
}

function formatCurrency($value, $symbol = 'BRL')
{
    $value = round($value * 100) / 100;
    return $symbol . ' ' . number_format($value, 2, ',', '.');
}

function unitSale($opt)
{
    $unitCost = $opt['received_proposal'] - (($opt['received_proposal'] * $opt['kickback']) / 100);
    return ceil($unitCost / $opt['received_proposal_percent']);
}

function sumTaxesProvider($eventP, $opt)
{
    return ((unitSale($opt) * $eventP['iss_percent']) / 100) + ((unitSale($opt) * $eventP['service_percent']) / 100) + ((unitSale($opt) * $eventP['iva_percent']) / 100);
}

function sumTotal($rate, $taxes, $qtdDayle)
{
    return (($rate + $taxes) * $qtdDayle);
}

function hexToRgb($hex, $a)
{
    // remover o sinal de jogo da frente, se existir
    $hex = str_replace('#', '', $hex);

    // converter o valor hexadecimal em valores RGB
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    // retornar a cor em formato RGB
    return 'rgba(' . $r . ', ' . $g . ', ' . $b . ', ' . $a . ')';
}


$sumTotalHotelValue = 0;
$sumQtdDayles = 0;
$sumValueRate = 0;

$sumTotalABValue = 0;
$sumABQtdDayles = 0;
$sumABValueRate = 0;

$sumTotalHallValue = 0;
$sumHallQtdDayles = 0;
$sumHallValueRate = 0;

$sumTotalAddValue = 0;
$sumAddQtdDayles = 0;
$sumAddValueRate = 0;


$hotelEvent = $event->event_hotels->firstWhere('hotel_id', $provider->id);
$abEvent = $event->event_abs->firstWhere('ab_id', $provider->id);
$hallEvent = $event->event_halls->firstWhere('hall_id', $provider->id);
$addEvent = $event->event_adds->firstWhere('add_id', $provider->id);

$sumValueRate = 0;
$sumQtdDayles = 0;
$sumTotalHotelValue = 0;
$sumABValueRate = 0;
$sumABQtdDayles = 0;
$sumTotalABValue = 0;
$sumHallValueRate = 0;
$sumHallQtdDayles = 0;
$sumTotalHallValue = 0;
$sumAddValueRate = 0;
$sumAddQtdDayles = 0;
$sumTotalAddValue = 0;

foreach ($hotelEvent->eventHotelsOpt as $item) {

    $rate = floatval($item->received_proposal);
    $taxes = floatval(sumTaxesProvider($hotelEvent, $item));
    $qtdDayle = $item->count * daysBetween($item->in, $item->out);

    $sumValueRate += $rate;
    $sumQtdDayles += $qtdDayle;
    $sumTotalHotelValue += ($rate + $taxes) * $qtdDayle;
}

foreach ($abEvent->eventAbOpts as $item) {
    $rate = floatval($item->received_proposal);
    $taxes = floatval(sumTaxesProvider($abEvent, $item));
    $qtdDayle = $item->count * daysBetween($item->in, $item->out);

    $sumABValueRate += $rate;
    $sumABQtdDayles += $qtdDayle;
    $sumTotalABValue += ($rate + $taxes) * $qtdDayle;
}

foreach ($hallEvent->eventHallOpts as $item) {
    $rate = floatval($item->received_proposal);
    $taxes = floatval(sumTaxesProvider($hallEvent, $item));

    $sumHallValueRate += $rate;
    $sumHallQtdDayles += daysBetween($item->in, $item->out);
    $sumTotalHallValue += ($rate + $taxes) * daysBetween($item->in, $item->out);
}

foreach ($addEvent->eventAddOpts as $item) {
    $rate = floatval($item->received_proposal);
    $taxes = floatval(sumTaxesProvider($hallEvent, $item));
    $qtdDayle = $item->count * daysBetween($item->in, $item->out);

    $sumAddValueRate += $rate;
    $sumAddQtdDayles += $qtdDayle;
    $sumTotalAddValue += ($rate + $taxes) * daysBetween($item->in, $item->out);
}
?>
<!DOCTYPE html>
<html>

<head>
    <style>
        /* estilos personalizados */
        .header {
            background-color: #FFF;
            /* fundo cinza claro */
            border-bottom: 5px solid #e9540d;
            /* detalhe laranja */
            padding: 1rem;
        }

        .logo {
            max-width: 200px;
        }


        tr {
            page-break-inside: avoid;
        }

        .resumo {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <div id="app">

        <div style="margin: 0 auto 2.5rem; max-width: 90%; padding: 0 1rem;margin-top: 27px;">
            <header class="header" style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center;">
                    <img style="width: 4rem; height: 4rem; margin-right: 0.5rem;" src="{{ $event['customer']['logo'] }}" alt="{{ $event['customer']['name'] }}">
                    <div>
                        <h1 style="font-weight: bold; font-size: 1.25rem; margin-bottom: 0.25rem;">Evento: {{ $event['name'] }}</h1>
                        <p style="color: #6B7280; font-size: 1rem;">Hotel: {{ $provider['name'] }}</p>
                    </div>
                </div>
                <img style="width: 5rem; height: 5rem; fill: #6B7280;" src="/storage/logos/logo.png" alt="Logo">
            </header>
        </div>

        <div style="overflow-x:auto;">
            @if($hotelEvent != null && $hotelEvent->eventHotelsOpt != null && count($hotelEvent->eventHotelsOpt) > 0)
            <table style="width: 100%; border-collapse: collapse; border: 1px solid gray;">
                <thead>
                    <tr style="background-image: linear-gradient(to right, <?php echo hexToRgb($event->customer->color, 0.7) ?>, <?php echo hexToRgb($event->customer->color, 0.3) ?>); color: white;">
                        <th colspan="11" style="padding: 0.5rem; text-align: center;">Hospedagem</th>
                    </tr>
                    <tr style="background-color: #f7fafc;">
                        <th style="border: 1px solid gray; padding: 0.5rem;">Cat. Apto</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Regime</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Tipo Apto</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">IN</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Out</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Qtd</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Diárias</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Valor</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Taxas</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">TTL com Taxa</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Total Geral</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hotelEvent->eventHotelsOpt as $key => $item)
                    <tr>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->category_hotel->name }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->regime->name }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->apto_hotel->name }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->in)) }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->out)) }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->count }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ daysBetween($item->in, $item->out) }}</td>

                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ formatCurrency($item->received_proposal, $hotelEvent->currency->symbol) }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">
                            {{ formatCurrency(sumTaxesProvider($hotelEvent, $item), $hotelEvent->currency->symbol) }}
                        </td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">
                            {{ formatCurrency($item->received_proposal + sumTaxesProvider($hotelEvent, $item), $hotelEvent->currency->symbol) }}
                        </td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ formatCurrency(sumTotal($item->received_proposal, sumTaxesProvider($hotelEvent, $item), $item->count * daysBetween($item->in, $item->out)), $hotelEvent->currency->symbol) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-footer">
                    <tr>
                        <td colspan="5" style="border: 1px solid gray; padding: 0.5rem;">
                            Diária Média: {{ formatCurrency($sumValueRate / count($hotelEvent->eventHotelsOpt), $hotelEvent->currency->symbol) }} Sem Taxas
                        </td>
                        <td colspan="2" style="border: 1px solid gray; padding: 0.5rem;">{{ $sumQtdDayles }} Room Nights</td>
                        <td colspan="3"></td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ formatCurrency($sumTotalHotelValue,  $hotelEvent->currency->symbol) }}</td>
                    </tr>

                    <tr>
                        <td style="border: 1px solid gray; padding: 0.5rem;">Comentários</td>
                        <td colspan="10" style="border: 1px solid gray; padding: 0.5rem;">{{ $hotelEvent->customer_observation }}</td>
                    </tr>
                </tfoot>

            </table>
            @endif


            @if($abEvent != null && $abEvent->eventAbOpts != null && count($abEvent->eventAbOpts) > 0)
            <table style="width: 100%; border-collapse: collapse; border: 1px solid gray;margin-top: 27px;">
                <thead>
                    <tr style="background-image: linear-gradient(to right, <?php echo hexToRgb($event->customer->color, 0.7) ?>, <?php echo hexToRgb($event->customer->color, 0.3) ?>); color: white;">
                        <th colspan="10" style="padding: 0.5rem; text-align: center;">Alimentos & Bebidas</th>
                    </tr>
                    <tr style="background-color: #f7fafc;">
                        <th style="border: 1px solid gray; padding: 0.5rem;">Refeição</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Local</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">De</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Até</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Qtd</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Diárias</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Valor</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Taxas</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">TTL com Taxa</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Total Geral</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($abEvent->eventAbOpts as $key => $item)
                    <tr>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->service_type->name }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->local->name }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->in)) }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->out)) }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->count }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ daysBetween($item->in, $item->out) }}</td>

                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ formatCurrency($item->received_proposal, $abEvent->currency->symbol) }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">
                            {{ formatCurrency(sumTaxesProvider($abEvent, $item), $abEvent->currency->symbol) }}
                        </td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">
                            {{ formatCurrency($item->received_proposal + sumTaxesProvider($abEvent, $item), $abEvent->currency->symbol) }}
                        </td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ formatCurrency(sumTotal($item->received_proposal, sumTaxesProvider($abEvent, $item), $item->count * daysBetween($item->in, $item->out)), $abEvent->currency->symbol) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-footer">
                    <tr>
                        <td colspan="4" style="border: 1px solid gray; padding: 0.5rem;">
                            Preço Medio por Refeição: {{ formatCurrency($sumABValueRate / count($abEvent->eventAbOpts), $abEvent->currency->symbol) }} Sem Taxas
                        </td>
                        <td colspan="2" style="border: 1px solid gray; padding: 0.5rem;">{{ $sumABQtdDayles }} Refeições</td>
                        <td colspan="3"></td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ formatCurrency($sumTotalABValue,  $abEvent->currency->symbol) }}</td>
                    </tr>

                    <tr>
                        <td style="border: 1px solid gray; padding: 0.5rem;">Comentários</td>
                        <td colspan="10" style="border: 1px solid gray; padding: 0.5rem;">{{ $abEvent->customer_observation }}</td>
                    </tr>
                </tfoot>

            </table>
            @endif


            @if($hallEvent != null && $hallEvent->eventHallOpts != null && count($hallEvent->eventHallOpts) > 0)
            <table style="width: 100%; border-collapse: collapse; border: 1px solid gray;margin-top: 27px;">
                <thead>
                    <tr style="background-image: linear-gradient(to right, <?php echo hexToRgb($event->customer->color, 0.7) ?>, <?php echo hexToRgb($event->customer->color, 0.3) ?>); color: white;">
                        <th colspan="11" style="padding: 0.5rem; text-align: center;">Salões & Eventos</th>
                    </tr>
                    <tr style="background-color: #f7fafc;">
                        <th style="border: 1px solid gray; padding: 0.5rem;">Nome</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Metragem</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Finalidade</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">De</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Até</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Qtd</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Diárias</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Valor</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Taxas</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">TTL com Taxa</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Total Geral</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hallEvent->eventHallOpts as $key => $item)
                    <tr>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->name }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->m2 }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->purpose->name }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->in)) }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->out)) }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->count }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ daysBetween($item->in, $item->out) }}</td>

                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ formatCurrency($item->received_proposal, $hallEvent->currency->symbol) }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">
                            {{ formatCurrency(sumTaxesProvider($hallEvent, $item), $hallEvent->currency->symbol) }}
                        </td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">
                            {{ formatCurrency($item->received_proposal + sumTaxesProvider($hallEvent, $item), $hallEvent->currency->symbol) }}
                        </td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ formatCurrency(sumTotal($item->received_proposal, sumTaxesProvider($hallEvent, $item), $item->count * daysBetween($item->in, $item->out)), $hallEvent->currency->symbol) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-footer">
                    <tr>
                        <td colspan="5" style="border: 1px solid gray; padding: 0.5rem;">
                            Diária Média dos Salões: {{ formatCurrency($sumHallValueRate / count($hallEvent->eventHallOpts), $hallEvent->currency->symbol) }} Sem Taxas
                        </td>
                        <td colspan="2" style="border: 1px solid gray; padding: 0.5rem;">{{ $sumHallQtdDayles }} Refeições</td>
                        <td colspan="3"></td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ formatCurrency($sumTotalHallValue,  $hallEvent->currency->symbol) }}</td>
                    </tr>

                    <tr>
                        <td style="border: 1px solid gray; padding: 0.5rem;">Comentários</td>
                        <td colspan="10" style="border: 1px solid gray; padding: 0.5rem;">{{ $hallEvent->customer_observation }}</td>
                    </tr>
                </tfoot>

            </table>
            @endif



            @if($addEvent != null && $addEvent->eventAddOpts != null && count($addEvent->eventAddOpts) > 0)
            <table style="width: 100%; border-collapse: collapse; border: 1px solid gray;margin-top: 27px;">
                <thead>
                    <tr style="background-image: linear-gradient(to right, <?php echo hexToRgb($event->customer->color, 0.7) ?>, <?php echo hexToRgb($event->customer->color, 0.3) ?>); color: white;">
                        <th colspan="11" style="padding: 0.5rem; text-align: center;">Adicionais</th>
                    </tr>
                    <tr style="background-color: #f7fafc;">
                        <th style="border: 1px solid gray; padding: 0.5rem;">Serviço</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Frequência</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Measure</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">De</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Até</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Qtd</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Diárias</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Valor</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Taxas</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">TTL com Taxa</th>
                        <th style="border: 1px solid gray; padding: 0.5rem;">Total Geral</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($addEvent->eventAddOpts as $key => $item)
                    <tr>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->service->name }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->frequency->name }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->measure->name }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->in)) }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->out)) }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ $item->count }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ daysBetween($item->in, $item->out) }}</td>

                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ formatCurrency($item->received_proposal, $addEvent->currency->symbol) }}</td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">
                            {{ formatCurrency(sumTaxesProvider($addEvent, $item), $addEvent->currency->symbol) }}
                        </td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">
                            {{ formatCurrency($item->received_proposal + sumTaxesProvider($addEvent, $item), $addEvent->currency->symbol) }}
                        </td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ formatCurrency(sumTotal($item->received_proposal, sumTaxesProvider($addEvent, $item), $item->count * daysBetween($item->in, $item->out)), $addEvent->currency->symbol) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-footer">
                    <tr>
                        <td colspan="5" style="border: 1px solid gray; padding: 0.5rem;">
                            Valor médio: {{ formatCurrency($sumAddValueRate / count($addEvent->eventAddOpts), $addEvent->currency->symbol) }} Sem Taxas
                        </td>
                        <td colspan="2" style="border: 1px solid gray; padding: 0.5rem;">{{ $sumAddQtdDayles }} Refeições</td>
                        <td colspan="3"></td>
                        <td style="border: 1px solid gray; padding: 0.5rem;">{{ formatCurrency($sumTotalAddValue,  $addEvent->currency->symbol) }}</td>
                    </tr>

                    <tr>
                        <td style="border: 1px solid gray; padding: 0.5rem;">Comentários</td>
                        <td colspan="10" style="border: 1px solid gray; padding: 0.5rem;">{{ $addEvent->customer_observation }}</td>
                    </tr>
                </tfoot>

            </table>
            @endif


            <table style="width:100%; border-collapse: collapse; border: 1px solid #ccc; margin-top: 4rem;">
                <thead>
                    <tr>
                        <th colspan="8" style="text-align:center; font-weight:bold;">
                            Resumo da Proposta
                        </th>
                    </tr>
                    <tr style="background-color: #ebf8ff;">
                        <th style="border: 1px solid #ccc; padding: 0.5rem;">Serviços</th>
                        <th style="border: 1px solid #ccc; padding: 0.5rem;" colspan="2">Totais de</th>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">Moeda</td>
                        <th style="border: 1px solid #ccc; padding: 0.5rem;">Preço médio</th>
                        <th style="border: 1px solid #ccc; padding: 0.5rem;">Total do Pedido</th>
                    </tr>
                </thead>
                <tbody>
                    @if($hotelEvent != null && $hotelEvent->eventHotelsOpt != null && count($hotelEvent->eventHotelsOpt) > 0)
                    <tr style="border: 1px solid #ccc;">
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">Hospedagem</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">Rooms Night</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ $sumQtdDayles}}</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ $hotelEvent->currency->sigla}}</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ formatCurrency($sumValueRate / count($hotelEvent->eventHotelsOpt))}}</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ formatCurrency($sumTotalHotelValue)}}</td>
                    </tr>
                    @endif

                    @if($abEvent != null && $abEvent->eventAbOpts != null && count($abEvent->eventAbOpts) > 0)
                    <tr style="border: 1px solid #ccc;">
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">Alimentos & Bebidas</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">Refeições</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ $sumABQtdDayles }}</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ $abEvent != null ? $abEvent->currency->sigla : ""}}</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ formatCurrency($sumABValueRate / count($abEvent->eventAbOpts))}}</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{formatCurrency($sumTotalABValue) }} </td>
                    </tr>
                    @endif


                    @if($hallEvent != null && $hallEvent->eventHallOpts != null && count($hallEvent->eventHallOpts) > 0)
                    <tr style="border: 1px solid #ccc;">
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">Alimentos & Bebidas</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">Refeições</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ $sumHallQtdDayles }}</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ $hallEvent != null ? $hallEvent->currency->sigla : ""}}</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ formatCurrency($sumHallValueRate / count($hallEvent->eventHallOpts))}}</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ formatCurrency($sumTotalHallValue) }} </td>
                    </tr>
                    @endif


                    @if($addEvent != null && $addEvent->eventAddOpts != null && count($addEvent->eventAddOpts) > 0)
                    <tr style="border: 1px solid #ccc;">
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">Alimentos & Bebidas</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">Refeições</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ $sumAddQtdDayles }}</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ $addEvent != null ? $addEvent->currency->sigla : ""}}</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ formatCurrency($sumAddValueRate / count($addEvent->eventAddOpts))}}</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ formatCurrency($sumTotalAddValue) }} </td>
                    </tr>
                    @endif
                </tbody>
                <tfoot class="table-footer">
                    <tr style="background-color: #ebf8ff;">
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">IOF</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ $event->iof }}</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">Taxa de Serviço</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ $event->service_charge }}</td>
                        <td></td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">Valor Total</td>
                    </tr>
                    <tr style="background-color: #f7fafc;">
                        <td style="border: 1px solid #ccc; padding: 0.5rem;" colspan="2">{{ formatCurrency((($sumTotalHotelValue +
                            $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue) * $event->iof) / 100) }}</td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;" colspan="2">{{ formatCurrency((($sumTotalHotelValue +
                            $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue) * $event->service_charge) / 100) }}</td>
                        <td></td>
                        <td style="border: 1px solid #ccc; padding: 0.5rem;">{{ formatCurrency($sumTotalHotelValue +
                            $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + ((($sumTotalHotelValue +
                            $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue) * $event->iof) / 100) +
                            ((($sumTotalHotelValue +
                            $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue) * $event->service_charge) / 100)) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <footer id="footer" style="bottom: 0; width: 100%;">
            <div style="padding-top: 400px !important; display: flex; justify-content: flex-end; align-items: center; border-top: 1px solid black; margin-top: 4rem;">
                <div style="font-size: small; font-weight: 500; color: #4b5563;">
                    <div style="display: flex; align-items: center;">
                        <p style="margin-right: 0.5rem;">PRAZO:</p>
                        <hr style="border-top: 1px solid black; width: 8rem; height: 0; flex-grow: 1;">
                    </div>
                </div>
            </div>
            <div style="padding-top: 100px; display: flex; justify-content: center; align-items: center; gap: 2rem; padding: 2rem 0; margin-top: 6rem;">
                <div style="width: 33.33%;">
                    <hr style="border-top: 1px solid black;">
                    <p style="text-align: center;">Autorizado por</p>
                </div>
                <div style="width: 33.33%;">
                    <hr style="border-top: 1px solid black;">
                    <p style="text-align: center;">Data da autorização</p>
                </div>
            </div>
            <div style="background-color: #1f2937; color: #d1d5db; padding: 1rem 2rem; padding-top: 1.5rem; margin-top: 6rem;">
                <div style="max-width: 64rem; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p>Avenida das Americas, 3434 - Bloco 5 - Grupo 520</p>
                        <p>Barra da Tijuca - Rio de Janeiro - 22.640-102</p>
                    </div>
                    <div>
                        <p>Tel.: (+55 21) 2025-7900</p>
                        <p>www.4BTS.com.br</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

</body>

</html>