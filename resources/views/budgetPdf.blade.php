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
            border-bottom: 5px solid rgba(233, 84, 13, 1);
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
            width: 19cm !important;
            margin-bottom: 30px;
        }

        td {
            border: 1px solid black;
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
                    </div>
                </div>
            </header>
        </div>

        <div style="width: 92%; margin-bottom: 30px;">
            @if($hotelEvent != null && $hotelEvent->eventHotelsOpt != null && count($hotelEvent->eventHotelsOpt) > 0)
            <table style="max-width: 19cm; width: 100%; border-collapse: collapse; border: 1px solid gray; page-break-inside: avoid;">
                <thead style="display: table-header-group;">
                    <tr style="background-color: rgba(233, 84, 13, 0.5)">
                        <th colspan="11" style="padding: 15px 5px; text-align: center;">Hospedagem</th>
                    </tr>
                    <tr style="background-color: #f7fafc;">
                        <th style="border: 1px solid gray; padding: 10px 3px;">Regime</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Propósito</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Categoria</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Apartamento</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Entrada</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Saída</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Quantidade</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Noites</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Comissão %</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Valor</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Observação</th>
                    </tr>
                </thead>
                <tbody style="page-break-inside: avoid;">
                    @foreach($hotelEvent->eventHotelsOpt as $key => $item)
                    <tr>
                        <th style="border: 1px solid gray; padding: 10px 3px;">{{ $item->regime->name }}</th>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->purpose->name }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->category_hotel->name  }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->apto_hotel->name }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ date("d/m/Y", strtotime($item->in)) }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ date("d/m/Y", strtotime($item->out)) }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->count }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ daysBetween($item->in, $item->out) }}</td>

                        <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                        <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                        <td style="border: 1px solid gray; padding: 10px 3px; "></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot style="display: table-footer-group; background-color: #f7fafc;">
                    <tr>
                        <td style="padding: 0.75rem;" colspan="3">
                            <label style="font-weight: bold;">Taxa de Hospedagem (%):</label>
                        </td>
                        <td style="padding: 0.75rem;"></td>
                        <td style="padding: 0.75rem; text-align: left; vertical-align: top;" colspan="7" rowspan="3">
                            <label style="font-weight: bold;">Observação:</label>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.75rem;" colspan="3">
                            <label style="font-weight: bold;">Taxa de ISS (%):</label>
                        </td>
                        <td style="padding: 0.75rem;"></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.75rem;" colspan="3">
                            <label style="font-weight: bold;">Taxa IVA (%):</label>
                        </td>
                        <td style="padding: 0.75rem;"></td>
                    </tr>
                </tfoot>
            </table>
            @endif


            @if($abEvent != null && $abEvent->eventAbOpts != null && count($abEvent->eventAbOpts) > 0)
            <table style="max-width: 19cm; width: 100%; border-collapse: collapse; border: 1px solid gray; page-break-inside: avoid;">
                <thead style="display: table-header-group;">
                    <tr style="background-color: rgba(233, 84, 13, 0.5)">
                        <th colspan="10" style="padding: 15px 5px; text-align: center;">Alimentos & Bebidas</th>
                    </tr>
                    <tr style="background-color: #f7fafc;">
                        <th style="border: 1px solid gray; padding: 10px 3px;">Serviço</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Tipo Serviço</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Local</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">De</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Ate</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Quantidade</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Noites</th>

                        <th style="border: 1px solid gray; padding: 10px 3px;">Comissão %</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Valor</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Observação</th>
                    </tr>
                </thead>
                <tbody style="page-break-inside: avoid;">
                    @foreach($abEvent->eventAbOpts as $key => $item)
                    <tr>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->service->name }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->service_type->name }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->local->name }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ date("d/m/Y", strtotime($item->in)) }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ date("d/m/Y", strtotime($item->out)) }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->count }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ daysBetween($item->in, $item->out) }}</td>

                        <td style="border: 1px solid gray; padding: 10px 3px;"></td>
                        <td style="border: 1px solid gray; padding: 10px 3px;"></td>
                        <td style="border: 1px solid gray; padding: 10px 3px;"></td>
                    </tr>
                    @endforeach
                </tbody>

                <tfoot style="display: table-footer-group; background-color: #f7fafc;">
                    <tr>
                        <td style="padding: 0.75rem;" colspan="3">
                            <label style="font-weight: bold;">Taxa de Hospedagem (%):</label>
                        </td>
                        <td style="padding: 0.75rem;"></td>
                        <td style="padding: 0.75rem; text-align: left; vertical-align: top;" colspan="7" rowspan="3">
                            <label style="font-weight: bold;">Observação:</label>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.75rem;" colspan="3">
                            <label style="font-weight: bold;">Taxa de ISS (%):</label>
                        </td>
                        <td style="padding: 0.75rem;"></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.75rem;" colspan="3">
                            <label style="font-weight: bold;">Taxa IVA (%):</label>
                        </td>
                        <td style="padding: 0.75rem;"></td>
                    </tr>
                </tfoot>
            </table>
            @endif


            @if($hallEvent != null && $hallEvent->eventHallOpts != null && count($hallEvent->eventHallOpts) > 0)
            <table style="max-width: 19cm; width: 100%; border-collapse: collapse; border: 1px solid gray; page-break-inside: avoid;">
                <thead style="display: table-header-group;">
                    <tr style="background-color: rgba(233, 84, 13, 0.5)">
                        <th colspan="10" style="padding: 15px 5px; text-align: center;">Salões & Eventos</th>
                    </tr>
                    <tr style="background-color: #f7fafc;">
                        <th style="border: 1px solid gray; padding: 10px 3px;">Nome</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Finalidade</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">M2</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">De</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Ate</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Quantidade</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Noites</th>

                        <th style="border: 1px solid gray; padding: 10px 3px;">Comissão %</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Valor</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Observação</th>
                    </tr>
                </thead>
                <tbody style="page-break-inside: avoid;">
                    @foreach($hallEvent->eventHallOpts as $key => $item)
                    <tr>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->name }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->purpose->name }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->m2 }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ date("d/m/Y", strtotime($item->in)) }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ date("d/m/Y", strtotime($item->out)) }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->count }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ daysBetween($item->in, $item->out) }}</td>

                        <td style="border: 1px solid gray; padding: 10px 3px;"></td>
                        <td style="border: 1px solid gray; padding: 10px 3px;"></td>
                        <td style="border: 1px solid gray; padding: 10px 3px;"></td>
                    </tr>
                    @endforeach
                </tbody>

                <tfoot style="display: table-footer-group; background-color: #f7fafc;">
                    <tr>
                        <td style="padding: 0.75rem;" colspan="3">
                            <label style="font-weight: bold;">Taxa de Hospedagem (%):</label>
                        </td>
                        <td style="padding: 0.75rem;"></td>
                        <td style="padding: 0.75rem; text-align: left; vertical-align: top;" colspan="6" rowspan="3">
                            <label style="font-weight: bold;">Observação:</label>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.75rem;" colspan="3">
                            <label style="font-weight: bold;">Taxa de ISS (%):</label>
                        </td>
                        <td style="padding: 0.75rem;"></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.75rem;" colspan="3">
                            <label style="font-weight: bold;">Taxa IVA (%):</label>
                        </td>
                        <td style="padding: 0.75rem;"></td>
                    </tr>
                </tfoot>

            </table>
            @endif


            @if($addEvent != null && $addEvent->eventAddOpts != null && count($addEvent->eventAddOpts) > 0)
            <table style="max-width: 19cm; width: 100%; border-collapse: collapse; border: 1px solid gray; page-break-inside: avoid;">
                <thead style="display: table-header-group;">
                    <tr style="background-color: rgba(233, 84, 13, 0.5)">
                        <th colspan="10" style="padding: 15px 5px; text-align: center;">Adicionais</th>
                    </tr>

                    <tr style="background-color: #f7fafc;">
                        <th style="border: 1px solid gray; padding: 10px 3px;">Serviço</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Medida</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Frequência</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">De</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Ate</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Quantidade</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Noites</th>

                        <th style="border: 1px solid gray; padding: 10px 3px;">Comissão %</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Valor</th>
                        <th style="border: 1px solid gray; padding: 10px 3px;">Observação</th>
                    </tr>
                </thead>
                <tbody style="page-break-inside: avoid;">
                    @foreach($addEvent->eventAddOpts as $key => $item)
                    <tr>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->service->name }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->measure->name }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->frequency->name }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ date("d/m/Y", strtotime($item->in)) }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ date("d/m/Y", strtotime($item->out)) }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ $item->count }}</td>
                        <td style="border: 1px solid gray; padding: 10px 3px;">{{ daysBetween($item->in, $item->out) }}</td>

                        <td style="border: 1px solid gray; padding: 10px 3px;"></td>
                        <td style="border: 1px solid gray; padding: 10px 3px;"></td>
                        <td style="border: 1px solid gray; padding: 10px 3px;"></td>
                    </tr>
                    @endforeach
                </tbody>

                <tfoot style="display: table-footer-group; background-color: #f7fafc;">
                    <tr>
                        <td style="padding: 0.75rem;" colspan="3">
                            <label style="font-weight: bold;">Taxa de Hospedagem (%):</label>
                        </td>
                        <td style="padding: 0.75rem;"></td>
                        <td style="padding: 0.75rem; text-align: left; vertical-align: top;" colspan="6" rowspan="3">
                            <label style="font-weight: bold;">Observação:</label>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.75rem;" colspan="3">
                            <label style="font-weight: bold;">Taxa de ISS (%):</label>
                        </td>
                        <td style="padding: 0.75rem;"></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.75rem;" colspan="3">
                            <label style="font-weight: bold;">Taxa IVA (%):</label>
                        </td>
                        <td style="padding: 0.75rem;"></td>
                    </tr>
                </tfoot>
            </table>
            @endif

        </div>

        <hr style="border-top: 1px solid black;">

        <footer id="footer" style="position: absolute; bottom: 0; width: 100%;">

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