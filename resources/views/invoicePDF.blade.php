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
    // Convert both dates to DateTime objects
    $date1 = new DateTime($date1);
    $date2 = new DateTime($date2);

    // Set both dates to the start of the day (00:00:00)
    $date1->setTime(0, 0, 0);
    $date2->setTime(0, 0, 0);

    // Calculate the difference in days
    $interval = $date1->diff($date2);

    // Return the absolute value of the difference in days
    return ceil($interval->days);
}
function daysBetween1($date1, $date2)
{
    // Convert both dates to DateTime objects
    $date1 = new DateTime($date1);
    $date2 = new DateTime($date2);

    // Set both dates to the start of the day (00:00:00)
    $date1->setTime(0, 0, 0);
    $date2->setTime(0, 0, 0);

    // Calculate the difference in days
    $interval = $date1->diff($date2);

    // Return the absolute value of the difference in days
    return ceil($interval->days) + 1;
}

$symbolGeral = 'BRL';
function formatCurrency($value, $symbol = '')
{
    global $symbolGeral;
    if ($symbol != '') {
        $symbolGeral = $symbol;
    }
    $value = round($value * 100) / 100;
    return $symbolGeral . ' ' . number_format($value, 2, ',', '.');
}

function unitSale($opt)
{
    if ($opt['received_proposal_percent'] == 0) {
        return $opt['received_proposal'];
    }

    return ceil($opt['received_proposal'] / $opt['received_proposal_percent']);
}


function sumTaxesProvider($eventP, $opt)
{
    return ((unitSale($opt) * $eventP['iss_percent']) / 100) + ((unitSale($opt) * $eventP['service_percent']) / 100) + ((unitSale($opt) * $eventP['iva_percent']) / 100) + ((unitSale($opt) * $eventP['service_charge']) / 100);
}

