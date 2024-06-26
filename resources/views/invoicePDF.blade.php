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
function daysBetween1($date1, $date2)
{
    // Convert both dates to UNIX timestamp
    $one = strtotime($date1);
    $two = strtotime($date2);
    // Calculate the difference in seconds
    $difference = abs($one - $two);
    // Convert back to days and return
    return ceil($difference / (60 * 60 * 24)) + 1;
}

function formatCurrency($value, $symbol = 'BRL')
{
    $value = round($value * 100) / 100;
    return $symbol . ' ' . number_format($value, 2, ',', '.');
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
$sumQtdDayles = 0;
$totalKickback = 0;


$sumTotalABValue = 0;
$sumABQtdDayles = 0;
$sumABValueRate = 0;
$totalKickbackAB = 0;

$sumTotalHallValue = 0;
$sumHallQtdDayles = 0;
$sumHallValueRate = 0;
$totalKickbackHall = 0;

$sumTotalAddValue = 0;
$sumAddQtdDayles = 0;
$sumAddValueRate = 0;
$totalKickbackAdd = 0;

$sumTransportValueRate = 0;
$sumTransportQtdDayles = 0;
$sumTotalTransportValue = 0;
$totalKickbackTransport = 0;

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
            padding: 0.5rem;
            text-align: center;
        }

        th {
            text-align: center;
            padding: 0.5rem;
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
            padding: 12px 40px;
            background-color: #e9540d;
            position: relative;
        }

        .arrow:before {
            content: "";
            position: absolute;
            top: -0px;
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
                            <div class="title">Faturamento N° {{ $event != null ? $event->code : '' }}</div>
                        </div>
                    </div>

                    <div class="event-info">
                        <div class="line">
                            <p>De: <span class="event-data">{{date("d/m/Y", strtotime($event->date)) }}</span></p>
                            <p>Até: <span class="event-data">{{date("d/m/Y", strtotime($event->date_final)) }}</span></p>

                            <p>Solicitante: <span class="event-data">{{$event->requester }}</span></p>
                            <p>Base de pax: <span class="event-data">{{$event->pax_base }}</span></p>
                        </div>
                        <div class="line">
                            <p>Evento: <span class="event-data">{{ $event->name }}</p>

                            <p>CRD: <span class="event-data">{{$event->crd != null ? $event->crd->name : "" }}</span></p>
                            <p>CC: <span class="event-data">{{$event->cost_center }}</span></p>
                        </div>

                        <div class="line">
                            <p>Fornecedor: <span class="event-data">{{$provider != null ?  $provider->name : ''}}</p>
                        </div>
                    </div>
                </div>
                <div class="right">
                    <img src="{{ asset($event->customer->logo) }}" style="max-width: 100px; max-height: 100px;" alt="{{ $event->customer->name}}">
                </div>
            </header>

            <div>

                @if($hotelEvent != null && $hotelEvent->eventHotelsOpt != null && count($hotelEvent->eventHotelsOpt) > 0)

                <?php
                if ($hotelEvent->iof > 0) {
                    $percIOF = $hotelEvent->iof;
                }
                if ($hotelEvent->service_charge > 0) {
                    $percService = $hotelEvent->service_charge;
                }
                ?>
                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="11" style="padding: 0.5rem; text-align: center;">Hospedagem</th>
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
                        $sumTaxeHotelCost += $taxesCost * $qtdDayle;
                        $sumTaxeHotelSale += $taxes * $qtdDayle;

                        $sumQtdDayles += $qtdDayle;

                        $totalKickback += ($item->received_proposal * $qtdDayle * $item->kickback) / 100;
                        $sumTotalHotelCost += sumTotal($item->received_proposal, $taxesCost, $qtdDayle);

                        $sumTotalHotelSale += sumTotal(unitSale($item), $taxes, $qtdDayle);

                        ?>
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->category_hotel->name }}</td>
                            <td>{{ $item->regime->name }}</td>
                            <td>{{ $item->apto_hotel->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency(unitSale($item), $hotelEvent->currency->symbol) }}</td>
                            <td>
                                {{ formatCurrency(sumTaxesProvider($hotelEvent, $item), $hotelEvent->currency->symbol) }}
                            </td>
                            <td>
                                {{ formatCurrency(unitSale($item) + sumTaxesProvider($hotelEvent, $item), $hotelEvent->currency->symbol) }}
                            </td>
                            <td>{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($hotelEvent, $item), $item->count * daysBetween($item->in, $item->out)), $hotelEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <?php
                    $sumTotalHotelSaleTaxa = ((($sumTotalHotelSale * $hotelEvent->iof) / 100) + $sumTotalHotelSale) * 1.10;
                    ?>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="11" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr class="table-subheader" style="background-color: #ffe0b1">
                                        <th class="align-middle custom-bg-success-text-white">
                                            Room Nights:
                                        </th>
                                        <td class="align-middle">{{ $sumQtdDayles }}</td>
                                        <th class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumTotalHotelSale / $sumQtdDayles, $hotelEvent->currency->symbol) }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            EMITIR NOTA FISCAL?
                                        </th>
                                        <td class="align-middle">
                                            {{ $hotelEvent->invoice ? 'Sim' : 'Não' }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            Total Venda
                                        </th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumTotalHotelSale, $hotelEvent->currency->symbol) }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            Total Custo
                                        </th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumTotalHotelCost, $hotelEvent->currency->symbol) }}
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
                                            {{ formatCurrency(($sumTotalHotelSale * $hotelEvent->iss_percent) / 100, $hotelEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $hotelEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumTotalHotelCost * $hotelEvent->iss_percent) / 100, $hotelEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="background-color: #c1d9ff; border: 1px solid #ffffff; color: #000">TOTAL COM TAXAS CLIENTE</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalHotelSaleTaxa, $hotelEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $hotelEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalHotelSale * $hotelEvent->service_percent) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $hotelEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalHotelCost * $hotelEvent->service_percent) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff; background-color: #ffe5e5; color: #000">TOTAL COM TAXAS A PAGAR</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalHotelCost, $hotelEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $hotelEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalHotelSale * $hotelEvent->iva_percent) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $hotelEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalHotelCost * $hotelEvent->iva_percent) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">LUCRO TOTAL</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalHotelSaleTaxa - $sumTotalHotelCost, $hotelEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA TURISMO ({{ $hotelEvent->service_charge }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalHotelSale * $hotelEvent->service_charge) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA TURISMO ({{ $hotelEvent->service_charge }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalHotelCost * $hotelEvent->service_charge) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">Comissão</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($totalKickback, $hotelEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IOF ({{ $hotelEvent->iof }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalHotelSale * $hotelEvent->iof) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IOF ({{ $hotelEvent->iof }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalHotelCost * $hotelEvent->iof) / 100, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;" colspan="2"></td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">Serviço 4BTS (10.00%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalHotelSale * 0.10, $hotelEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;" colspan="4"></td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </tfoot>
                </table>
                @endif


                @if($abEvent != null && $abEvent->eventAbOpts != null && count($abEvent->eventAbOpts) > 0)
                <?php
                if ($abEvent->iof > 0) {
                    $percIOF = $abEvent->iof;
                }
                if ($abEvent->service_charge > 0) {
                    $percService = $abEvent->service_charge;
                }
                ?>
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
                        <?php
                        $rate = floatval($item->received_proposal);
                        $taxes = floatval(sumTaxesProvider($abEvent, $item));
                        $qtdDayle = $item->count * daysBetween1($item->in, $item->out);

                        $sumABValueRate += $rate;
                        $sumABQtdDayles += $qtdDayle;
                        $totalKickbackAB += ($sumABValueRate * floatval($item->kickback)) / 100;
                        $sumTotalABValue += sumTotal($rate, $taxes, $qtdDayle);
                        ?>

                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->service_type->name }}</td>
                            <td>{{ $item->local->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween1($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency($item->received_proposal, $abEvent->currency->symbol) }}</td>
                            <td>
                                {{ formatCurrency(sumTaxesProvider($abEvent, $item), $abEvent->currency->symbol) }}
                            </td>
                            <td>
                                {{ formatCurrency($item->received_proposal + sumTaxesProvider($abEvent, $item), $abEvent->currency->symbol) }}
                            </td>
                            <td>{{ formatCurrency(sumTotal($item->received_proposal, sumTaxesProvider($abEvent, $item), $item->count * daysBetween1($item->in, $item->out)), $abEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="10" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr class="table-subheader" style="background-color: #ffe0b1">
                                        <th class="align-middle custom-bg-success-text-white">
                                            Room Nights:
                                        </th>
                                        <td class="align-middle">{{ $sumABQtdDayles }}</td>
                                        <th class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumABValueRate / $sumABQtdDayles, $abEvent->currency->symbol) }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            EMITIR NOTA FISCAL?
                                        </th>
                                        <td class="align-middle">
                                            {{ $abEvent->invoice ? 'Sim' : 'Não' }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            Total Venda
                                        </th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumTotalABValue, $abEvent->currency->symbol) }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            Total Custo
                                        </th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumABValueRate, $abEvent->currency->symbol) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="6">{{ $abEvent->internal_observation }}</td>
                            <td>Comissão</td>
                            <td style="background-color: #ffc670; color: #000"><b>{{ formatCurrency($totalKickbackAB, $hotelEvent->currency->symbol) }}</b></td>
                        </tr>

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
                                            {{ formatCurrency(($sumTotalABValue * $abEvent->iss_percent) / 100, $abEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $abEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumABValueRate * $abEvent->iss_percent) / 100, $abEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="background-color: #c1d9ff; border: 1px solid #ffffff; color: #000">TOTAL COM TAXAS CLIENTE</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalABValue, $abEvent->currency->symbol) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $abEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalABValue * $abEvent->service_percent) / 100, $abEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $abEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumABValueRate * $abEvent->service_percent) / 100, $abEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff; background-color: #ffe5e5; color: #000">TOTAL COM TAXAS A PAGAR</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumABValueRate, $abEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $abEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalABValue * $abEvent->iva_percent) / 100, $abEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $abEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumABValueRate * $abEvent->iva_percent) / 100, $abEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">LUCRO TOTAL</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalABValue - $sumABValueRate, $abEvent->currency->symbol) }}</td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </tfoot>

                </table>
                @endif


                @if($hallEvent != null && $hallEvent->eventHallOpts != null && count($hallEvent->eventHallOpts) > 0)
                <?php
                if ($hallEvent->iof > 0) {
                    $percIOF = $hallEvent->iof;
                }
                if ($hallEvent->service_charge > 0) {
                    $percService = $hallEvent->service_charge;
                }
                ?>
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
                        <?php
                        $rate = floatval($item->received_proposal);
                        $taxes = floatval(sumTaxesProvider($hallEvent, $item));

                        $sumHallValueRate += $rate;
                        $sumHallQtdDayles += daysBetween1($item->in, $item->out);
                        $totalKickbackHall += ($sumHallValueRate * floatval($item->kickback)) / 100;
                        $sumTotalHallValue += ($rate + $taxes) * daysBetween1($item->in, $item->out);
                        ?>
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->m2 }}</td>
                            <td>{{ $item->purpose->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween1($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency($item->received_proposal, $hallEvent->currency->symbol) }}</td>
                            <td>
                                {{ formatCurrency(sumTaxesProvider($hallEvent, $item), $hallEvent->currency->symbol) }}
                            </td>
                            <td>
                                {{ formatCurrency($item->received_proposal + sumTaxesProvider($hallEvent, $item), $hallEvent->currency->symbol) }}
                            </td>
                            <td>{{ formatCurrency(sumTotal($item->received_proposal, sumTaxesProvider($hallEvent, $item), $item->count * daysBetween1($item->in, $item->out)), $hallEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="11" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr class="table-subheader" style="background-color: #ffe0b1">
                                        <th class="align-middle custom-bg-success-text-white">
                                            Room Nights:
                                        </th>
                                        <td class="align-middle">{{ $sumHallQtdDayles }}</td>
                                        <th class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumHallValueRate / $sumHallQtdDayles, $hallEvent->currency->symbol) }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            EMITIR NOTA FISCAL?
                                        </th>
                                        <td class="align-middle">
                                            {{ $hallEvent->invoice ? 'Sim' : 'Não' }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            Total Venda
                                        </th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumTotalHallValue, $hallEvent->currency->symbol) }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            Total Custo
                                        </th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumHallValueRate, $hallEvent->currency->symbol) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="7">{{ $hallEvent->internal_observation }}</td>
                            <td>Comissão</td>
                            <td style="background-color: #ffc670; color: #000"><b>{{ formatCurrency($totalKickbackHall, $hotelEvent->currency->symbol) }}</b></td>
                        </tr>

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
                                            {{ formatCurrency(($sumTotalHallValue * $hallEvent->iss_percent) / 100, $hallEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $hallEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumHallValueRate * $hallEvent->iss_percent) / 100, $hallEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="background-color: #c1d9ff; border: 1px solid #ffffff; color: #000">TOTAL COM TAXAS CLIENTE</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalHallValue, $hallEvent->currency->symbol) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $hallEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalHallValue * $hallEvent->service_percent) / 100, $hallEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $hallEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHallValueRate * $hallEvent->service_percent) / 100, $hallEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff; background-color: #ffe5e5; color: #000">TOTAL COM TAXAS A PAGAR</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumHallValueRate, $hallEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $hallEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalHallValue * $hallEvent->iva_percent) / 100, $hallEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $hallEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumHallValueRate * $hallEvent->iva_percent) / 100, $hallEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">LUCRO TOTAL</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalHallValue - $sumHallValueRate, $hallEvent->currency->symbol) }}</td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </tfoot>

                </table>
                @endif


                @if($addEvent != null && $addEvent->eventAddOpts != null && count($addEvent->eventAddOpts) > 0)
                <?php
                if ($addEvent->iof > 0) {
                    $percIOF = $addEvent->iof;
                }
                if ($addEvent->service_charge > 0) {
                    $percService = $addEvent->service_charge;
                }
                ?>
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
                        <?php
                        $rate = floatval($item->received_proposal);
                        $taxes = floatval(sumTaxesProvider($addEvent, $item));
                        $qtdDayle = $item->count * daysBetween1($item->in, $item->out);

                        $sumAddValueRate += $rate;
                        $sumAddQtdDayles += $qtdDayle;
                        $totalKickbackAdd += ($sumAddValueRate * floatval($item->kickback)) / 100;
                        $sumTotalAddValue += sumTotal($rate, $taxes, daysBetween1($item->in, $item->out));
                        ?>

                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->service->name }}</td>
                            <td>{{ $item->frequency->name }}</td>
                            <td>{{ $item->measure->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween1($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency($item->received_proposal, $addEvent->currency->symbol) }}</td>
                            <td>
                                {{ formatCurrency(sumTaxesProvider($addEvent, $item), $addEvent->currency->symbol) }}
                            </td>
                            <td>
                                {{ formatCurrency($item->received_proposal + sumTaxesProvider($addEvent, $item), $addEvent->currency->symbol) }}
                            </td>
                            <td>{{ formatCurrency(sumTotal($item->received_proposal, sumTaxesProvider($addEvent, $item), $item->count * daysBetween1($item->in, $item->out)), $addEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>


                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="11" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr class="table-subheader" style="background-color: #ffe0b1">
                                        <th class="align-middle custom-bg-success-text-white">
                                            Room Nights:
                                        </th>
                                        <td class="align-middle">{{ $sumAddQtdDayles }}</td>
                                        <th class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumAddValueRate / $sumAddQtdDayles, $addEvent->currency->symbol) }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            EMITIR NOTA FISCAL?
                                        </th>
                                        <td class="align-middle">
                                            {{ $addEvent->invoice ? 'Sim' : 'Não' }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            Total Venda
                                        </th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumTotalAddValue, $addEvent->currency->symbol) }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            Total Custo
                                        </th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumAddValueRate, $addEvent->currency->symbol) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="7">{{ $addEvent->internal_observation }}</td>
                            <td>Comissão</td>
                            <td style="background-color: #ffc670; color: #000"><b>{{ formatCurrency($totalKickbackAdd, $hotelEvent->currency->symbol) }}</b></td>
                        </tr>

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
                                            {{ formatCurrency(($sumTotalAddValue * $addEvent->iss_percent) / 100, $addEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $addEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumAddValueRate * $addEvent->iss_percent) / 100, $addEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="background-color: #c1d9ff; border: 1px solid #ffffff; color: #000">TOTAL COM TAXAS CLIENTE</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalAddValue, $addEvent->currency->symbol) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $addEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalAddValue * $addEvent->service_percent) / 100, $addEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $addEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAddValueRate * $addEvent->service_percent) / 100, $addEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff; background-color: #ffe5e5; color: #000">TOTAL COM TAXAS A PAGAR</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumAddValueRate, $addEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $addEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalAddValue * $addEvent->iva_percent) / 100, $addEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $addEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumAddValueRate * $addEvent->iva_percent) / 100, $addEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">LUCRO TOTAL</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalAddValue - $sumAddValueRate, $addEvent->currency->symbol) }}</td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </tfoot>

                </table>
                @endif

                @if($transportEvent != null && $transportEvent->eventTransportOpts != null && count($transportEvent->eventTransportOpts) > 0)
                <?php
                if ($transportEvent->iof > 0) {
                    $percIOF = $transportEvent->iof;
                }
                if ($transportEvent->service_charge > 0) {
                    $percService = $transportEvent->service_charge;
                }
                ?>
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
                        <?php

                        $rate = floatval($item->received_proposal);
                        $taxes = floatval(sumTaxesProvider($transportEvent, $item));
                        $qtdDayle = $item->count * daysBetween1($item->in, $item->out);

                        $sumTransportValueRate += $rate;
                        $sumTransportQtdDayles += $qtdDayle;
                        $totalKickbackTransport += ($sumTransportValueRate * floatval($item->kickback)) / 100;
                        $sumTotalTransportValue += sumTotal($rate, $taxes, daysBetween1($item->in, $item->out));
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

                            <td>{{ formatCurrency($item->received_proposal, $transportEvent->currency->symbol) }}</td>
                            <td>
                                {{ formatCurrency(sumTaxesProvider($transportEvent, $item), $transportEvent->currency->symbol) }}
                            </td>
                            <td>
                                {{ formatCurrency($item->received_proposal + sumTaxesProvider($transportEvent, $item), $transportEvent->currency->symbol) }}
                            </td>
                            <td>{{ formatCurrency(sumTotal($item->received_proposal, sumTaxesProvider($transportEvent, $item), $item->count * daysBetween1($item->in, $item->out)), $transportEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>


                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="11" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr class="table-subheader" style="background-color: #ffe0b1">
                                        <th class="align-middle custom-bg-success-text-white">
                                            Room Nights:
                                        </th>
                                        <td class="align-middle">{{ $sumTransportQtdDayles }}</td>
                                        <th class="align-middle custom-bg-success-text-white">DIÁRIA MÉDIA</th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumTransportValueRate / $sumTransportQtdDayles, $transportEvent->currency->symbol) }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            EMITIR NOTA FISCAL?
                                        </th>
                                        <td class="align-middle">
                                            {{ $transportEvent->invoice ? 'Sim' : 'Não' }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            Total Venda
                                        </th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumTotalTransportValue, $transportEvent->currency->symbol) }}
                                        </td>
                                        <th class="align-middle custom-bg-success-text-white">
                                            Total Custo
                                        </th>
                                        <td class="align-middle">
                                            {{ formatCurrency($sumTransportValueRate, $transportEvent->currency->symbol) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="7">{{ $transportEvent->internal_observation }}</td>
                            <td>Comissão</td>
                            <td style="background-color: #ffc670; color: #000"><b>{{ formatCurrency($totalKickbackTransport, $hotelEvent->currency->symbol) }}</b></td>
                        </tr>

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
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $transportEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumTotalTransportValue * $transportEvent->iss_percent) / 100, $transportEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">ISS ({{ $transportEvent->iss_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">
                                            {{ formatCurrency(($sumTransportValueRate * $transportEvent->iss_percent) / 100, $transportEvent->currency->symbol) }}
                                        </td>
                                        <td class="custom-bg-success-text-white" style="background-color: #c1d9ff; border: 1px solid #ffffff; color: #000">TOTAL COM TAXAS CLIENTE</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalTransportValue, $transportEvent->currency->symbol) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $transportEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalTransportValue * $transportEvent->service_percent) / 100, $transportEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">TAXA DE SERVIÇO ({{ $transportEvent->service_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTransportValueRate * $transportEvent->service_percent) / 100, $transportEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff; background-color: #ffe5e5; color: #000">TOTAL COM TAXAS A PAGAR</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTransportValueRate, $transportEvent->currency->symbol) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $transportEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTotalTransportValue * $transportEvent->iva_percent) / 100, $transportEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">IVA ({{ $transportEvent->iva_percent }}%)</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency(($sumTransportValueRate * $transportEvent->iva_percent) / 100, $transportEvent->currency->symbol) }}</td>
                                        <td class="custom-bg-success-text-white" style="border: 1px solid #ffffff;">LUCRO TOTAL</td>
                                        <td style="background-color: #ffe0b1; border: 1px solid #ffffff;">{{ formatCurrency($sumTotalTransportValue - $sumTransportValueRate, $transportEvent->currency->symbol) }}</td>
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
                    <tbody>$sumTaxeHotelCost = 0;
                        = 0;
                        @if($hotelEvent != null && $hotelEvent->eventHotelsOpt != null && count($hotelEvent->eventHotelsOpt) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Hospedagem</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTotalHotelSaleTaxa, $hotelEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency($sumTotalHotelSale * 0.1, $hotelEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTaxeHotelSale, $hotelEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTotalHotelCost, $hotelEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTaxeHotelCost, $hotelEvent->currency->symbol) }}</td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif
                        @if($abEvent != null && $abEvent->eventAbOpts != null && count($abEvent->eventAbOpts) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Alimentos & Bebidas</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumABValueRate, $abEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency($sumTotalABValue - $sumABValueRate, $abEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTotalABValue - $sumABValueRate, $abEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumABValueRate, $abEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency($sumTotalABValue - $sumABValueRate, $abEvent->currency->symbol) }}</td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif

                        @if($hallEvent != null && $hallEvent->eventHallOpts != null && count($hallEvent->eventHallOpts) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Salões/ Eventos</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumHallValueRate, $hallEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency($sumTotalHallValue - $sumHallValueRate, $hallEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTotalHallValue - $sumHallValueRate, $hallEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumHallValueRate, $hallEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency($sumTotalHallValue - $sumHallValueRate, $hallEvent->currency->symbol) }}</td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif

                        @if($addEvent != null && $addEvent->eventAddOpts != null && count($addEvent->eventAddOpts) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Adicionais</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumAddValueRate, $addEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency($sumTotalAddValue - $sumAddValueRate, $addEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTotalAddValue - $sumAddValueRate, $addEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumAddValueRate, $addEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency($sumTotalAddValue - $sumAddValueRate, $addEvent->currency->symbol) }}</td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif
                        @if($transportEvent != null && $transportEvent->eventTransportOpts != null && count($transportEvent->eventTransportOpts) > 0)
                        <tr>
                            <td>Transporte</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTransportValueRate, $transportEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency($sumTotalTransportValue - $sumTransportValueRate, $transportEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTotalTransportValue - $sumTransportValueRate, $transportEvent->currency->symbol) }}</td>
                            <td class="custom-bg-success-text-white">{{ formatCurrency($sumTransportValueRate, $transportEvent->currency->symbol) }}</td>
                            <td>{{ formatCurrency($sumTotalTransportValue - $sumTransportValueRate, $transportEvent->currency->symbol) }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <table style="width: 50%; min-width: 9cm; max-width: 9cm; margin-top: 30px;">
                <tbody>
                    <tr style="background-color: #ebf8ff; color: #000;">
                        <td colspan="2" style="background-color: #ebf8ff;">TOTAL CLIENTE</td>
                        <td colspan="3" style="background-color: #ffe3b9; padding: 0.5rem;">
                            {{ formatCurrency($sumTotalHotelSaleTaxa + $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue) }}
                        </td>
                    </tr>
                    <tr style="background-color: #ebf8ff; color: #000; ">
                        <td colspan="2" style="background-color: #ebf8ff;">TOTAL A PAGAR</td>
                        <td colspan="3" style="background-color: #ffe3b9; padding: 0.5rem;">
                            {{ formatCurrency($sumTotalHotelCost + $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue) }}
                        </td>
                    </tr>
                    <tr style="background-color: #ebf8ff;">
                        <td colspan="2" style="background-color: #ebf8ff;">LUCRO TOTAL</td>
                        <td colspan="3" style="background-color: #ffe3b9; padding: 0.5rem;">
                            {{ formatCurrency(($sumTotalHotelSaleTaxa + $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue)
                                - ($sumTotalHotelCost + $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue)) }}
                        </td>
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