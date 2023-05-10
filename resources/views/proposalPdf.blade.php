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

if ($hotelEvent != null) {
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
    foreach ($hallEvent->eventHallOpts as $item) {
        $rate = floatval($item->received_proposal);
        $taxes = floatval(sumTaxesProvider($hallEvent, $item));

        $sumHallValueRate += $rate;
        $sumHallQtdDayles += daysBetween($item->in, $item->out);
        $sumTotalHallValue += ($rate + $taxes) * daysBetween($item->in, $item->out);
    }
}
if ($addEvent != null) {
    foreach ($addEvent->eventAddOpts as $item) {
        $rate = floatval($item->received_proposal);
        $taxes = floatval(sumTaxesProvider($hallEvent, $item));
        $qtdDayle = $item->count * daysBetween($item->in, $item->out);

        $sumAddValueRate += $rate;
        $sumAddQtdDayles += $qtdDayle;
        $sumTotalAddValue += ($rate + $taxes) * daysBetween($item->in, $item->out);
    }
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

        table {
            width: 15cm;
            max-width: 19cm !important;
        }
    </style>
</head>

<body>
    <div id="app">

        <div style="margin: 0 auto 2.5rem;">
            <header class="header">
                <div style="display: inline-block;vertical-align: middle;">
                    <img style="width: 4rem;height: 4rem;margin-right: 25px;vertical-align: middle;left: 1;" src="/storage/logos/logo.png" alt="4BTS">

                    <div style="display: inline-block;vertical-align: middle;">
                        <h1 style="font-weight: bold; font-size: 1.25rem; margin-bottom: 0.25rem;">Evento: {{ $event->name }}</h1>
                        <p style="color: #6B7280; font-size: 1rem; margin-bottom: 0;">Hotel: {{ $provider->name }}</p>
                    </div>
                </div>
                <div style="display: inline-block;vertical-align: middle;right: 16px;position: absolute;">
                    <img style="width: 5rem; height: 5rem; fill: #6B7280;" src="{{ asset($event->customer->logo)  }}" alt="4BTS">
                </div>
            </header>
        </div>


        <div>
            @if($hotelEvent != null && $hotelEvent->eventHotelsOpt != null && count($hotelEvent->eventHotelsOpt) > 0)
            <table style="font-size: 10pt; max-width: 19cm; width: 100%; border-collapse: collapse; border: 1px solid gray; page-break-inside: avoid;">
                <thead style="display: table-header-group;">
                    <tr style="background-color: <?php echo hexToRgb($event->customer->color, 0.5) ?>">
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
                <tbody style="page-break-inside: avoid;">
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
            <table style="font-size: 10pt; max-width: 19cm; width: 100%; border-collapse: collapse; border: 1px solid gray;margin-top: 27px; page-break-inside: avoid;">
                <thead style="display: table-header-group;">
                    <tr style="background-color: <?php echo hexToRgb($event->customer->color, 0.5) ?>">
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
                <tbody style="page-break-inside: avoid;">
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
            <table style="font-size: 10pt; max-width: 19cm; width: 100%; border-collapse: collapse; border: 1px solid gray;margin-top: 27px; page-break-inside: avoid;">
                <thead style="display: table-header-group;">
                    <tr style="background-color: <?php echo hexToRgb($event->customer->color, 0.5) ?>">
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
                <tbody style="page-break-inside: avoid;">
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
            <table style="font-size: 10pt; max-width: 19cm; width: 100%; border-collapse: collapse; border: 1px solid gray;margin-top: 27px; page-break-inside: avoid;">
                <thead style="display: table-header-group;">
                    <tr style="background-color: <?php echo hexToRgb($event->customer->color, 0.5) ?>">
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
                <tbody style="page-break-inside: avoid;">
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


        </div>

        <table style="width:100%; border-collapse: collapse; border: 1px solid #ccc; page-break-before: always;">
            <thead>
                <tr>
                    <th colspan="6" style="padding: 0.5rem; text-align:center; font-weight:bold;">
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
            <tbody style="page-break-inside: avoid;">
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
        <hr style="border-top: 1px solid black;">

        <footer id="footer" style="position: absolute; bottom: 0; width: 100%;">
            <div style="margin-top: 70px; text-align: right;">
                <div style="width: 5.5cm; float:right">
                    <hr style="border-top: 1px solid black; margin-top:32px;">
                </div>
                <p style="margin-right: 5cm;">PRAZO:</p>
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

            <div style="background-color: #1f2937; color: #fff;">
                <div style="display: inline-block; padding: 10px; ">
                    <p>Avenida das Americas, 3434 - Bloco 5 - Grupo 520</p>
                    <p>Barra da Tijuca - Rio de Janeiro - 22.640-102</p>
                </div>
                <div style="display: inline-block; padding: 10px; text-align: right; float: right;">
                    <p>Tel.: (+55 21) 2025-7900</p>
                    <p>www.4BTS.com.br</p>
                </div>
            </div>
        </footer>

    </div>

</body>

</html>