function sumTaxesProviderCost($eventP, $opt)
{
    return (($opt['received_proposal'] * $eventP['iss_percent']) / 100) + (($opt['received_proposal'] * $eventP['service_percent']) / 100) + (($opt['received_proposal'] * $eventP['iva_percent']) / 100) + (($opt['received_proposal'] * $eventP['service_charge']) / 100);
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


$sumTotalHotelSale = 0;
$sumTotalHotelSaleTaxa = 0;
$sumTaxeHotelCost = 0;
$sumTaxeHotelSale = 0;
$sumTotalHotelCost = 0;
$sumTotalHotelCostTaxa = 0;
$sumHotelCost = 0;
$sumHotelSale = 0;
$sumQtdDayles = 0;
$totalKickback = 0;

$sumTotalHotelCostTaxa = 0;
$sumTotalAbCostTaxa = 0;
$sumTotalHallCostTaxa = 0;
$sumTotalAddCostTaxa  = 0;
$sumTotalTransportCostTaxa = 0;


$sumTotalAbSale = 0;
$sumTotalAbSaleTaxa = 0;
$sumTaxeAbCost = 0;
$sumTaxeAbCostTaxa = 0;
$sumTaxeAbSale = 0;
$sumTotalAbCost = 0;
$sumAbCost = 0;
$sumAbSale = 0;
$sumQtdDaylesAb = 0;
$totalKickbackAb = 0;


$sumTotalHallSale = 0;
$sumTotalHallSaleTaxa = 0;
$sumTaxeHallCost = 0;
$sumTaxeHallCostTaxa = 0;
$sumTaxeHallSale = 0;
$sumTotalHallCost = 0;
$sumHallCost = 0;
$sumHallSale = 0;
$sumQtdDaylesHall = 0;
$totalKickbackHall = 0;


$sumTotalAddSale = 0;
$sumTotalAddSaleTaxa = 0;
$sumTaxeAddCost = 0;
$sumTaxeAddCostTaxa = 0;
$sumTaxeAddSale = 0;
$sumTotalAddCost = 0;
$sumAddCost = 0;
$sumAddSale = 0;
$sumQtdDaylesAdd = 0;
$totalKickbackAdd = 0;


$sumTotalTransportSale = 0;
$sumTotalTransportSaleTaxa = 0;
$sumTaxeTransportCost = 0;
$sumTaxeTransportCostTaxa = 0;
$sumTaxeTransportSale = 0;
$sumTotalTransportCost = 0;
$sumTransportCost = 0;
$sumTransportSale = 0;
$sumQtdDaylesTransport = 0;
$totalKickbackTransport = 0;



$strip = false;

$hotelEvent = null;
$abEvent = null;
$hallEvent = null;
$addEvent = null;
$transportEvent = null;

if ($provider != null) {

    if ($table == 'event_hotels' || $table == 'event_abs' || $table == 'event_halls') {
        $hotelEvent = $event->event_hotels->firstWhere('hotel_id', $provider->id);
    }

    if ($table == 'event_hotels' || $table == 'event_abs' || $table == 'event_halls') {
        $abEvent = $event->event_abs->firstWhere('ab_id', $provider->id);
    }

    if ($table == 'event_hotels' || $table == 'event_abs' || $table == 'event_halls') {
        $hallEvent = $event->event_halls->firstWhere('hall_id', $provider->id);
    }

    if ($table == 'event_adds') {
        $addEvent = $event->event_adds->firstWhere('add_id', $provider->id);
    }

    if ($table == 'event_transports') {
        $transportEvent = $event->event_transports->firstWhere('transport_id', $provider->id);
    }
}

$strip = false;

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
            page-break-inside: auto;
        }

        th,
        td {
            padding: 0.3rem;
            height: 30px;
            padding: 0.3rem;
            text-align: center;
        }

        th {
            text-align: center;
            padding: 0.3rem;
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
            margin-bottom: 10px;
            height: 150px;
        }

        .left {
            float: left;
            width: calc(100% - 180px);
        }

        .arrow {
            display: inline-block;
            margin: 15px 0 0 -19px;
            padding: 9px 40px;
            background-color: #e9540d;
            position: relative;
        }

        .arrow:before {
            content: "";
            position: absolute;
            top: -3px;
            left: 100%;
            border: 20px solid transparent;
            border-left: 45px solid #e9540d;
        }

        .title {
            font-weight: 900;
            font-style: normal;
            color: rgb(250, 249, 249);
            text-decoration: none;
            font-size: 9pt;
            margin: 0;
        }

        .event-info {
            margin-left: 20px;
        }

        .line {
            margin-bottom: 10px;
            white-space: nowrap;
            font-size: 7pt;
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
                            <div class="title">TICKET N° {{ $event != null ? $event->code : '' }}</div>
                        </div>
                    </div>

                    <div class="event-info">
                        <div class="line">
                            <p>De: <span class="event-data">{{date("d/m/Y", strtotime($event->date)) }}</span></p>
                            <p>Até: <span class="event-data">{{date("d/m/Y", strtotime($event->date_final)) }}</span></p>

                            <p>Solicitante: <span class="event-data">{{$event->requester }}</span></p>
                            <p>Base de pax: <span class="event-data">{{$event->pax_base }}</span></p>

                            <p>Setor: <span class="event-data">{{$event->sector }}</span></p>
                        </div>
                        <div class="line">
                            <p>Evento: <span class="event-data">{{ $event->name }}</p>

                            <p>CRD: <span class="event-data">{{$event->crd != null ? $event->crd->number . ": " . $event->crd->name: "" }}</span></p>
                            <p>CC: <span class="event-data">{{$event->cost_center }}</span></p>

                            <p>Operador :
                                <span class="event-data">
                                    @if ($transportEvent != null)
                                    {{ $event->landOperator->name ?? 'Sem operador' }}
                                    @elseif ($hotelEvent != null || $abEvent != null || $hallEvent != null || $addEvent != null)
                                    {{ $event->hotelOperator->name ?? 'Sem operador' }}
                                    @else
                                    {{ 'Sem operador' }}
                                    @endif
                                </span>
                            </p>
                        </div>

                        <div class="line">
                            <p>Fornecedor: <span class="event-data">{{$provider != null ?  $provider->name : ''}}</p>

                            @if($event->exchange_rate != null && $event->exchange_rate != 0 && $event->exchange_rate != 1)
                            <p>Câmbio <span class="event-data">{{$event->exchange_rate}}</span></p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="right">
                    <img src="{{ asset($event->customer->logo) }}" style="max-width: 100px; max-height: 100px;" alt="{{ $event->customer->name}}">
                </div>
            </header>

            <div>

                @if($hotelEvent != null && $hotelEvent->eventHotelsOpt != null && count($hotelEvent->eventHotelsOpt) > 0)

                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="11" style="padding: 0.3rem; text-align: center;">Hospedagem</th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th>Cat. Apto</th>
                            <th>Regime</th>
                            <th>Tipo Apto</th>
                            <th>Checkin</th>
                            <th>Checkout</th>
                            <th>Qtd</th>
                            <th>Diárias</th>
                            <th>Valor</th>
                            <th>Taxas</th>
                            <th>TTL com Taxa</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hotelEvent->eventHotelsOpt as $key => $item)
                        <?php
                        $taxes = sumTaxesProvider($hotelEvent, $item);
                        $qtdDayle = $item->count * daysBetween($item->in, $item->out);

                        $taxesCost = sumTaxesProviderCost($hotelEvent, $item);

                        $sumQtdDayles += $qtdDayle;

                        $totalKickback += ($item->received_proposal * $qtdDayle * $item->kickback) / 100;

                        $sumHotelCost += $item->received_proposal * $qtdDayle;
                        $sumHotelSale += unitSale($item) * $qtdDayle;
                        $sumTotalHotelCost += sumTotal($item->received_proposal, $taxesCost, $qtdDayle);
                        $sumTotalHotelSale += sumTotal(unitSale($item), $taxes, $qtdDayle);

                        $sumTaxeHotelCost += ($taxesCost * $qtdDayle);
                        $sumTaxeHotelSale += ($taxes * $qtdDayle);
                        ?>
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->category_hotel->name }}</td>
                            <td>{{ $item->regime->name }}</td>
                            <td>{{ $item->apto_hotel->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency(unitSale($item) , $hotelEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(sumTaxesProvider($hotelEvent, $item), $hotelEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(unitSale($item) + sumTaxesProvider($hotelEvent, $item), $hotelEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($hotelEvent, $item), $item->count * daysBetween($item->in, $item->out)), $hotelEvent->currency->symbol) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <?php
                    $sumTotalHotelSaleTaxa = ((($sumTotalHotelSale * $hotelEvent->iof) / 100) + $sumTotalHotelSale) *  (1 + ($hotelEvent->taxa_4bts / 100));
                    $sumTotalHotelCostTaxa = ((($sumTotalHotelCost * $hotelEvent->iof) / 100) + $sumTotalHotelCost);

                    $hotelTaxa4BTS = ((($sumTotalHotelSale * $hotelEvent->iof) / 100) + $sumTotalHotelSale) * ($hotelEvent->taxa_4bts / 100);
                    $sumTaxeHotelCost += (($sumTotalHotelCost * $hotelEvent->iof) / 100);
                    $sumTaxeHotelSale += (($sumTotalHotelSale * $hotelEvent->iof) / 100);
                    ?>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="11" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr class="table-subheader" style="background-color: #ffe0b1">
                                        <th class="align-middle custom-bg-success-text-white">Room Nights:</th>
                                        <td class="align-middle">{{ $sumQtdDayles }}</td>
                                        <th class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</th>
                                        <td class="align-middle">{{ formatCurrency($sumTotalHotelSale / $sumQtdDayles, $hotelEvent->currency->symbol) }}</td>
                                        <th class="align-middle custom-bg-success-text-white">EMITIR NOTA FISCAL?</th>
                                        <td class="align-middle">{{ $hotelEvent->invoice ? 'Sim' : 'Não' }}</td>
                                        <th class="align-middle custom-bg-success-text-white">Total Venda</th>
                                        <td class="align-middle">{{ formatCurrency($sumTotalHotelSale, $hotelEvent->currency->symbol) }}</td>
                                        <th class="align-middle custom-bg-success-text-white">Total Custo</th>
                                        <td class="align-middle">{{ formatCurrency($sumTotalHotelCost, $hotelEvent->currency->symbol) }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        @if($hotelEvent->internal_observation != null && $hotelEvent->internal_observation != "")
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="9">{{ $hotelEvent->internal_observation }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="11" style="padding: 0;">

                                <table style="border-collapse: collapse; width: 100%; text-align: center;">
                                    <tr>
                                        <th colspan="2" style="background-color: #c1d9ff; border: 1px solid #ffffff;">IMPOSTOS CLIENTE</th>
                                        <th colspan="2" style="background-color: #ffe5e5; border: 1px solid #ffffff;">IMPOSTOS A PAGAR</th>
                                        <th></th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $hotelEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumHotelSale * $hotelEvent->iss_percent) / 100, $hotelEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $hotelEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumHotelCost * $hotelEvent->iss_percent) / 100, $hotelEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="background-color: #c1d9ff; border: 1px solid #ffffff; color: #000">TOTAL COM TAXAS CLIENTE</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalHotelSaleTaxa, $hotelEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $hotelEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHotelSale * $hotelEvent->service_percent) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $hotelEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHotelCost * $hotelEvent->service_percent) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff; background-color: #ffe5e5; color: #000">TOTAL COM TAXAS A PAGAR</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalHotelCostTaxa, $hotelEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $hotelEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHotelSale * $hotelEvent->iva_percent) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $hotelEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHotelCost * $hotelEvent->iva_percent) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">LUCRO TOTAL</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalHotelSaleTaxa - $sumTotalHotelCostTaxa, $hotelEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA TURISMO ({{ $hotelEvent->service_charge }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHotelSale * $hotelEvent->service_charge) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA TURISMO ({{ $hotelEvent->service_charge }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHotelCost * $hotelEvent->service_charge) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">Comissão</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($totalKickback, $hotelEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IOF ({{ $hotelEvent->iof }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHotelSale * $hotelEvent->iof) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IOF ({{ $hotelEvent->iof }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalHotelCost * $hotelEvent->iof) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;" colspan="2"></td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">Serviço 4BTS ({{ number_format($hotelEvent->taxa_4bts, 2) }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($hotelTaxa4BTS, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;" colspan="4"></td>
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
                            <th colspan="10" style="padding: 0.3rem; text-align: center;">Alimentos & Bebidas</th>
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
                        <?php
                        $taxesAb = sumTaxesProvider($abEvent, $item);
                        $qtdDayleAb = $item->count * daysBetween1($item->in, $item->out);

                        $taxesCostAb = sumTaxesProviderCost($abEvent, $item);

                        $sumQtdDaylesAb += $qtdDayleAb;

                        $totalKickbackAb += ($item->received_proposal * $qtdDayleAb * $item->kickback) / 100;

                        $sumAbCost += $item->received_proposal * $qtdDayleAb;
                        $sumAbSale += unitSale($item) * $qtdDayleAb;
                        $sumTotalAbCost += sumTotal($item->received_proposal, $taxesCostAb, $qtdDayleAb);
                        $sumTotalAbSale += sumTotal(unitSale($item), $taxesAb, $qtdDayleAb);

                        $sumTaxeAbCost += ($taxesCostAb * $qtdDayleAb);
                        $sumTaxeAbSale += ($taxesAb * $qtdDayleAb);
                        ?>

                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->service_type->name }}</td>
                            <td>{{ $item->local->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween1($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency(unitSale($item), $abEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(sumTaxesProvider($abEvent, $item), $abEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(unitSale($item) + sumTaxesProvider($abEvent, $item), $abEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($abEvent, $item), $item->count * daysBetween1($item->in, $item->out)), $abEvent->currency->symbol) }}</td>

                        </tr>
                        @endforeach
                    </tbody>
                    <?php
                    $sumTotalAbSaleTaxa = ((($sumTotalAbSale * $abEvent->iof) / 100) + $sumTotalAbSale) *  (1 + ($abEvent->taxa_4bts / 100));
                    $sumTotalAbCostTaxa = ((($sumTotalAbCost * $abEvent->iof) / 100) + $sumTotalAbCost);

                    $abTaxa4BTS = ((($sumTotalAbSale * $abEvent->iof) / 100) + $sumTotalAbSale) * ($abEvent->taxa_4bts / 100);
                    $sumTaxeAbCost += (($sumTotalAbCost * $abEvent->iof) / 100);
                    $sumTaxeAbSale += (($sumTotalAbSale * $abEvent->iof) / 100);
                    ?>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="10" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr class="table-subheader" style="background-color: #ffe0b1">
                                        <th class="align-middle custom-bg-success-text-white">Room Nights:</th>
                                        <td class="align-middle">{{ $sumQtdDaylesAb }}</td>
                                        <th class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</th>
                                        <td class="align-middle">{{ formatCurrency($sumTotalAbSale / $sumQtdDaylesAb, $abEvent->currency->symbol) }}</td>
                                        <th class="align-middle custom-bg-success-text-white">EMITIR NOTA FISCAL?</th>
                                        <td class="align-middle">{{ $abEvent->invoice ? 'Sim' : 'Não' }}</td>
                                        <th class="align-middle custom-bg-success-text-white">Total Venda</th>
                                        <td class="align-middle">{{ formatCurrency($sumTotalAbSale, $abEvent->currency->symbol) }}</td>
                                        <th class="align-middle custom-bg-success-text-white">Total Custo</th>
                                        <td class="align-middle">{{ formatCurrency($sumTotalAbCost, $abEvent->currency->symbol) }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        @if($abEvent->internal_observation != null && $abEvent->internal_observation != "")
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="8">{{ $abEvent->internal_observation }}</td>
                        </tr>
                        @endif

                        <tr>
                            <td colspan="10" style="padding: 0;">

                                <table style="border-collapse: collapse; width: 100%; text-align: center;">
                                    <tr>
                                        <th colspan="2" style="background-color: #c1d9ff; border: 1px solid #ffffff;">IMPOSTOS CLIENTE</th>
                                        <th colspan="2" style="background-color: #ffe5e5; border: 1px solid #ffffff;">IMPOSTOS A PAGAR</th>
                                        <th></th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $abEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumAbSale * $abEvent->iss_percent) / 100, $abEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $abEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumAbCost * $abEvent->iss_percent) / 100, $abEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="background-color: #c1d9ff; border: 1px solid #ffffff; color: #000">TOTAL COM TAXAS CLIENTE</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalAbSaleTaxa, $abEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $abEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAbSale * $abEvent->service_percent) / 100, $abEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $abEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAbCost * $abEvent->service_percent) / 100, $abEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff; background-color: #ffe5e5; color: #000">TOTAL COM TAXAS A PAGAR</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalAbCostTaxa, $abEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $abEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAbSale * $abEvent->iva_percent) / 100, $abEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $abEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAbCost * $abEvent->iva_percent) / 100, $abEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">LUCRO TOTAL</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalAbSaleTaxa - $sumTotalAbCostTaxa, $abEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA TURISMO ({{ $abEvent->service_charge }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAbSale * $abEvent->service_charge) / 100, $abEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA TURISMO ({{ $abEvent->service_charge }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAbCost * $abEvent->service_charge) / 100, $abEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">Comissão</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($totalKickbackAb, $abEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IOF ({{ $abEvent->iof }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAbSale * $abEvent->iof) / 100, $abEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IOF ({{ $abEvent->iof }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalAbCost * $abEvent->iof) / 100, $abEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;" colspan="2"></td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">Serviço 4BTS ({{ number_format($abEvent->taxa_4bts, 2) }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($abTaxa4BTS, $abEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;" colspan="4"></td>
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
                            <th colspan="11" style="padding: 0.3rem; text-align: center;">Salões & Eventos {{$hallEvent->hall->name }}</th>
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
                        <?php
                        $taxesHall = sumTaxesProvider($hallEvent, $item);
                        $qtdDayleHall = $item->count * daysBetween1($item->in, $item->out);

                        $taxesCostHall = sumTaxesProviderCost($hallEvent, $item);

                        $sumQtdDaylesHall += $qtdDayleHall;

                        $totalKickbackHall += ($item->received_proposal * $qtdDayleHall * $item->kickback) / 100;

                        $sumHallCost += $item->received_proposal * $qtdDayleHall;
                        $sumHallSale += unitSale($item) * $qtdDayleHall;
                        $sumTotalHallCost += sumTotal($item->received_proposal, $taxesCostHall, $qtdDayleHall);
                        $sumTotalHallSale += sumTotal(unitSale($item), $taxesHall, $qtdDayleHall);


                        $sumTaxeHallCost += ($taxesCostHall * $qtdDayleHall);
                        $sumTaxeHallSale += ($taxesHall * $qtdDayleHall);
                        ?>
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->m2 }}</td>
                            <td>{{ $item->purpose->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween1($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency(unitSale($item), $hallEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(sumTaxesProvider($hallEvent, $item), $hallEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(unitSale($item) + sumTaxesProvider($hallEvent, $item), $hallEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($hallEvent, $item), $item->count * daysBetween1($item->in, $item->out)), $hallEvent->currency->symbol) }}</td>

                        </tr>
                        @endforeach
                    </tbody>
                    <?php
                    $sumTotalHallSaleTaxa = ((($sumTotalHallSale * $hallEvent->iof) / 100) + $sumTotalHallSale) *  (1 + ($hallEvent->taxa_4bts / 100));
                    $sumTotalHallCostTaxa = ((($sumTotalHallCost * $hallEvent->iof) / 100) + $sumTotalHallCost);

                    $hallTaxa4BTS = ((($sumTotalHallSale * $hallEvent->iof) / 100) + $sumTotalHallSale) * ($hallEvent->taxa_4bts / 100);
                    $sumTaxeHallCost += (($sumTotalHallCost * $hallEvent->iof) / 100);
                    $sumTaxeHallSale += (($sumTotalHallSale * $hallEvent->iof) / 100);
                    ?>
                    <tfoot class="table-footer">
                        <tr class="table-subheader" style="background-color: #ffe0b1">
                            <th class="align-middle custom-bg-success-text-white">Room Nights:</th>
                            <td class="align-middle">{{ $sumQtdDaylesHall }}</td>
                            <th class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</th>
                            <td class="align-middle">{{ formatCurrency($sumTotalHallSale / $sumQtdDaylesHall, $hallEvent->currency->symbol) }}</td>
                            <th class="align-middle custom-bg-success-text-white" colspan="2">EMITIR NOTA FISCAL?</th>
                            <td class="align-middle">{{ $hallEvent->invoice ? 'Sim' : 'Não' }}</td>
                            <th class="align-middle custom-bg-success-text-white">Total Venda</th>
                            <td class="align-middle">{{ formatCurrency($sumTotalHallSale, $hallEvent->currency->symbol) }}</td>
                            <th class="align-middle custom-bg-success-text-white">Total Custo</th>
                            <td class="align-middle">{{ formatCurrency($sumTotalHallCost, $hallEvent->currency->symbol) }}</td>
                        </tr>

                        @if($hallEvent->internal_observation != null && $hallEvent->internal_observation != "")
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="9">{{ $hallEvent->internal_observation }}</td>
                        </tr>
                        @endif

                        <tr>
                            <td colspan="11" style="padding: 0;">

                                <table style="border-collapse: collapse; width: 100%; text-align: center;">
                                    <tr>
                                        <th colspan="2" style="background-color: #c1d9ff; border: 1px solid #ffffff;">IMPOSTOS CLIENTE</th>
                                        <th colspan="2" style="background-color: #ffe5e5; border: 1px solid #ffffff;">IMPOSTOS A PAGAR</th>
                                        <th></th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $hallEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumHallSale * $hallEvent->iss_percent) / 100, $hallEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $hallEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumHallCost * $hallEvent->iss_percent) / 100, $hallEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="background-color: #c1d9ff; border: 1px solid #ffffff; color: #000">TOTAL COM TAXAS CLIENTE</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalHallSaleTaxa, $hallEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $hallEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHallSale * $hallEvent->service_percent) / 100, $hallEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $hallEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHallCost * $hallEvent->service_percent) / 100, $hallEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff; background-color: #ffe5e5; color: #000">TOTAL COM TAXAS A PAGAR</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalHallCostTaxa, $hallEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $hallEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHallSale * $hallEvent->iva_percent) / 100, $hallEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $hallEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHallCost * $hallEvent->iva_percent) / 100, $hallEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">LUCRO TOTAL</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalHallSaleTaxa - $sumTotalHallCostTaxa, $hallEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA TURISMO ({{ $hallEvent->service_charge }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHallSale * $hallEvent->service_charge) / 100, $hallEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA TURISMO ({{ $hallEvent->service_charge }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHallCost * $hallEvent->service_charge) / 100, $hallEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">Comissão</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($totalKickbackHall, $hallEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IOF ({{ $hallEvent->iof }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHallSale * $hallEvent->iof) / 100, $hallEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IOF ({{ $hallEvent->iof }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalHallCost * $hallEvent->iof) / 100, $hallEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;" colspan="2"></td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">Serviço 4BTS ({{ number_format($hallEvent->taxa_4bts, 2) }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($hallTaxa4BTS, $hallEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;" colspan="4"></td>
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
                            <th colspan="11" style="padding: 0.3rem; text-align: center;">Adicionais {{$addEvent->add->name }}</th>
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
                        <?php
                        $taxesAdd = sumTaxesProvider($addEvent, $item);
                        $qtdDayleAdd = $item->count * daysBetween1($item->in, $item->out);

                        $taxesCostAdd = sumTaxesProviderCost($addEvent, $item);

                        $sumQtdDaylesAdd += $qtdDayleAdd;

                        $totalKickbackAdd += ($item->received_proposal * $qtdDayleAdd * $item->kickback) / 100;

                        $sumAddCost += $item->received_proposal * $qtdDayleAdd;
                        $sumAddSale += unitSale($item) * $qtdDayleAdd;
                        $sumTotalAddCost += sumTotal($item->received_proposal, $taxesCostAdd, $qtdDayleAdd);
                        $sumTotalAddSale += sumTotal(unitSale($item), $taxesAdd, $qtdDayleAdd);

                        $sumTaxeAddCost += ($taxesCostAdd * $qtdDayleAdd);
                        $sumTaxeAddSale += ($taxesAdd * $qtdDayleAdd);
                        ?>

                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->service->name }}</td>
                            <td>{{ $item->frequency->name }}</td>
                            <td>{{ $item->measure->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween1($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency(unitSale($item), $addEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(sumTaxesProvider($addEvent, $item), $addEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(unitSale($item) + sumTaxesProvider($addEvent, $item), $addEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($addEvent, $item), $item->count * daysBetween1($item->in, $item->out)), $addEvent->currency->symbol) }}</td>

                        </tr>
                        @endforeach
                    </tbody>

                    <?php
                    $sumTotalAddSaleTaxa = ((($sumTotalAddSale * $addEvent->iof) / 100) + $sumTotalAddSale) *  (1 + ($addEvent->taxa_4bts / 100));
                    $sumTotalAddCostTaxa = ((($sumTotalAddCost * $addEvent->iof) / 100) + $sumTotalAddCost);

                    $addTaxa4BTS = ((($sumTotalAddSale * $addEvent->iof) / 100) + $sumTotalAddSale) * ($addEvent->taxa_4bts / 100);
                    $sumTaxeAddCost += (($sumTotalAddCost * $addEvent->iof) / 100);
                    $sumTaxeAddSale += (($sumTotalAddSale * $addEvent->iof) / 100);
                    ?>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="11" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr class="table-subheader" style="background-color: #ffe0b1">
                                        <th class="align-middle custom-bg-success-text-white">Room Nights:</th>
                                        <td class="align-middle">{{ $sumQtdDaylesAdd }}</td>
                                        <th class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</th>
                                        <td class="align-middle">{{ formatCurrency($sumTotalAddSale / $sumQtdDaylesAdd, $addEvent->currency->symbol) }}</td>
                                        <th class="align-middle custom-bg-success-text-white" colspan="2">EMITIR NOTA FISCAL?</th>
                                        <td class="align-middle">{{ $addEvent->invoice ? 'Sim' : 'Não' }}</td>
                                        <th class="align-middle custom-bg-success-text-white">Total Venda</th>
                                        <td class="align-middle">{{ formatCurrency($sumTotalAddSale, $addEvent->currency->symbol) }}</td>
                                        <th class="align-middle custom-bg-success-text-white">Total Custo</th>
                                        <td class="align-middle">{{ formatCurrency($sumTotalAddCost, $addEvent->currency->symbol) }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        @if($addEvent->internal_observation != null && $addEvent->internal_observation != "")
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="9">{{ $addEvent->internal_observation }}</td>
                        </tr>
                        @endif

                        <tr>
                            <td colspan="11" style="padding: 0;">

                                <table style="border-collapse: collapse; width: 100%; text-align: center;">
                                    <tr>
                                        <th colspan="2" style="background-color: #c1d9ff; border: 1px solid #ffffff;">IMPOSTOS CLIENTE</th>
                                        <th colspan="2" style="background-color: #ffe5e5; border: 1px solid #ffffff;">IMPOSTOS A PAGAR</th>
                                        <th></th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $addEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumAddSale * $addEvent->iss_percent) / 100, $addEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $addEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumAddCost * $addEvent->iss_percent) / 100, $addEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="background-color: #c1d9ff; border: 1px solid #ffffff; color: #000">TOTAL COM TAXAS CLIENTE</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalAddSaleTaxa, $addEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $addEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAddSale * $addEvent->service_percent) / 100, $addEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $addEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAddCost * $addEvent->service_percent) / 100, $addEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff; background-color: #ffe5e5; color: #000">TOTAL COM TAXAS A PAGAR</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalAddCostTaxa, $addEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $addEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAddSale * $addEvent->iva_percent) / 100, $addEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $addEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAddCost * $addEvent->iva_percent) / 100, $addEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">LUCRO TOTAL</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalAddSaleTaxa - $sumTotalAddCostTaxa, $addEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA TURISMO ({{ $addEvent->service_charge }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAddSale * $addEvent->service_charge) / 100, $addEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA TURISMO ({{ $addEvent->service_charge }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAddCost * $addEvent->service_charge) / 100, $addEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">Comissão</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($totalKickbackAdd, $addEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IOF ({{ $addEvent->iof }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAddSale * $addEvent->iof) / 100, $addEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IOF ({{ $addEvent->iof }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalAddCost * $addEvent->iof) / 100, $addEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;" colspan="2"></td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">Serviço 4BTS ({{ number_format($addEvent->taxa_4bts, 2) }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($addTaxa4BTS, $addEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;" colspan="4"></td>
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
                            <th colspan="12" style="padding: 0.3rem; text-align: center;">Transporte Terrestre {{$transportEvent->transport->name }}</th>
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
                        <?php

                        $taxesTransport = sumTaxesProvider($transportEvent, $item);
                        $qtdDayleTransport = $item->count * daysBetween1($item->in, $item->out);

                        $taxesCostTransport = sumTaxesProviderCost($transportEvent, $item);

                        $sumQtdDaylesTransport += $qtdDayleTransport;

                        $totalKickbackTransport += ($item->received_proposal * $qtdDayleTransport * $item->kickback) / 100;

                        $sumTransportCost += $item->received_proposal * $qtdDayleTransport;
                        $sumTransportSale += unitSale($item) * $qtdDayleTransport;
                        $sumTotalTransportCost += sumTotal($item->received_proposal, $taxesCostTransport, $qtdDayleTransport);
                        $sumTotalTransportSale += sumTotal(unitSale($item), $taxesTransport, $qtdDayleTransport);


                        $sumTaxeTransportCost += ($taxesCostTransport * $qtdDayleTransport);
                        $sumTaxeTransportSale += ($taxesTransport * $qtdDayleTransport);
                        ?>
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->brand->name }}</td>
                            <td>{{ $item->vehicle->name }}</td>
                            <td>{{ $item->model->name }}</td>
                            <td>{{ $item->service->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween1($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency(unitSale($item), $transportEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(sumTaxesProvider($transportEvent, $item), $transportEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(unitSale($item) + sumTaxesProvider($transportEvent, $item), $transportEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($transportEvent, $item), $item->count * daysBetween1($item->in, $item->out)), $transportEvent->currency->symbol) }}</td>

                        </tr>
                        @endforeach
                    </tbody>

                    <?php
                    $sumTotalTransportSaleTaxa = ((($sumTotalTransportSale * $transportEvent->iof) / 100) + $sumTotalTransportSale) *  (1 + ($transportEvent->taxa_4bts / 100));
                    $sumTotalTransportCostTaxa = ((($sumTotalTransportCost * $transportEvent->iof) / 100) + $sumTotalTransportCost);

                    $transportTaxa4BTS = ((($sumTotalTransportSale * $transportEvent->iof) / 100) + $sumTotalTransportSale) * ($transportEvent->taxa_4bts / 100);
                    $sumTaxeTransportCost += (($sumTotalTransportCost * $transportEvent->iof) / 100);
                    $sumTaxeTransportSale += (($sumTotalTransportSale * $transportEvent->iof) / 100);
                    ?>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="12" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr class="table-subheader" style="background-color: #ffe0b1">
                                        <th class="align-middle custom-bg-success-text-white">Room Nights:</th>
                                        <td class="align-middle">{{ $sumQtdDaylesTransport }}</td>
                                        <th class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</th>
                                        <td class="align-middle">{{ formatCurrency($sumTotalTransportSale / $sumQtdDaylesTransport, $transportEvent->currency->symbol) }}</td>
                                        <th class="align-middle custom-bg-success-text-white">EMITIR NOTA FISCAL?</th>
                                        <td class="align-middle">{{ $transportEvent->invoice ? 'Sim' : 'Não' }}</td>
                                        <th class="align-middle custom-bg-success-text-white">Total Venda</th>
                                        <td class="align-middle">{{ formatCurrency($sumTotalTransportSale, $transportEvent->currency->symbol) }}</td>
                                        <th class="align-middle custom-bg-success-text-white">Total Custo</th>
                                        <td class="align-middle">{{ formatCurrency($sumTotalTransportCost, $transportEvent->currency->symbol) }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        @if($transportEvent->internal_observation != null && $transportEvent->internal_observation != "")
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="10">{{ $transportEvent->internal_observation }}</td>
                        </tr>
                        @endif

                        <tr>
                            <td colspan="12" style="padding: 0;">

                                <table style="border-collapse: collapse; width: 100%; text-align: center;">
                                    <tr>
                                        <th colspan="2" style="background-color: #c1d9ff; border: 1px solid #ffffff;">IMPOSTOS CLIENTE</th>
                                        <th colspan="2" style="background-color: #ffe5e5; border: 1px solid #ffffff;">IMPOSTOS A PAGAR</th>
                                        <th></th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $transportEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumTransportSale * $transportEvent->iss_percent) / 100, $transportEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $transportEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumTransportCost * $transportEvent->iss_percent) / 100, $transportEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="background-color: #c1d9ff; border: 1px solid #ffffff; color: #000">TOTAL COM TAXAS CLIENTE</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalTransportSaleTaxa, $transportEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $transportEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTransportSale * $transportEvent->service_percent) / 100, $transportEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $transportEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTransportCost * $transportEvent->service_percent) / 100, $transportEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff; background-color: #ffe5e5; color: #000">TOTAL COM TAXAS A PAGAR</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalTransportCostTaxa, $transportEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $transportEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTransportSale * $transportEvent->iva_percent) / 100, $transportEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $transportEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTransportCost * $transportEvent->iva_percent) / 100, $transportEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">LUCRO TOTAL</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalTransportSaleTaxa - $sumTotalTransportCostTaxa, $transportEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA TURISMO ({{ $transportEvent->service_charge }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTransportSale * $transportEvent->service_charge) / 100, $transportEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA TURISMO ({{ $transportEvent->service_charge }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTransportCost * $transportEvent->service_charge) / 100, $transportEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">Comissão</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($totalKickbackTransport, $transportEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IOF ({{ $transportEvent->iof }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTransportSale * $transportEvent->iof) / 100, $transportEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IOF ({{ $transportEvent->iof }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalTransportCost * $transportEvent->iof) / 100, $transportEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;" colspan="2"></td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">Serviço 4BTS ({{ number_format($transportEvent->taxa_4bts, 2) }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($transportTaxa4BTS, $transportEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;" colspan="4"></td>
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
                            <th colspan="7" style="padding: 0.3rem; text-align:center;">
                                Resumo da Proposta
                            </th>
                        </tr>

                        <tr>
                            <th></th>
                            <th colspan="3" style="color: #000; background-color: #c1d9ff; border: 1px solid #ffffff;">Cliente</th>
                            <th colspan="2" style="color: #000; background-color: #ffe5e5; border: 1px solid #ffffff;">A pagar</th>
                        </tr>
                        <tr>
                            <th class="custom-bg-success-text-white">Resumo</th>
                            <th style="background-color: #c1d9ff; border: 1px solid #ffffff;">Tarifas</th>
                            <th style="background-color: #c1d9ff; border: 1px solid #ffffff;">Taxas 4BTS</th>
                            <th style="background-color: #c1d9ff; border: 1px solid #ffffff;">Impostos</th>
                            <th style="background-color: #ffe5e5; border: 1px solid #ffffff;">Tarifas</th>
                            <th style="background-color: #ffe5e5; border: 1px solid #ffffff;">Impostos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($hotelEvent != null && $hotelEvent->eventHotelsOpt != null && count($hotelEvent->eventHotelsOpt) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Hospedagem</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumHotelSale, $hotelEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency($hotelTaxa4BTS, $hotelEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTaxeHotelSale, $hotelEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumHotelCost, $hotelEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTaxeHotelCost, $hotelEvent->currency->symbol) }}</td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif
                        @if($abEvent != null && $abEvent->eventAbOpts != null && count($abEvent->eventAbOpts) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Alimentos & Bebidas</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumAbSale, $abEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency($abTaxa4BTS, $abEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTaxeAbSale, $abEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumAbCost, $abEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTaxeAbCost, $abEvent->currency->symbol) }}</td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif

                        @if($hallEvent != null && $hallEvent->eventHallOpts != null && count($hallEvent->eventHallOpts) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Salões/ Eventos</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumHallSale, $hallEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency($hallTaxa4BTS, $hallEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTaxeHallSale, $hallEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumHallCost, $hallEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTaxeHallCost, $hallEvent->currency->symbol) }}</td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif

                        @if($addEvent != null && $addEvent->eventAddOpts != null && count($addEvent->eventAddOpts) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Adicionais</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumAddSale, $addEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency($addTaxa4BTS, $addEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTaxeAddSale, $addEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumAddCost, $addEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTaxeAddCost, $addEvent->currency->symbol) }}</td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif
                        @if($transportEvent != null && $transportEvent->eventTransportOpts != null && count($transportEvent->eventTransportOpts) > 0)
                        <tr>
                            <td>Transporte</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTransportSale, $transportEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency($transportTaxa4BTS, $transportEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTaxeTransportSale, $transportEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTransportCost, $transportEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTaxeTransportCost, $transportEvent->currency->symbol) }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <table style="width: 50%; min-width: 9cm; max-width: 9cm; margin-top: 30px;">
                <tbody>
                    <?php
                    $totalCliente = $sumTotalHotelSaleTaxa + $sumTotalAbSaleTaxa + $sumTotalHallSaleTaxa + $sumTotalAddSaleTaxa + $sumTotalTransportSaleTaxa;
                    $totalAPagar = $sumTotalHotelCostTaxa + $sumTotalAbCostTaxa + $sumTotalHallCostTaxa + $sumTotalAddCostTaxa + $sumTotalTransportCostTaxa;
                    $lucroTotal = $totalCliente - $totalAPagar;

                    $formattedTotalCliente = formatCurrency($totalCliente);
                    $formattedTotalAPagar = formatCurrency($totalAPagar);
                    $formattedLucroTotal = formatCurrency($lucroTotal);
                    $formattedTotalClienteBRL = $event->exchange_rate != null && $event->exchange_rate != 0 && $event->exchange_rate != 1 ? formatCurrency($totalCliente * $event->exchange_rate, 'R$') : null;
                    $formattedTotalAPagarBRL = $event->exchange_rate != null && $event->exchange_rate != 0 && $event->exchange_rate != 1 ? formatCurrency($totalAPagar * $event->exchange_rate, 'R$') : null;
                    $formattedLucroTotalBRL = $event->exchange_rate != null && $event->exchange_rate != 0 && $event->exchange_rate != 1 ? formatCurrency($lucroTotal * $event->exchange_rate, 'R$') : null;
                    ?>

                    <tr style="background-color: #ebf8ff; color: #000;">
                        <td colspan="2" style="background-color: #ebf8ff;">TOTAL CLIENTE</td>
                        <td colspan="3" style="background-color: #ffe3b9; padding: 0.3rem;">
                            {{ $formattedTotalCliente }}
                        </td>
                        @if($formattedTotalClienteBRL)
                        <td style="background-color: #ffe3b9; padding: 0.3rem;">
                            {{ $formattedTotalClienteBRL }}
                        </td>
                        @endif
                    </tr>
                    <tr style="background-color: #ebf8ff; color: #000; ">
                        <td colspan="2" style="background-color: #ebf8ff;">TOTAL A PAGAR</td>
                        <td colspan="3" style="background-color: #ffe3b9; padding: 0.3rem;">
                            {{ $formattedTotalAPagar }}
                        </td>
                        @if($formattedTotalAPagarBRL)
                        <td style="background-color: #ffe3b9; padding: 0.3rem;">
                            {{ $formattedTotalAPagarBRL }}
                        </td>
                        @endif
                    </tr>
                    <tr style="background-color: #ebf8ff;">
                        <td colspan="2" style="background-color: #ebf8ff;">LUCRO TOTAL</td>
                        <td colspan="3" style="background-color: #ffe3b9; padding: 0.3rem;">
                            {{ $formattedLucroTotal }}
                        </td>
                        @if($formattedLucroTotalBRL)
                        <td style="background-color: #ffe3b9; padding: 0.3rem;">
                            {{ $formattedLucroTotalBRL }}
                        </td>
                        @endif
                    </tr>
                </tbody>
            </table>

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