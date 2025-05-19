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
$sumTotalHotelValueRate = 0;

$sumTotalABValue = 0;
$sumABQtdDayles = 0;
$sumABValueRate = 0;
$sumTotalABValueRate = 0;

$sumTotalHallValue = 0;
$sumHallQtdDayles = 0;
$sumHallValueRate = 0;
$sumTotalHallValueRate = 0;

$sumTotalAddValue = 0;
$sumAddQtdDayles = 0;
$sumAddValueRate = 0;
$sumTotalAddValueRate = 0;

$sumTransportValueRate = 0;
$sumTransportQtdDayles = 0;
$sumTotalTransportValue = 0;
$sumTotalTransportValueRate = 0;

$percIOF = 0;
$percService = 10;

$hotelEvent  = null;
$abEvent = null;
$hallEvent  = null;
$addEvent  = null;
$transportEvent = null;

if ($provider != null) {
    if ($table == 'event_hotels') {
        $hotelEvent = $event->event_hotels->firstWhere('hotel_id', $provider->id);
    }

    if ($table == 'event_hotels') {
        $abEvent = $event->event_abs->firstWhere('ab_id', $provider->id);
    }

    if ($table == 'event_hotels') {
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

$qtdLinhas = 0;
$rodapeSize = 13;

if ($hotelEvent != null) {
    if ($hotelEvent->iof > 0) {
        $percIOF = $hotelEvent->iof;
    }
    $rodapeSize++;
    $qtdLinhas += 4;
    foreach ($hotelEvent->eventHotelsOpt as $item) {
        $qtdLinhas++;
        $rate = floatval(unitSale($item));
        $taxes = floatval(sumTaxesProvider($hotelEvent, $item));
        $qtdDayle = $item->count * daysBetween($item->in, $item->out);

        $sumTotalHotelValueRate += $rate * $qtdDayle;
        $sumValueRate += $rate;
        $sumQtdDayles += $qtdDayle;
        $sumTotalHotelValue += ($rate + $taxes) * $qtdDayle;
    }
}
if ($abEvent != null) {
    if ($abEvent->iof > 0) {
        $percIOF = $abEvent->iof;
    }
    $rodapeSize++;
    $qtdLinhas += 4;
    foreach ($abEvent->eventAbOpts as $item) {
        $qtdLinhas++;
        $rate = floatval(unitSale($item));
        $taxes = floatval(sumTaxesProvider($abEvent, $item));
        $qtdDayle = $item->count * daysBetween1($item->in, $item->out);

        $sumTotalABValueRate += $rate * $qtdDayle;
        $sumABValueRate += $rate;
        $sumABQtdDayles += $qtdDayle;
        $sumTotalABValue += ($rate + $taxes) * $qtdDayle;
    }
}
if ($hallEvent != null) {
    if ($hallEvent->iof > 0) {
        $percIOF = $hallEvent->iof;
    }
    $rodapeSize++;
    $qtdLinhas += 4;
    foreach ($hallEvent->eventHallOpts as $item) {
        $qtdLinhas++;
        $rate = floatval(unitSale($item));
        $taxes = floatval(sumTaxesProvider($hallEvent, $item));
        $qtdDayle = $item->count * daysBetween1($item->in, $item->out);

        $sumTotalHallValueRate += $rate * $qtdDayle;
        $sumHallValueRate += $rate;
        $sumHallQtdDayles +=  $qtdDayle;
        $sumTotalHallValue += ($rate + $taxes) *  $qtdDayle;
    }
}
if ($addEvent != null) {
    if ($addEvent->iof > 0) {
        $percIOF = $addEvent->iof;
    }
    $rodapeSize++;
    $qtdLinhas += 4;
    foreach ($addEvent->eventAddOpts as $item) {
        $qtdLinhas++;
        $rate = floatval(unitSale($item));
        $taxes = floatval(sumTaxesProvider($addEvent, $item));
        $qtdDayle = $item->count * daysBetween1($item->in, $item->out);

        $sumTotalAddValueRate += $rate * $qtdDayle;
        $sumAddValueRate += $rate;
        $sumAddQtdDayles += $qtdDayle;
        $sumTotalAddValue += ($rate + $taxes) * $qtdDayle;
    }
}
if ($transportEvent != null) {
    if ($transportEvent->iof > 0) {
        $percIOF = $transportEvent->iof;
    }

    $rodapeSize++;
    $qtdLinhas += 4;
    foreach ($transportEvent->eventTransportOpts as $item) {
        $qtdLinhas++;
        $rate = floatval(unitSale($item));
        $taxes = floatval(sumTaxesProvider($transportEvent, $item));
        $qtdDayle = $item->count * daysBetween1($item->in, $item->out);

        $sumTotalTransportValueRate += $rate * $qtdDayle;
        $sumTransportValueRate += $rate;
        $sumTransportQtdDayles += $qtdDayle;
        $sumTotalTransportValue += ($rate + $taxes) * $qtdDayle;
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
                    <div class="event-info">
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
                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="10" style="padding: 0.3rem; text-align: center;">Hospedagem</th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th>IN</th>
                            <th>Out</th>
                            <th>Qtd</th>

                            <th>Tipo</th>
                            <th>Categoria</th>

                            <th>Diárias</th>
                            <th>Valor</th>
                            <th>Taxas</th>
                            <th>TTL com Taxa</th>
                            <th>Total Geral</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hotelEvent->eventHotelsOpt as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>

                            <td>{{ $item->apto_hotel->name }}</td>
                            <td>{{ $item->category_hotel->name }}</td>

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
                    <tfoot class="table-footer">
                        <tr style="background-color: #ffe0b1">
                            <td colspan="1"><b>Comentários:</b></td>
                            <td colspan="7">{{ $hotelEvent->customer_observation }}</td>
                            <td><b>Prazo</b></td>
                            <td>{{ $hotelEvent->deadline_date === null ? "--" : date("d/m/Y", strtotime($hotelEvent->deadline_date)) }}</td>
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
                        @foreach($abEvent->eventAbOpts as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->service_type->name }}</td>
                            <td>{{ $item->local->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween1($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency(unitSale($item), $abEvent->currency->symbol) }}</td>
                            <td>
                                {{ formatCurrency(sumTaxesProvider($abEvent, $item), $abEvent->currency->symbol) }}
                            </td>
                            <td>
                                {{ formatCurrency(unitSale($item) + sumTaxesProvider($abEvent, $item), $abEvent->currency->symbol) }}
                            </td>
                            <td>{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($abEvent, $item), $item->count * daysBetween1($item->in, $item->out)), $abEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="6">{{ $abEvent->customer_observation }}</td>
                            <td><b>Prazo</b></td>
                            <td>{{ $abEvent->deadline_date === null ? "--" : date("d/m/Y", strtotime($abEvent->deadline_date)) }}</td>
                        </tr>
                    </tfoot>

                </table>
                @endif


                @if($hallEvent != null && $hallEvent->eventHallOpts != null && count($hallEvent->eventHallOpts) > 0)
                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="11" style="padding: 0.3rem; text-align: center;">Salões & Eventos</th>
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
                        @foreach($hallEvent->eventHallOpts as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->m2 }}</td>
                            <td>{{ $item->purpose->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween1($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency(unitSale($item), $hallEvent->currency->symbol) }}</td>
                            <td>
                                {{ formatCurrency(sumTaxesProvider($hallEvent, $item), $hallEvent->currency->symbol) }}
                            </td>
                            <td>
                                {{ formatCurrency(unitSale($item) + sumTaxesProvider($hallEvent, $item), $hallEvent->currency->symbol) }}
                            </td>
                            <td>{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($hallEvent, $item), $item->count * daysBetween1($item->in, $item->out)), $hallEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="7">{{ $hallEvent->customer_observation }}</td>
                            <td><b>Prazo</b></td>
                            <td>{{ $hallEvent->deadline_date === null ? "--" : date("d/m/Y", strtotime($hallEvent->deadline_date)) }}</td>
                        </tr>
                    </tfoot>

                </table>
                @endif


                @if($addEvent != null && $addEvent->eventAddOpts != null && count($addEvent->eventAddOpts) > 0)
                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="11" style="padding: 0.3rem; text-align: center;">Adicionais</th>
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
                        @foreach($addEvent->eventAddOpts as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <td>{{ $item->service->name }}</td>
                            <td>{{ $item->frequency->name }}</td>
                            <td>{{ $item->measure->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween1($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency(unitSale($item), $addEvent->currency->symbol) }}</td>
                            <td>
                                {{ formatCurrency(sumTaxesProvider($addEvent, $item), $addEvent->currency->symbol) }}
                            </td>
                            <td>
                                {{ formatCurrency(unitSale($item) + sumTaxesProvider($addEvent, $item), $addEvent->currency->symbol) }}
                            </td>
                            <td>{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($addEvent, $item), $item->count * daysBetween1($item->in, $item->out)), $addEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="7">{{ $addEvent->customer_observation }}</td>
                            <td><b>Prazo</b></td>
                            <td>{{ $addEvent->deadline_date === null ? "--" : date("d/m/Y", strtotime($addEvent->deadline_date)) }}</td>
                        </tr>
                    </tfoot>

                </table>
                @endif


                @if($transportEvent != null && $transportEvent->eventTransportOpts != null && count($transportEvent->eventTransportOpts) > 0)
                <table>
                    <thead style="display: table-header-group;">
                        <tr>
                            <th colspan="12" style="padding: 0.3rem; text-align: center;">Transporte Terrestre</th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <!-- <th>Marca</th> -->
                            <th>Veículo</th>
                            <th>Modelo</th>
                            <th>Serviço</th>
                            <th>OBS</th>
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
                        @foreach($transportEvent->eventTransportOpts as $key => $item)
                        <tr style="background-color: <?= $key % 2 == 0 ? '#ffffff' : '#f7fafc' ?>">
                            <!-- <td>{{ $item->brand->name }}</td> -->
                            <td>{{ $item->vehicle->name }}</td>
                            <td>{{ $item->model->name }}</td>
                            <td>{{ $item->service->name }}</td>
                            <td>{{ $item->observation }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->in)) }}</td>
                            <td>{{ date("d/m/Y", strtotime($item->out)) }}</td>
                            <td>{{ $item->count }}</td>
                            <td>{{ daysBetween1($item->in, $item->out) }}</td>

                            <td>{{ formatCurrency(unitSale($item), $transportEvent->currency->symbol) }}</td>
                            <td>
                                {{ formatCurrency(sumTaxesProvider($transportEvent, $item), $transportEvent->currency->symbol) }}
                            </td>
                            <td>
                                {{ formatCurrency(unitSale($item) + sumTaxesProvider($transportEvent, $item), $transportEvent->currency->symbol) }}
                            </td>
                            <td>{{ formatCurrency(sumTotal(unitSale($item), sumTaxesProvider($transportEvent, $item), $item->count * daysBetween1($item->in, $item->out)), $transportEvent->currency->symbol) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-footer">
                        <tr style="background-color: #ffe0b1">
                            <td colspan="2"><b>Comentários:</b></td>
                            <td colspan="8">{{ $transportEvent->customer_observation }}</td>
                            <td><b>Prazo</b></td>
                            <td>{{ $transportEvent->deadline_date === null ? "--" : date("d/m/Y", strtotime($transportEvent->deadline_date)) }}</td>
                        </tr>
                    </tfoot>

                </table>
                @endif

                <?php
                $quebrar = false;

                if ($qtdLinhas <= 19 && ($qtdLinhas + $rodapeSize) > 19) {
                    $quebrar = true;
                } elseif ($qtdLinhas > 19 && ($qtdLinhas % 27) + $rodapeSize > 24) {
                    $quebrar = true;
                }
                ?>
                <table style="<?= $quebrar ? "page-break-before: always;" : "" ?>">
                    <thead>
                        <tr>
                            <th colspan="5" style="padding: 0.3rem; text-align:center;">
                                Resumo da Proposta
                            </th>
                        </tr>
                        <tr style="background-color: #e9540d; color: rgb(250, 249, 249);">
                            <th>Serviços</th>
                            <th colspan="2" style="text-align: left;">Totais de:</th>
                            <th>Preço médio</th>
                            <th>Total do Pedido</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($hotelEvent != null && $hotelEvent->eventHotelsOpt != null && count($hotelEvent->eventHotelsOpt) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Hospedagem</td>
                            <td>Rooms Night</td>
                            <td>{{ $sumQtdDayles}}</td>
                            <td>{{ formatCurrency($sumValueRate / count($hotelEvent->eventHotelsOpt))}}</td>
                            <td>{{ formatCurrency($sumTotalHotelValue)}}</td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif

                        @if($abEvent != null && $abEvent->eventAbOpts != null && count($abEvent->eventAbOpts) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Alimentos & Bebidas</td>
                            <td>Refeições</td>
                            <td>{{ $sumABQtdDayles }}</td>
                            <td>{{ formatCurrency($sumABValueRate / count($abEvent->eventAbOpts))}}</td>
                            <td>{{formatCurrency($sumTotalABValue) }} </td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif


                        @if($hallEvent != null && $hallEvent->eventHallOpts != null && count($hallEvent->eventHallOpts) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Salões e eventos</td>
                            <td>Serviços</td>
                            <td>{{ $sumHallQtdDayles }}</td>
                            <td>{{ formatCurrency($sumHallValueRate / count($hallEvent->eventHallOpts))}}</td>
                            <td>{{ formatCurrency($sumTotalHallValue) }} </td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif


                        @if($addEvent != null && $addEvent->eventAddOpts != null && count($addEvent->eventAddOpts) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Adicionais</td>
                            <td>Serviços</td>
                            <td>{{ $sumAddQtdDayles }}</td>
                            <td>{{ formatCurrency($sumAddValueRate / count($addEvent->eventAddOpts))}}</td>
                            <td>{{ formatCurrency($sumTotalAddValue) }} </td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif

                        @if($transportEvent != null && $transportEvent->eventTransportOpts != null && count($transportEvent->eventTransportOpts) > 0)
                        <tr style="background-color: <? !$strip ? '#ffffff' : '#f7fafc' ?>">
                            <td>Transportes</td>
                            <td>Veículos</td>
                            <td>{{ $sumTransportQtdDayles}}</td>
                            <td>{{ formatCurrency($sumTransportValueRate / count($transportEvent->eventTransportOpts))}}</td>
                            <td>{{ formatCurrency($sumTotalTransportValue)}}</td>
                        </tr>
                        <?php $strip = !$strip; ?>
                        @endif
                    </tbody>
                    <tfoot class="table-footer">
                        <tr style="background-color: #ebf8ff;">
                            <td colspan="5">

                                <table style="width: 100%;">
                                    <tr>
                                        <?php
                                        $total = $sumTotalHotelValue + $sumTotalABValue + $sumTotalHallValue + $sumTotalAddValue + $sumTotalTransportValue;

                                        $totalIOF = ($total * $percIOF) / 100;
                                        $totalServico = ((($total * $percIOF) / 100) + $total) * ($percService / 100);
                                        ?>
                                        <td style="height: 18px; width: 15%; float: left; background-color: #ffd18c; padding: 0.3rem;">IOF: </td>
                                        <td style="height: 18px; width: 15%; float: left; background-color: #ffe3b9; padding: 0.3rem;">{{ $percIOF }}% ({{formatCurrency($totalIOF)}})</td>
                                        <td style="height: 18px; width: 15%; float: left; background-color: #ffd18c; padding: 0.3rem;">Taxa de Serviço: </td>
                                        <td style="height: 18px; width: 15%; float: left; background-color: #ffe3b9; padding: 0.3rem;">{{ $percService }}% ({{formatCurrency($totalServico)}})</td>
                                        <td style="height: 18px; width: 15%; float: left; background-color: #ffd18c; padding: 0.3rem;">Valor Total: </td>
                                        <td style="height: 18px; width: 15%; float: left; background-color: #ffe3b9; padding: 0.3rem;">
                                            {{ formatCurrency($total + $totalIOF + $totalServico) }}
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