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
    return ((unitSale($opt) * $eventP['iss_percent']) / 100) + ((unitSale($opt) * $eventP['service_percent']) / 100) + ((unitSale($opt) * $eventP['iva_percent']) / 100) + ((unitSale($opt) * $eventP['service_charge']) / 100);
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

$hotelEvent  = null;
$abEvent = null;
$hallEvent  = null;
$addEvent  = null;
$transportEvent = null;

if ($provider != null) {
    $hotelEvent = $event->event_hotels->firstWhere('hotel_id', $provider->id);
    $abEvent = $event->event_abs->firstWhere('ab_id', $provider->id);
    $hallEvent = $event->event_halls->firstWhere('hall_id', $provider->id);
    $addEvent = $event->event_adds->firstWhere('add_id', $provider->id);
    $transportEvent = $event->event_transports->firstWhere('transport_id', $provider->id);
}

$strip = false;

$qtdLinhas = 0;
$rodapeSize = 13;

if ($hotelEvent != null) {
    if ($hotelEvent->iof > 0) {
        $percIOF = $hotelEvent->iof;
    }
    if ($hotelEvent->service_charge > 0) {
        $percService = $hotelEvent->service_charge;
    }
    $rodapeSize++;
    $qtdLinhas += 4;
    foreach ($hotelEvent->eventHotelsOpt as $item) {
        $qtdLinhas++;
        $rate = floatval(unitSale($item));
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
    $rodapeSize++;
    $qtdLinhas += 4;
    foreach ($abEvent->eventAbOpts as $item) {
        $qtdLinhas++;
        $rate = floatval(unitSale($item));
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
    $rodapeSize++;
    $qtdLinhas += 4;
    foreach ($hallEvent->eventHallOpts as $item) {
        $qtdLinhas++;
        $rate = floatval(unitSale($item));
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
    $rodapeSize++;
    $qtdLinhas += 4;
    foreach ($addEvent->eventAddOpts as $item) {
        $qtdLinhas++;
        $rate = floatval(unitSale($item));
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
    $rodapeSize++;
    $qtdLinhas += 4;
    foreach ($transportEvent->eventTransportOpts as $item) {
        $qtdLinhas++;
        $rate = floatval(unitSale($item));
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
            font-size: 7pt;
            max-width: 19cm;
            min-width: 19cm;
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: auto;
        }

        th,
        td {
            padding: 0.3rem;
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
            /* transform: translateY(50%); */
            /* width: 150px; */
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
    </style>
</head>

<body>

    <body>

        <div id="app">

            <header class="header">
                <div class="left">
                    <div class="seta">
                        <div class="arrow">
                            <div class="title">Proposta n° {{ $provider != null ? $provider->id : '' }}</div>
                        </div>
                    </div>

                    <div class="event-info">
                        <div class="line">
                            <p>De: <span class="event-data">{{date("d/m/Y", strtotime($event->date)) }}</span></p>
                            <p>Até: <span class="event-data">{{date("d/m/Y", strtotime($event->date_final)) }}</span></p>
                        </div>
                        <div class="line">
                            <p>Evento: <span class="event-data">{{ $event->name }}</p>
                        </div>
                    </div>
                </div>
                <div class="right">
                    <img src="{{ asset($event->customer->logo) }}" style="max-width: 175px; max-height: 175px;" alt="{{ $event->customer->name}}">
                </div>
            </header>

            <div class="event-info row">
                <div class="line">
                    <p>Fornecedor: <span class="event-data">{{$provider != null ?  $provider->name : ''}}</p>
                </div>
            </div>

            <div>
                @if($hotelEvent != null && $hotelEvent->eventHotelsOpt != null && count($hotelEvent->eventHotelsOpt) > 0)
                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="11" style="padding: 0.5rem; text-align: center;">Hospedagem</th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th style="padding: 0.5rem;">Cat. Apto</th>
                            <th style="padding: 0.5rem;">Regime</th>
                            <th style="padding: 0.5rem;">Tipo Apto</th>
                            <th style="padding: 0.5rem;">IN</th>
                            <th style="padding: 0.5rem;">Out</th>
                            <th style="padding: 0.5rem;">Qtd</th>
                            <th style="padding: 0.5rem;">Diárias</th>
                            <th style="padding: 0.5rem;">Valor</th>
                            <th style="padding: 0.5rem;">Taxas</th>
                            <th style="padding: 0.5rem;">TTL com Taxa</th>
                            <th style="padding: 0.5rem;">Total Geral</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hotelEvent->eventHotelsOpt as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td style="padding: 0.5rem;">{{ $item->category_hotel->name }}</td>
                            <td style="padding: 0.5rem;">{{ $item->regime->name }}</td>
                            <td style="padding: 0.5rem;">{{ $item->apto_hotel->name }}</td>
                            <td style="padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td style="padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td style="padding: 0.5rem;">{{ $item->count }}</td>
                            <td style="padding: 0.5rem;">{{ daysBetween($item->in, $item->out) }}</td>

                            <td style="padding: 0.5rem;">{{ formatCurrency(unitSale($item), $hotelEvent->currency->symbol) }}</td>
                            <td style="padding: 0.5rem;">
                                {{ formatCurrency(sumTaxesProvider($hotelEvent, $item), $hotelEvent->currency->symbol) }}
                            </td>
                            <td style="padding: 0.5rem;">
                                {{ formatCurrency(unitSale($item) + sumTaxesProvider($hotelEvent, $item), $hotelEvent->currency->symbol) }}
                            </td>
                            <td style="padding: 0.5rem;">{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($hotelEvent, $item), $item->count * daysBetween($item->in, $item->out)), $hotelEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2" style="padding: 0.5rem;"><b>Comentários:</b></td>
                            <td colspan="7" style="padding: 0.5rem;">{{ $hotelEvent->customer_observation }}</td>
                            <td style="padding: 0.5rem;"><b>Prazo</b></td>
                            <td style="padding: 0.5rem;">{{ $hotelEvent->deadline_date === null ? "--" : date("d/m/Y", strtotime($hotelEvent->deadline_date)) }}</td>
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
                            <th style="padding: 0.5rem;">Refeição</th>
                            <th style="padding: 0.5rem;">Local</th>
                            <th style="padding: 0.5rem;">De</th>
                            <th style="padding: 0.5rem;">Até</th>
                            <th style="padding: 0.5rem;">Qtd</th>
                            <th style="padding: 0.5rem;">Diárias</th>
                            <th style="padding: 0.5rem;">Valor</th>
                            <th style="padding: 0.5rem;">Taxas</th>
                            <th style="padding: 0.5rem;">TTL com Taxa</th>
                            <th style="padding: 0.5rem;">Total Geral</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($abEvent->eventAbOpts as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td style="padding: 0.5rem;">{{ $item->service_type->name }}</td>
                            <td style="padding: 0.5rem;">{{ $item->local->name }}</td>
                            <td style="padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td style="padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td style="padding: 0.5rem;">{{ $item->count }}</td>
                            <td style="padding: 0.5rem;">{{ daysBetween($item->in, $item->out) }}</td>

                            <td style="padding: 0.5rem;">{{ formatCurrency(unitSale($item), $abEvent->currency->symbol) }}</td>
                            <td style="padding: 0.5rem;">
                                {{ formatCurrency(sumTaxesProvider($abEvent, $item), $abEvent->currency->symbol) }}
                            </td>
                            <td style="padding: 0.5rem;">
                                {{ formatCurrency(unitSale($item) + sumTaxesProvider($abEvent, $item), $abEvent->currency->symbol) }}
                            </td>
                            <td style="padding: 0.5rem;">{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($abEvent, $item), $item->count * daysBetween($item->in, $item->out)), $abEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2" style="padding: 0.5rem;"><b>Comentários:</b></td>
                            <td colspan="6" style="padding: 0.5rem;">{{ $abEvent->customer_observation }}</td>
                            <td style="padding: 0.5rem;"><b>Prazo</b></td>
                            <td style="padding: 0.5rem;">{{ $abEvent->deadline_date === null ? "--" : date("d/m/Y", strtotime($abEvent->deadline_date)) }}</td>
                        </tr>
                    </tfoot>

                </table>
                @endif


                @if($hallEvent != null && $hallEvent->eventHallOpts != null && count($hallEvent->eventHallOpts) > 0)
                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="11" style="padding: 0.5rem; text-align: center;">Salões & Eventos</th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th style="padding: 0.5rem;">Nome</th>
                            <th style="padding: 0.5rem;">Metragem</th>
                            <th style="padding: 0.5rem;">Finalidade</th>
                            <th style="padding: 0.5rem;">De</th>
                            <th style="padding: 0.5rem;">Até</th>
                            <th style="padding: 0.5rem;">Qtd</th>
                            <th style="padding: 0.5rem;">Diárias</th>
                            <th style="padding: 0.5rem;">Valor</th>
                            <th style="padding: 0.5rem;">Taxas</th>
                            <th style="padding: 0.5rem;">TTL com Taxa</th>
                            <th style="padding: 0.5rem;">Total Geral</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hallEvent->eventHallOpts as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td style="padding: 0.5rem;">{{ $item->name }}</td>
                            <td style="padding: 0.5rem;">{{ $item->m2 }}</td>
                            <td style="padding: 0.5rem;">{{ $item->purpose->name }}</td>
                            <td style="padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td style="padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td style="padding: 0.5rem;">{{ $item->count }}</td>
                            <td style="padding: 0.5rem;">{{ daysBetween($item->in, $item->out) }}</td>

                            <td style="padding: 0.5rem;">{{ formatCurrency(unitSale($item), $hallEvent->currency->symbol) }}</td>
                            <td style="padding: 0.5rem;">
                                {{ formatCurrency(sumTaxesProvider($hallEvent, $item), $hallEvent->currency->symbol) }}
                            </td>
                            <td style="padding: 0.5rem;">
                                {{ formatCurrency(unitSale($item) + sumTaxesProvider($hallEvent, $item), $hallEvent->currency->symbol) }}
                            </td>
                            <td style="padding: 0.5rem;">{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($hallEvent, $item), $item->count * daysBetween($item->in, $item->out)), $hallEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2" style="padding: 0.5rem;"><b>Comentários:</b></td>
                            <td colspan="7" style="padding: 0.5rem;">{{ $hallEvent->customer_observation }}</td>
                            <td style="padding: 0.5rem;"><b>Prazo</b></td>
                            <td style="padding: 0.5rem;">{{ $hallEvent->deadline_date === null ? "--" : date("d/m/Y", strtotime($hallEvent->deadline_date)) }}</td>
                        </tr>
                    </tfoot>

                </table>
                @endif


                @if($addEvent != null && $addEvent->eventAddOpts != null && count($addEvent->eventAddOpts) > 0)
                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="11" style="padding: 0.5rem; text-align: center;">Adicionais</th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th style="padding: 0.5rem;">Serviço</th>
                            <th style="padding: 0.5rem;">Frequência</th>
                            <th style="padding: 0.5rem;">Measure</th>
                            <th style="padding: 0.5rem;">De</th>
                            <th style="padding: 0.5rem;">Até</th>
                            <th style="padding: 0.5rem;">Qtd</th>
                            <th style="padding: 0.5rem;">Diárias</th>
                            <th style="padding: 0.5rem;">Valor</th>
                            <th style="padding: 0.5rem;">Taxas</th>
                            <th style="padding: 0.5rem;">TTL com Taxa</th>
                            <th style="padding: 0.5rem;">Total Geral</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($addEvent->eventAddOpts as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td style="padding: 0.5rem;">{{ $item->service->name }}</td>
                            <td style="padding: 0.5rem;">{{ $item->frequency->name }}</td>
                            <td style="padding: 0.5rem;">{{ $item->measure->name }}</td>
                            <td style="padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td style="padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td style="padding: 0.5rem;">{{ $item->count }}</td>
                            <td style="padding: 0.5rem;">{{ daysBetween($item->in, $item->out) }}</td>

                            <td style="padding: 0.5rem;">{{ formatCurrency(unitSale($item), $addEvent->currency->symbol) }}</td>
                            <td style="padding: 0.5rem;">
                                {{ formatCurrency(sumTaxesProvider($addEvent, $item), $addEvent->currency->symbol) }}
                            </td>
                            <td style="padding: 0.5rem;">
                                {{ formatCurrency(unitSale($item) + sumTaxesProvider($addEvent, $item), $addEvent->currency->symbol) }}
                            </td>
                            <td style="padding: 0.5rem;">{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($addEvent, $item), $item->count * daysBetween($item->in, $item->out)), $addEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2" style="padding: 0.5rem;"><b>Comentários:</b></td>
                            <td colspan="7" style="padding: 0.5rem;">{{ $addEvent->customer_observation }}</td>
                            <td style="padding: 0.5rem;"><b>Prazo</b></td>
                            <td style="padding: 0.5rem;">{{ $addEvent->deadline_date === null ? "--" : date("d/m/Y", strtotime($addEvent->deadline_date)) }}</td>
                        </tr>
                    </tfoot>

                </table>
                @endif


                @if($transportEvent != null && $transportEvent->eventTransportOpts != null && count($transportEvent->eventTransportOpts) > 0)
                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="12" style="padding: 0.5rem; text-align: center;">Transporte Terrestre</th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th style="padding: 0.5rem;">Marca</th>
                            <th style="padding: 0.5rem;">Veículo</th>
                            <th style="padding: 0.5rem;">Modelo</th>
                            <th style="padding: 0.5rem;">Serviço</th>
                            <th style="padding: 0.5rem;">De</th>
                            <th style="padding: 0.5rem;">Até</th>
                            <th style="padding: 0.5rem;">Qtd</th>
                            <th style="padding: 0.5rem;">Diárias</th>
                            <th style="padding: 0.5rem;">Valor</th>
                            <th style="padding: 0.5rem;">Taxas</th>
                            <th style="padding: 0.5rem;">TTL com Taxa</th>
                            <th style="padding: 0.5rem;">Total Geral</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transportEvent->eventTransportOpts as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td style="padding: 0.5rem;">{{ $item->brand->name }}</td>
                            <td style="padding: 0.5rem;">{{ $item->vehicle->name }}</td>
                            <td style="padding: 0.5rem;">{{ $item->model->name }}</td>
                            <td style="padding: 0.5rem;">{{ $item->service->name }}</td>
                            <td style="padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td style="padding: 0.5rem;">{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td style="padding: 0.5rem;">{{ $item->count }}</td>
                            <td style="padding: 0.5rem;">{{ daysBetween($item->in, $item->out) }}</td>

                            <td style="padding: 0.5rem;">{{ formatCurrency(unitSale($item), $transportEvent->currency->symbol) }}</td>
                            <td style="padding: 0.5rem;">
                                {{ formatCurrency(sumTaxesProvider($transportEvent, $item), $transportEvent->currency->symbol) }}
                            </td>
                            <td style="padding: 0.5rem;">
                                {{ formatCurrency(unitSale($item) + sumTaxesProvider($transportEvent, $item), $transportEvent->currency->symbol) }}
                            </td>
                            <td style="padding: 0.5rem;">{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($transportEvent, $item), $item->count * daysBetween($item->in, $item->out)), $transportEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2" style="padding: 0.5rem;"><b>Comentários:</b></td>
                            <td colspan="6" style="padding: 0.5rem;">{{ $transportEvent->customer_observation }}</td>
                            <td style="padding: 0.5rem;"><b>Prazo</b></td>
                            <td style="padding: 0.5rem;">{{ $transportEvent->deadline_date === null ? "--" : date("d/m/Y", strtotime($transportEvent->deadline_date)) }}</td>
                        </tr>
                    </tfoot>

                </table>
                @endif

                <?php
                $quebrar = false;

                if ($qtdLinhas <= 19 && ($qtdLinhas + $rodapeSize) > 19) {
                    $quebrar = true;
                } elseif ($qtdLinhas > 19 && ($qtdLinhas % 27) + $rodapeSize > 27) {
                    $quebrar = true;
                }
                ?>
                <table style="<?= $quebrar ? "page-break-before: always;" : "" ?>">
                    <thead>
                        <tr>
                            <th colspan="5" style="padding: 0.5rem; text-align:center;">
                                Resumo da Proposta
                            </th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th style="padding: 0.5rem;">Serviços</th>
                            <th style="padding: 0.5rem;" colspan="2">Totais de</th>
                            <th style="padding: 0.5rem;">Preço médio</th>
                            <th style="padding: 0.5rem;">Total do Pedido</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($hotelEvent != null && $hotelEvent->eventHotelsOpt != null && count($hotelEvent->eventHotelsOpt) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td style="padding: 0.5rem;">Hospedagem</td>
                            <td style="padding: 0.5rem;">Rooms Night</td>
                            <td style="padding: 0.5rem;">{{ $sumQtdDayles}}</td>
                            <td style="padding: 0.5rem;">{{ formatCurrency($sumValueRate / count($hotelEvent->eventHotelsOpt))}}</td>
                            <td style="padding: 0.5rem;">{{ formatCurrency($sumTotalHotelValue)}}</td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif

                        @if($abEvent != null && $abEvent->eventAbOpts != null && count($abEvent->eventAbOpts) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td style="padding: 0.5rem;">Alimentos & Bebidas</td>
                            <td style="padding: 0.5rem;">Refeições</td>
                            <td style="padding: 0.5rem;">{{ $sumABQtdDayles }}</td>
                            <td style="padding: 0.5rem;">{{ formatCurrency($sumABValueRate / count($abEvent->eventAbOpts))}}</td>
                            <td style="padding: 0.5rem;">{{formatCurrency($sumTotalABValue) }} </td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif


                        @if($hallEvent != null && $hallEvent->eventHallOpts != null && count($hallEvent->eventHallOpts) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td style="padding: 0.5rem;">Salões e eventos</td>
                            <td style="padding: 0.5rem;">Serviços</td>
                            <td style="padding: 0.5rem;">{{ $sumHallQtdDayles }}</td>
                            <td style="padding: 0.5rem;">{{ formatCurrency($sumHallValueRate / count($hallEvent->eventHallOpts))}}</td>
                            <td style="padding: 0.5rem;">{{ formatCurrency($sumTotalHallValue) }} </td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif


                        @if($addEvent != null && $addEvent->eventAddOpts != null && count($addEvent->eventAddOpts) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td style="padding: 0.5rem;">Adicionais</td>
                            <td style="padding: 0.5rem;">Serviços</td>
                            <td style="padding: 0.5rem;">{{ $sumAddQtdDayles }}</td>
                            <td style="padding: 0.5rem;">{{ formatCurrency($sumAddValueRate / count($addEvent->eventAddOpts))}}</td>
                            <td style="padding: 0.5rem;">{{ formatCurrency($sumTotalAddValue) }} </td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif

                        @if($transportEvent != null && $transportEvent->eventTransportOpts != null && count($transportEvent->eventTransportOpts) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td style="padding: 0.5rem;">Transportes</td>
                            <td style="padding: 0.5rem;">Veículos</td>
                            <td style="padding: 0.5rem;">{{ $sumTransportQtdDayles}}</td>
                            <td style="padding: 0.5rem;">{{ formatCurrency($sumValueRate / count($transportEvent->eventTransportOpts))}}</td>
                            <td style="padding: 0.5rem;">{{ formatCurrency($sumTotalTransportValue)}}</td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif
                    </tbody>
                    <tfoot class="table-footer">
                        <tr style="background-color: #ebf8ff;">
                            <td colspan="5">

                                <table style="width: 100%;">
                                    <tr>
                                        <td style="height: 18px; width: 15%; float: left; background-color: #ffd18c; padding: 0.5rem;">IOF: </td>
                                        <td style="height: 18px; width: 15%; float: left; background-color: #ffe3b9; padding: 0.5rem;">{{ $percIOF }}</td>
                                        <td style="height: 18px; width: 15%; float: left; background-color: #ffd18c; padding: 0.5rem;">Taxa de Serviço: </td>
                                        <td style="height: 18px; width: 15%; float: left; background-color: #ffe3b9; padding: 0.5rem;">{{ $percService }}</td>
                                        <td style="height: 18px; width: 15%; float: left; background-color: #ffd18c; padding: 0.5rem;">Valor Total: </td>
                                        <td style="height: 18px; width: 15%; float: left; background-color: #ffe3b9; padding: 0.5rem;">
                                            {{ formatCurrency($sumTotalHotelValue +
                                        $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue + ((($sumTotalHotelValue +
                                        $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue) * $percIOF) / 100) +
                                        ((($sumTotalHotelValue +
                                        $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue) * $percService) / 100)) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <hr style="border-top: 1px solid black;">



            <footer id="footer" style="width:100%; border-collapse: collapse;">

                <div class="event-info row" style="text-align: right; margin-left: -20px;">
                    <div class="line">
                        <p>PRAZO: <span class="event-data">{{date("d/m/Y", strtotime($event->date)) }}</p>
                    </div>
                </div>
                <div style="margin-top: 70px; display: table; width: 100%;">
                    <div style="display: table-cell; text-align: center;">
                        <div style="width: 7cm; margin: 0 auto;">
                            <hr style="border-top: 1px solid black;">
                        </div>
                        <p>Autorizado por</p>
                    </div>
                    <div style="display: table-cell; text-align: center;">
                        <div style="width: 7cm; margin: 0 auto;">
                            <hr style="border-top: 1px solid black;">
                        </div>
                        <p>Data da autorização</p>
                    </div>
                </div>

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