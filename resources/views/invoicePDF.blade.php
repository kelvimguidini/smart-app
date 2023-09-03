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

$sumTransportValueRate = 0;
$sumTransportQtdDayles = 0;
$sumTotalTransportValue = 0;

$percIOF = 0;
$percService = 10;


$strip = false;

$currency_name = 'Reais';

$hotelEvent = $event->event_hotels->firstWhere('hotel_id', $provider->id);
$abEvent = $event->event_abs->firstWhere('ab_id', $provider->id);
$hallEvent = $event->event_halls->firstWhere('hall_id', $provider->id);
$addEvent = $event->event_adds->firstWhere('add_id', $provider->id);
$transportEvent = $event->event_transports->firstWhere('transport_id', $provider->id);

$strip = false;

if ($hotelEvent != null) {
    if ($hotelEvent->iof > 0) {
        $percIOF = $hotelEvent->iof;
    }
    if ($hotelEvent->service_charge > 0) {
        $percService = $hotelEvent->service_charge;
    }

    foreach ($hotelEvent->eventHotelsOpt as $item) {

        $rate = floatval($item->received_proposal);
        $taxes = floatval(sumTaxesProvider($hotelEvent, $item));
        $qtdDayle = $item->count * daysBetween($item->in, $item->out);

        $sumValueRate += $rate;
        $sumQtdDayles += $qtdDayle;
        $sumTotalHotelValue += ($rate + $taxes) * $qtdDayle;
    }
}
if ($abEvent != null) {
    if ($abEvent->iof > 0) {
        $percIOF = $abEvent->iof;
    }
    if ($abEvent->service_charge > 0) {
        $percService = $abEvent->service_charge;
    }
    foreach ($abEvent->eventAbOpts as $item) {
        $rate = floatval($item->received_proposal);
        $taxes = floatval(sumTaxesProvider($abEvent, $item));
        $qtdDayle = $item->count * daysBetween($item->in, $item->out);

        $sumABValueRate += $rate;
        $sumABQtdDayles += $qtdDayle;
        $sumTotalABValue += ($rate + $taxes) * $qtdDayle;
    }
}
if ($hallEvent != null) {
    if ($hallEvent->iof > 0) {
        $percIOF = $hallEvent->iof;
    }
    if ($hallEvent->service_charge > 0) {
        $percService = $hallEvent->service_charge;
    }
    foreach ($hallEvent->eventHallOpts as $item) {
        $rate = floatval($item->received_proposal);
        $taxes = floatval(sumTaxesProvider($hallEvent, $item));

        $sumHallValueRate += $rate;
        $sumHallQtdDayles += daysBetween($item->in, $item->out);
        $sumTotalHallValue += ($rate + $taxes) * daysBetween($item->in, $item->out);
    }
}
if ($addEvent != null) {
    if ($addEvent->iof > 0) {
        $percIOF = $addEvent->iof;
    }
    if ($addEvent->service_charge > 0) {
        $percService = $addEvent->service_charge;
    }
    foreach ($addEvent->eventAddOpts as $item) {
        $rate = floatval($item->received_proposal);
        $taxes = floatval(sumTaxesProvider($hallEvent, $item));
        $qtdDayle = $item->count * daysBetween($item->in, $item->out);

        $sumAddValueRate += $rate;
        $sumAddQtdDayles += $qtdDayle;
        $sumTotalAddValue += ($rate + $taxes) * daysBetween($item->in, $item->out);
    }
}
if ($transportEvent != null) {
    if ($transportEvent->iof > 0) {
        $percIOF = $transportEvent->iof;
    }
    if ($transportEvent->service_charge > 0) {
        $percService = $transportEvent->service_charge;
    }
    foreach ($transportEvent->eventTransportOpts as $item) {

        $rate = floatval($item->received_proposal);
        $taxes = floatval(sumTaxesProvider($transportEvent, $item));
        $qtdDayle = $item->count * daysBetween($item->in, $item->out);

        $sumTransportValueRate += $rate;
        $sumTransportQtdDayles += $qtdDayle;
        $sumTotalTransportValue += ($rate + $taxes) * daysBetween($item->in, $item->out);
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 10px;
        }

        body {
            font-family: Arial, sans-serif;
        }

        table {
            font-size: 10pt;
            max-width: 19cm;
            min-width: 19cm;
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
        }

        th,
        td {
            padding: 0.5rem;
            height: 30px;
        }

        th {
            text-align: center;
        }

        tbody tr:nth-child(odd) {
            background-color: #f7fafc;
        }

        tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        tfoot tr {
            background-color: #ebf8ff;
        }

        tfoot td {
            height: 30px;
        }

        .table-footer {
            background-color: #ffe0b1;
        }

        .header {
            background: #e8e8e8;
            padding: 10px;
            width: 100%;
            margin: -10px;
            margin-bottom: 40px;
            height: 175px;
        }

        .left {
            float: left;
            width: calc(100% - 180px);
        }

        .arrow {
            display: inline-block;
            margin: 15px 0 0 -19px;
            padding: 15px 50px;
            background-color: #e9540d;
            position: relative;
        }

        .arrow:before {
            content: "";
            position: absolute;
            top: 0px;
            left: 100%;
            border: 33px solid transparent;
            border-left: 45px solid #e9540d;
        }

        .title {
            font-weight: 900;
            font-style: normal;
            color: rgb(250, 249, 249);
            text-decoration: none;
            font-size: 30px;
            margin: 0;
        }

        .event-info {
            margin-left: 20px;
        }

        .line {
            margin-bottom: 10px;
            white-space: nowrap;
        }

        .line p {
            display: inline-block;
            font-weight: 600;
            font-style: normal;
            color: rgb(0, 0, 0);
            text-decoration: none;
            margin-bottom: 0;
            margin-right: 10px;
        }

        .event-data {
            font-weight: 700;
            font-style: normal;
            color: rgb(216, 93, 16);
            text-decoration: none;
            margin: 0;
        }

        .right {
            transform: translateY(50%);
            width: 150px;
            float: right;
        }

        .row {
            width: 100%;
        }

        #footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 14px;
            margin-bottom: -10px;
        }

        .custom-bg-success-text-white {
            background-color: #28a745;
            color: #fff;
        }
    </style>
