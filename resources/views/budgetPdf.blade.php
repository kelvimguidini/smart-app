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
        $qtdDayle = $item->count * daysBetween1($item->in, $item->out);

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
        $sumHallQtdDayles += daysBetween1($item->in, $item->out);
        $sumTotalHallValue += ($rate + $taxes) * daysBetween1($item->in, $item->out);
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
        $taxes = floatval(sumTaxesProvider($addEvent, $item));
        $qtdDayle = $item->count * daysBetween1($item->in, $item->out);

        $sumAddValueRate += $rate;
        $sumAddQtdDayles += $qtdDayle;
        $sumTotalAddValue += ($rate + $taxes) * daysBetween1($item->in, $item->out);
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
        $qtdDayle = $item->count * daysBetween1($item->in, $item->out);

        $sumTransportValueRate += $rate;
        $sumTransportQtdDayles += $qtdDayle;
        $sumTotalTransportValue += ($rate + $taxes) * daysBetween1($item->in, $item->out);
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
                            <div class="title">Proposta n° {{ $event != null ? $event->code : '' }}</div>
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
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th>Regime</th>
                            <th>Propósito</th>
                            <th>Categoria</th>
                            <th>Apartamento</th>
                            <th>Entrada</th>
                            <th>Saída</th>
                            <th>Quantidade</th>
                            <th>Noites</th>
                            <th>Comissão %</th>
                            <th>Valor</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hotelEvent->eventHotelsOpt as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <th>{{ $item->regime->name }}</th>
                            <td>{{ $item->purpose->name }}</td>
                            <td>{{ $item->category_hotel->name  }}</td>
                            <td>{{ $item->apto_hotel->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween($item->in, $item->out) }}</td>

                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr>
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa de Hospedagem (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
                            <td style="padding: 0.75rem; text-align: left; vertical-align: top; border: 1px solid gray; padding: 10px 3px;" colspan="7" rowspan="3">
                                <label style="font-weight: bold;">Observação:</label>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa de ISS (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa IVA (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
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
                            <th>Serviço</th>
                            <th>Tipo Serviço</th>
                            <th>Local</th>
                            <th>De</th>
                            <th>Ate</th>
                            <th>Quantidade</th>
                            <th>Noites</th>

                            <th>Comissão %</th>
                            <th>Valor</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($abEvent->eventAbOpts as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->service->name }}</td>
                            <td>{{ $item->service_type->name }}</td>
                            <td>{{ $item->local->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween1($item->in, $item->out) }}</td>

                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa de Hospedagem (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
                            <td style="padding: 0.75rem; text-align: left; vertical-align: top; border: 1px solid gray; padding: 10px 3px;" colspan="7" rowspan="3">
                                <label style="font-weight: bold;">Observação:</label>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa de ISS (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa IVA (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
                        </tr>
                    </tfoot>

                </table>
                @endif


                @if($hallEvent != null && $hallEvent->eventHallOpts != null && count($hallEvent->eventHallOpts) > 0)
                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="10" style="padding: 0.5rem; text-align: center;">Salões & Eventos</th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th>Nome</th>
                            <th>Finalidade</th>
                            <th>M2</th>
                            <th>De</th>
                            <th>Ate</th>
                            <th>Quantidade</th>
                            <th>Noites</th>

                            <th>Comissão %</th>
                            <th>Valor</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hallEvent->eventHallOpts as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->purpose->name }}</td>
                            <td>{{ $item->m2 }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween1($item->in, $item->out) }}</td>

                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr>
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa de Hospedagem (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
                            <td style="padding: 0.75rem; text-align: left; vertical-align: top; border: 1px solid gray; padding: 10px 3px;" colspan="6" rowspan="3">
                                <label style="font-weight: bold;">Observação:</label>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa de ISS (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa IVA (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
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
                            <th>Serviço</th>
                            <th>Medida</th>
                            <th>Frequência</th>
                            <th>De</th>
                            <th>Ate</th>
                            <th>Quantidade</th>
                            <th>Noites</th>

                            <th>Comissão %</th>
                            <th>Valor</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($addEvent->eventAddOpts as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->service->name }}</td>
                            <td>{{ $item->measure->name }}</td>
                            <td>{{ $item->frequency->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween1($item->in, $item->out) }}</td>

                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr>
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa de Hospedagem (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
                            <td style="padding: 0.75rem; text-align: left; vertical-align: top; border: 1px solid gray; padding: 10px 3px;" colspan="6" rowspan="3">
                                <label style="font-weight: bold;">Observação:</label>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa de ISS (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa IVA (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
                        </tr>
                    </tfoot>
                </table>
                @endif


                @if($transportEvent != null && $transportEvent->eventTransportOpts != null && count($transportEvent->eventTransportOpts) > 0)
                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="10" style="padding: 0.5rem; text-align: center;">Transporte Terrestre</th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th>Marca</th>
                            <th>Veículo</th>
                            <th>Modelo</th>
                            <th>Serviço</th>
                            <th>De</th>
                            <th>Até</th>
                            <th>Qtd</th>

                            <th>Comissão %</th>
                            <th>Valor</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transportEvent->eventTransportOpts as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->brand->name }}</td>
                            <td>{{ $item->vehicle->name }}</td>
                            <td>{{ $item->model->name }}</td>
                            <td>{{ $item->service->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>

                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                            <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr>
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa de Hospedagem (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
                            <td style="padding: 0.75rem; text-align: left; vertical-align: top; border: 1px solid gray; padding: 10px 3px;" colspan="6" rowspan="3">
                                <label style="font-weight: bold;">Observação:</label>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa de ISS (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem;" colspan="3">
                                <label style="font-weight: bold;">Taxa IVA (%):</label>
                            </td>
                            <td style="padding: 0.75rem; border: 1px solid gray; padding: 10px 3px;"></td>
                        </tr>
                    </tfoot>

                </table>
                @endif

            </div>

            <?php
            $quebrar = false;

            if ($qtdLinhas <= 19 && ($qtdLinhas + $rodapeSize) > 19) {
                $quebrar = true;
            } elseif ($qtdLinhas > 19 && ($qtdLinhas % 27) + $rodapeSize > 27) {
                $quebrar = true;
            }
            ?>

            <hr style="border-top: 1px solid black; <?= $quebrar ? "page-break-before: always;" : "" ?>">

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