</head>

<body>

    <body>

        <div id="app">

            <header class="header">
                <div class="left">
                    <div class="seta">
                        <div class="arrow">
                            <div class="title">Faturamento Hotel {{ $event->code }}</div>
                        </div>
                    </div>

                    <div class="event-info"></div>
                </div>
                <div class="right">
                    <img src="{{ asset($event->customer->logo) }}" style="width: 150px;" alt="{{ $event->customer->name}}">
                </div>
            </header>

            <div>
                <table style="border: 1px;">
                    <tr>
                        <th>Evento:</th>
                        <td>{{ $event->name }}</td>
                        <th>Moeda:</th>
                        <td>{{$currency_name}}</td>
                    </tr>
                    <tr>
                        <th>Data:</th>
                        <td>{{date("d/m/Y")}}</td>
                        <th>Base de Pax:</th>
                        <td>{{$event->pax_base}}</td>
                    </tr>
                    <tr>
                        <th>CRD:</th>
                        <td>{{$event->crd->number}} - {{$event->crd->name}}</td>
                        <th>Câmbio:</th>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Centro Custo Cliente:</th>
                        <td>{{$event->cost_center}}</td>
                        <th>Solicitante:</th>
                        <td>{{$event->requester}}</td>
                    </tr>
                </table>

                @if($hotelEvent != null && $hotelEvent->eventHotelsOpt != null && count($hotelEvent->eventHotelsOpt) > 0)
                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="11" style="padding: 0.5rem; text-align: center;">Hospedagem</th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th>Cat. Apto</th>
                            <th>Regime</th>
                            <th>Tipo Apto</th>
                            <th>IN</th>
                            <th>Out</th>
                            <th>Qtd</th>
                            <th>Diárias</th>
                            <th>Valor</th>
                            <th>Taxas</th>
                            <th>TTL com Taxa</th>
                            <th>Total Geral</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hotelEvent->eventHotelsOpt as $key => $item)

                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->category_hotel->name }}</td>
                            <td>{{ $item->regime->name }}</td>
                            <td>{{ $item->apto_hotel->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency($item->received_proposal, $hotelEvent->currency->symbol) }}</td>
                            <td>
                                {{ formatCurrency(sumTaxesProvider($hotelEvent, $item), $hotelEvent->currency->symbol) }}
                            </td>
                            <td>
                                {{ formatCurrency($item->received_proposal + sumTaxesProvider($hotelEvent, $item), $hotelEvent->currency->symbol) }}
                            </td>
                            <td>{{ formatCurrency(sumTotal($item->received_proposal, sumTaxesProvider($hotelEvent, $item), $item->count * daysBetween($item->in, $item->out)), $hotelEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="11" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr class="table-subheader">
                                        <td class="align-middle custom-bg-success-text-white">
                                            Room Nights:
                                        </td>
                                        <td class="align-middle">{{ $sumQtdDayles }}</td>
                                        <td class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</td>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumValueRate / $sumQtdDayles, $hotelEvent->currency->symbol) }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            EMITIR NOTA FISCAL?
                                        </td>
                                        <td class="align-middle">
                                            {{ $hotelEvent->invoice ? 'Sim' : 'Não' }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            Total Venda
                                        </td>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumTotalHotelValue, $hotelEvent->currency->symbol) }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            Total Custo
                                        </td>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumValueRate, $hotelEvent->currency->symbol) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="9">{{ $hotelEvent->internal_observation }}</td>
                        </tr>

                        <tr>
                            <td colspan="11" style="padding: 0;">
                                <table>
                                    <tr>
                                        <th colspan="3">IMPOSTOS CLIENTE</th>
                                        <th colspan="3">IMPOSTOS A PAGAR</th>
                                        <th colspan="2">COMISSÃO</th>
                                        <th>TOTAL DE VENDA</th>
                                        <th>TOTAL A PAGAR</th>
                                        <th>LUCRO TOTAL</th>
                                    </tr>
                                    <tr>
                                        <th>ISS %</th>
                                        <th>TAXA DE SERVIÇO</th>
                                        <th>IVA</th>

                                        <th>ISS %</th>
                                        <th>TAXA DE SERVIÇO</th>
                                        <th>IVA</th>

                                        <th>TAXA 4BTS</th>
                                        <th>10%</th>

                                        <th>TOTAL COM TAXAS & IMPOSTOS</th>
                                        <th>TOTAL COM TAXAS & IMPOSTOS</th>
                                        <th>COMISSÃO & TAXAS INCLUSAS</th>
                                    </tr>
                                    <tr>
                                        <td>4.372,95</td>
                                        <td>4.372,95</td>
                                        <td>4.372,95</td>

                                        <td>3.493,80</td>
                                        <td>3.493,80</td>
                                        <td>3.493,80</td>

                                        <td colspan="2">10.057,79</td>

                                        <td>118.380,13</td>
                                        <td>85.982,42</td>
                                        <td>39.385,31</td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </tfoot>
                </table>
                @endif


                @if($abEvent != null && $abEvent->eventAbOpts != null && count($abEvent->eventAbOpts) > 0)
                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="10" style="padding: 0.5rem; text-align: center;">Alimentos & Bebidas</th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th>Refeição</th>
                            <th>Local</th>
                            <th>De</th>
                            <th>Até</th>
                            <th>Qtd</th>
                            <th>Diárias</th>
                            <th>Valor</th>
                            <th>Taxas</th>
                            <th>TTL com Taxa</th>
                            <th>Total Geral</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($abEvent->eventAbOpts as $key => $item)

                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->service_type->name }}</td>
                            <td>{{ $item->local->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency($item->received_proposal, $abEvent->currency->symbol) }}</td>
                            <td>
                                {{ formatCurrency(sumTaxesProvider($abEvent, $item), $abEvent->currency->symbol) }}
                            </td>
                            <td>
                                {{ formatCurrency($item->received_proposal + sumTaxesProvider($abEvent, $item), $abEvent->currency->symbol) }}
                            </td>
                            <td>{{ formatCurrency(sumTotal($item->received_proposal, sumTaxesProvider($abEvent, $item), $item->count * daysBetween($item->in, $item->out)), $abEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="10" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr class="table-subheader">
                                        <td class="align-middle custom-bg-success-text-white">
                                            Room Nights:
                                        </td>
                                        <td class="align-middle">{{ $sumABQtdDayles }}</td>
                                        <td class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</td>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumABValueRate / $sumABQtdDayles, $abEvent->currency->symbol) }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            EMITIR NOTA FISCAL?
                                        </td>
                                        <td class="align-middle">
                                            {{ $abEvent->invoice ? 'Sim' : 'Não' }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            Total Venda
                                        </td>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumABValueRate) }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            Total Custo
                                        </td>
                                        <td class="align-middle">
                                            {{ $sumTotalABValue }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            Total Custo
                                        </td>
                                        <td class="align-middle">
                                            {{ $sumTotalABValue }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="8">{{ $abEvent->internal_observation }}</td>
                        </tr>


                        <tr>
                            <td colspan="10" style="padding: 0;">
                                <table>
                                    <tr>
                                        <th colspan="3">IMPOSTOS CLIENTE</th>
                                        <th colspan="3">IMPOSTOS A PAGAR</th>
                                        <th colspan="2">COMISSÃO</th>
                                        <th>TOTAL DE VENDA</th>
                                        <th>TOTAL A PAGAR</th>
                                        <th>LUCRO TOTAL</th>
                                    </tr>
                                    <tr>
                                        <th>ISS %</th>
                                        <th>TAXA DE SERVIÇO</th>
                                        <th>IVA</th>

                                        <th>ISS %</th>
                                        <th>TAXA DE SERVIÇO</th>
                                        <th>IVA</th>

                                        <th>TAXA 4BTS</th>
                                        <th>10%</th>

                                        <th>TOTAL COM TAXAS & IMPOSTOS</th>
                                        <th>TOTAL COM TAXAS & IMPOSTOS</th>
                                        <th>COMISSÃO & TAXAS INCLUSAS</th>
                                    </tr>
                                    <tr>
                                        <td>4.372,95</td>
                                        <td>4.372,95</td>
                                        <td>4.372,95</td>

                                        <td>3.493,80</td>
                                        <td>3.493,80</td>
                                        <td>3.493,80</td>

                                        <td colspan="2">10.057,79</td>

                                        <td>118.380,13</td>
                                        <td>85.982,42</td>
                                        <td>39.385,31</td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </tfoot>

                </table>
                @endif


                @if($hallEvent != null && $hallEvent->eventHallOpts != null && count($hallEvent->eventHallOpts) > 0)
                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="11" style="padding: 0.5rem; text-align: center;">Salões & Eventos {{$hallEvent->hall->name }}</th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th>Nome</th>
                            <th>Metragem</th>
                            <th>Finalidade</th>
                            <th>De</th>
                            <th>Até</th>
                            <th>Qtd</th>
                            <th>Diárias</th>
                            <th>Valor</th>
                            <th>Taxas</th>
                            <th>TTL com Taxa</th>
                            <th>Total Geral</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hallEvent->eventHallOpts as $key => $item)

                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->m2 }}</td>
                            <td>{{ $item->purpose->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency($item->received_proposal, $hallEvent->currency->symbol) }}</td>
                            <td>
                                {{ formatCurrency(sumTaxesProvider($hallEvent, $item), $hallEvent->currency->symbol) }}
                            </td>
                            <td>
                                {{ formatCurrency($item->received_proposal + sumTaxesProvider($hallEvent, $item), $hallEvent->currency->symbol) }}
                            </td>
                            <td>{{ formatCurrency(sumTotal($item->received_proposal, sumTaxesProvider($hallEvent, $item), $item->count * daysBetween($item->in, $item->out)), $hallEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="11" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr class="table-subheader">
                                        <td class="align-middle custom-bg-success-text-white">
                                            Room Nights:
                                        </td>
                                        <td class="align-middle">{{ $sumQtdDayles }}</td>
                                        <td class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</td>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumValueRate / $sumQtdDayles, $hotelEvent->currency->symbol) }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            EMITIR NOTA FISCAL?
                                        </td>
                                        <td class="align-middle">
                                            {{ $hotelEvent->invoice ? 'Sim' : 'Não' }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            Total Venda
                                        </td>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumValueRate) }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            Total Custo
                                        </td>
                                        <td class="align-middle">
                                            {{ $sumTotalHotelValue }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            Total Custo
                                        </td>
                                        <td class="align-middle">
                                            {{ $sumTotalHotelValue }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="9">{{ $hallEvent->internal_observation }}</td>
                        </tr>


                        <tr>
                            <td colspan="11" style="padding: 0;">
                                <table>
                                    <tr>
                                        <th colspan="3">IMPOSTOS CLIENTE</th>
                                        <th colspan="3">IMPOSTOS A PAGAR</th>
                                        <th colspan="2">COMISSÃO</th>
                                        <th>TOTAL DE VENDA</th>
                                        <th>TOTAL A PAGAR</th>
                                        <th>LUCRO TOTAL</th>
                                    </tr>
                                    <tr>
                                        <th>ISS %</th>
                                        <th>TAXA DE SERVIÇO</th>
                                        <th>IVA</th>

                                        <th>ISS %</th>
                                        <th>TAXA DE SERVIÇO</th>
                                        <th>IVA</th>

                                        <th>TAXA 4BTS</th>
                                        <th>10%</th>

                                        <th>TOTAL COM TAXAS & IMPOSTOS</th>
                                        <th>TOTAL COM TAXAS & IMPOSTOS</th>
                                        <th>COMISSÃO & TAXAS INCLUSAS</th>
                                    </tr>
                                    <tr>
                                        <td>4.372,95</td>
                                        <td>4.372,95</td>
                                        <td>4.372,95</td>

                                        <td>3.493,80</td>
                                        <td>3.493,80</td>
                                        <td>3.493,80</td>

                                        <td colspan="2">10.057,79</td>

                                        <td>118.380,13</td>
                                        <td>85.982,42</td>
                                        <td>39.385,31</td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </tfoot>

                </table>
                @endif


                @if($addEvent != null && $addEvent->eventAddOpts != null && count($addEvent->eventAddOpts) > 0)

                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="11" style="padding: 0.5rem; text-align: center;">Adicionais {{$addEvent->add->name }}</th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th>Serviço</th>
                            <th>Frequência</th>
                            <th>Measure</th>
                            <th>De</th>
                            <th>Até</th>
                            <th>Qtd</th>
                            <th>Diárias</th>
                            <th>Valor</th>
                            <th>Taxas</th>
                            <th>TTL com Taxa</th>
                            <th>Total Geral</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($addEvent->eventAddOpts as $key => $item)

                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->service->name }}</td>
                            <td>{{ $item->frequency->name }}</td>
                            <td>{{ $item->measure->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency($item->received_proposal, $addEvent->currency->symbol) }}</td>
                            <td>
                                {{ formatCurrency(sumTaxesProvider($addEvent, $item), $addEvent->currency->symbol) }}
                            </td>
                            <td>
                                {{ formatCurrency($item->received_proposal + sumTaxesProvider($addEvent, $item), $addEvent->currency->symbol) }}
                            </td>
                            <td>{{ formatCurrency(sumTotal($item->received_proposal, sumTaxesProvider($addEvent, $item), $item->count * daysBetween($item->in, $item->out)), $addEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">

                        <tr>
                            <td colspan="11" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr class="table-subheader">
                                        <td class="align-middle custom-bg-success-text-white">
                                            Room Nights:
                                        </td>
                                        <td class="align-middle">{{ $sumAddQtdDayles }}</td>
                                        <td class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</td>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumAddValueRate / $sumAddQtdDayles, $addEvent->currency->symbol) }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            EMITIR NOTA FISCAL?
                                        </td>
                                        <td class="align-middle">
                                            {{ $hotelEvent->invoice ? 'Sim' : 'Não' }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            Total Venda
                                        </td>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumAddValueRate) }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            Total Custo
                                        </td>
                                        <td class="align-middle">
                                            {{ $sumAddValueRate }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            Total Custo
                                        </td>
                                        <td class="align-middle">
                                            {{ $sumAddValueRate }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="9">{{ $addEvent->internal_observation }}</td>
                        </tr>


                        <tr>
                            <td colspan="11" style="padding: 0;">
                                <table>
                                    <tr>
                                        <th colspan="3">IMPOSTOS CLIENTE</th>
                                        <th colspan="3">IMPOSTOS A PAGAR</th>
                                        <th colspan="2">COMISSÃO</th>
                                        <th>TOTAL DE VENDA</th>
                                        <th>TOTAL A PAGAR</th>
                                        <th>LUCRO TOTAL</th>
                                    </tr>
                                    <tr>
                                        <th>ISS %</th>
                                        <th>TAXA DE SERVIÇO</th>
                                        <th>IVA</th>

                                        <th>ISS %</th>
                                        <th>TAXA DE SERVIÇO</th>
                                        <th>IVA</th>

                                        <th>TAXA 4BTS</th>
                                        <th>10%</th>

                                        <th>TOTAL COM TAXAS & IMPOSTOS</th>
                                        <th>TOTAL COM TAXAS & IMPOSTOS</th>
                                        <th>COMISSÃO & TAXAS INCLUSAS</th>
                                    </tr>
                                    <tr>
                                        <td>4.372,95</td>
                                        <td>4.372,95</td>
                                        <td>4.372,95</td>

                                        <td>3.493,80</td>
                                        <td>3.493,80</td>
                                        <td>3.493,80</td>

                                        <td colspan="2">10.057,79</td>

                                        <td>118.380,13</td>
                                        <td>85.982,42</td>
                                        <td>39.385,31</td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </tfoot>

                </table>
                @endif

                @if($transportEvent != null && $transportEvent->eventTransportOpts != null && count($transportEvent->eventTransportOpts) > 0)

                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="12" style="padding: 0.5rem; text-align: center;">Transporte Terrestre {{$transportEvent->transport->name }}</th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th>Marca</th>
                            <th>Veículo</th>
                            <th>Modelo</th>
                            <th>Serviço</th>
                            <th>De</th>
                            <th>Até</th>
                            <th>Qtd</th>
                            <th>Diárias</th>
                            <th>Valor</th>
                            <th>Taxas</th>
                            <th>TTL com Taxa</th>
                            <th>Total Geral</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transportEvent->eventTransportOpts as $key => $item)

                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->brand->name }}</td>
                            <td>{{ $item->vehicle->name }}</td>
                            <td>{{ $item->model->name }}</td>
                            <td>{{ $item->service->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency($item->received_proposal, $transportEvent->currency->symbol) }}</td>
                            <td>
                                {{ formatCurrency(sumTaxesProvider($transportEvent, $item), $transportEvent->currency->symbol) }}
                            </td>
                            <td>
                                {{ formatCurrency($item->received_proposal + sumTaxesProvider($transportEvent, $item), $transportEvent->currency->symbol) }}
                            </td>
                            <td>{{ formatCurrency(sumTotal($item->received_proposal, sumTaxesProvider($transportEvent, $item), $item->count * daysBetween($item->in, $item->out)), $transportEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="11" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr class="table-subheader">
                                        <td class="align-middle custom-bg-success-text-white">
                                            Room Nights:
                                        </td>
                                        <td class="align-middle">{{ $sumQtdDayles }}</td>
                                        <td class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</td>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumValueRate / $sumQtdDayles, $hotelEvent->currency->symbol) }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            EMITIR NOTA FISCAL?
                                        </td>
                                        <td class="align-middle">
                                            {{ $hotelEvent->invoice ? 'Sim' : 'Não' }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            Total Venda
                                        </td>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumValueRate) }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            Total Custo
                                        </td>
                                        <td class="align-middle">
                                            {{ $sumTotalHotelValue }}
                                        </td>
                                        <td class="align-middle custom-bg-success-text-white">
                                            Total Custo
                                        </td>
                                        <td class="align-middle">
                                            {{ $sumTotalHotelValue }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="10">{{ $transportEvent->internal_observation }}</td>
                        </tr>


                        <tr>
                            <td colspan="12" style="padding: 0;">
                                <table>
                                    <tr>
                                        <th colspan="3">IMPOSTOS CLIENTE</th>
                                        <th colspan="3">IMPOSTOS A PAGAR</th>
                                        <th colspan="2">COMISSÃO</th>
                                        <th>TOTAL DE VENDA</th>
                                        <th>TOTAL A PAGAR</th>
                                        <th>LUCRO TOTAL</th>
                                    </tr>
                                    <tr>
                                        <th>ISS %</th>
                                        <th>TAXA DE SERVIÇO</th>
                                        <th>IVA</th>

                                        <th>ISS %</th>
                                        <th>TAXA DE SERVIÇO</th>
                                        <th>IVA</th>

                                        <th>TAXA 4BTS</th>
                                        <th>10%</th>

                                        <th>TOTAL COM TAXAS & IMPOSTOS</th>
                                        <th>TOTAL COM TAXAS & IMPOSTOS</th>
                                        <th>COMISSÃO & TAXAS INCLUSAS</th>
                                    </tr>
                                    <tr>
                                        <td>4.372,95</td>
                                        <td>4.372,95</td>
                                        <td>4.372,95</td>

                                        <td>3.493,80</td>
                                        <td>3.493,80</td>
                                        <td>3.493,80</td>

                                        <td colspan="2">10.057,79</td>

                                        <td>118.380,13</td>
                                        <td>85.982,42</td>
                                        <td>39.385,31</td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </tfoot>

                </table>
                @endif


                <table style="page-break-before: always;">
                    <thead>
                        <tr>
                            <th colspan="7" style="padding: 0.5rem; text-align:center;">
                                Resumo da Proposta
                            </th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th>Resumo</th>
                            <th>Tarifas</th>
                            <th>Taxas</th>
                            <th>Impostos</th>
                            <th>Custos</th>
                            <th>Taxas</th>
                            <th>Impostos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Total de Hospedagem</td>
                            <td>87.459,00</td>
                            <td>14.430,74</td>
                            <td>15.786,35</td>
                            <td>69.876,00</td>
                            <td>3.493,80</td>
                            <td>12.612,62</td>
                        </tr>
                        <?php $strip = !$strip; ?>

                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Total de Salões/ Eventos</td>
                            <td>87.459,00</td>
                            <td>14.430,74</td>
                            <td>15.786,35</td>
                            <td>69.876,00</td>
                            <td>3.493,80</td>
                            <td>12.612,62</td>
                        </tr>
                        <?php $strip = !$strip; ?>

                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Total de Alimentos & Bebidas</td>
                            <td>87.459,00</td>
                            <td>14.430,74</td>
                            <td>15.786,35</td>
                            <td>69.876,00</td>
                            <td>3.493,80</td>
                            <td>12.612,62</td>
                        </tr>
                        <?php $strip = !$strip; ?>

                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Adicionais</td>
                            <td>87.459,00</td>
                            <td>14.430,74</td>
                            <td>15.786,35</td>
                            <td>69.876,00</td>
                            <td>3.493,80</td>
                            <td>12.612,62</td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        <tr>
                            <td>Transporte</td>
                            <td>14.430,74</td>
                            <td>15.786,35</td>
                            <td>69.876,00</td>
                            <td>3.493,80</td>
                            <td>12.612,62</td>
                            <td>12.612,62</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-footer">

                        <tr style="background-color: #ebf8ff;">
                            <td colspan="2" style="background-color: #ffffff"></td>
                            <td colspan="2" style="background-color: #ebf8ff;">TOTAL CLIENTE</td>
                            <td colspan="3" style="background-color: #ffe3b9; padding: 0.5rem;">
                                {{ formatCurrency($sumTotalHotelValue +
                                        $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue + ((($sumTotalHotelValue +
                                        $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue) * $percIOF) / 100) +
                                        ((($sumTotalHotelValue +
                                        $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue) * $percService) / 100)) }}
                            </td>
                        </tr>
                        <tr style="background-color: #ebf8ff;">
                            <td colspan="2" style="background-color: #ffffff"></td>
                            <td colspan="2" style="background-color: #ebf8ff;">TOTAL A PAGAR</td>
                            <td colspan="3" style="background-color: #ffe3b9; padding: 0.5rem;">
                                {{ formatCurrency($sumTotalHotelValue +
                                        $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue + ((($sumTotalHotelValue +
                                        $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue) * $percIOF) / 100) +
                                        ((($sumTotalHotelValue +
                                        $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue) * $percService) / 100)) }}
                            </td>
                        </tr>
                        <tr style="background-color: #ebf8ff;">
                            <td colspan="2" style="background-color: #ffffff"></td>
                            <td colspan="2" style="background-color: #ebf8ff;">LUCRO TOTAL</td>
                            <td colspan="3" style="background-color: #ffe3b9; padding: 0.5rem;">
                                {{ formatCurrency($sumTotalHotelValue +
                                        $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue + ((($sumTotalHotelValue +
                                        $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue) * $percIOF) / 100) +
                                        ((($sumTotalHotelValue +
                                        $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue) * $percService) / 100)) }}
                            </td>
                        </tr>

                    </tfoot>
                </table>
            </div>



            <hr style="border-top: 1px solid black;">

            <div style="height: 250px;"></div>


            <footer id="footer" style="width:100%; border-collapse: collapse;">

                <div class="header" style="background-color: #000; color: #fff; margin-bottom: 0; margin-top: 15px;">
                    <div class="left">
                        <div style="display: inline-block; padding: 10px; text-align: left;">
                            <p>Tel.: (+55 21) 2025-7900</p>
                            <p>Avenida das Americas, 3434 - Bloco 5 - Grupo 520</p>
                            <p>Barra da Tijuca - Rio de Janeiro - 22.640-102</p>
                        </div>
                    </div>
                    <div class="right" style="transform: initial;">
                        <img style="width: 150px;" src="{{ asset('/storage/logos/logo.png') }}" alt="4BTS">
                        <p>www.4BTS.com.br</p>
                    </div>
                </div>
            </footer>

        </div>

    </body>

</html>