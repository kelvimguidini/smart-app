<?php
$event = $event ?? null;
$provider = $provider ?? null;
$table = $table ?? null;

$airfares = collect();
if ($event != null && isset($event->event_airfares)) {
    if ($provider != null) {
        $airfares = $event->event_airfares->filter(function($item) use ($provider) {
            return $item->airfare_id == $provider->id || $item->airline_id == $provider->id || $item->id == $provider->id;
        });
        if ($airfares->isEmpty()) {
            $airfares = $event->event_airfares;
        }
    } else {
        $airfares = $event->event_airfares;
    }
}

$operador = $event->airOperator->name ?? ($event->hotelOperator->name ?? ($event->landOperator->name ?? 'ADMIN'));

if (!function_exists('quebraTexto')) {
    function quebraTexto($texto, $limite = 40)
    {
        $palavras = explode(' ', $texto ?? '');
        $linhaAtual = '';
        $resultado = '';

        foreach ($palavras as $palavra) {
            if (strlen($linhaAtual . ' ' . $palavra) > $limite) {
                $resultado .= trim($linhaAtual) . "<br>";
                $linhaAtual = $palavra;
            } else {
                $linhaAtual .= ' ' . $palavra;
            }
        }

        $resultado .= trim($linhaAtual);
        return $resultado;
    }
}

function formatCurrencyBr($value) {
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

function formatDateBr($dateStr) {
    if (empty($dateStr)) return '-';
    try {
        $dt = new DateTime($dateStr);
        return $dt->format('d/m/Y');
    } catch (\Exception $e) {
        return $dateStr;
    }
}

function formatDateExtensoBr($dateStr) {
    if (empty($dateStr)) return '-';
    try {
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        $dt = new DateTime($dateStr);
        $dia = $dt->format('d');
        $mes = (int)$dt->format('m');
        return "{$dia} de " . ($meses[$mes] ?? $dt->format('M'));
    } catch (\Exception $e) {
        return $dateStr;
    }
}

function getImgSrc($path) {
    if (empty($path)) return null;
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:image')) {
        return $path;
    }
    $cleanPath = ltrim($path, '/');
    if (file_exists(public_path($cleanPath))) {
        return public_path($cleanPath);
    }
    if (file_exists(storage_path('app/public/' . preg_replace('#^storage/#', '', $cleanPath)))) {
        return storage_path('app/public/' . preg_replace('#^storage/#', '', $cleanPath));
    }
    return null;
}

function getAirfarePhotoSrc($airfare, $pNum) {
    $field = 'photo_' . $pNum;
    $val = $airfare->$field ?? null;
    if (!empty($val)) {
        $resolved = getImgSrc($val);
        if ($resolved && file_exists($resolved)) return $resolved;
    }
    // Fallback default image
    $defaultPath = public_path("images/airfares/default_{$pNum}.jpg");
    if (file_exists($defaultPath)) {
        return $defaultPath;
    }
    $altPath = public_path("storage/airfares/1xIaOwnuKjeo2MU8AQC94vS6J0iNH0haGK66oMDq.jpg");
    if (file_exists($altPath)) {
        return $altPath;
    }
    return null;
}

function formatTimePdf($timeStr) {
    if (empty($timeStr) || $timeStr === '-') return '-';
    $clean = preg_replace('/[^0-9]/', '', (string)$timeStr);
    if (strlen($clean) === 4) {
        return substr($clean, 0, 2) . ':' . substr($clean, 2, 2);
    }
    if (strpos((string)$timeStr, ':') !== false) {
        $parts = explode(':', (string)$timeStr);
        if (count($parts) >= 2) {
            return str_pad($parts[0], 2, '0', STR_PAD_LEFT) . ':' . str_pad($parts[1], 2, '0', STR_PAD_LEFT);
        }
    }
    return $timeStr;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Proposta Fretamento</title>
    <style>
        @page {
            margin: 10px;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #222222;
            font-size: 8pt;
            margin: 0;
            padding: 0;
        }
        .header {
            background: #3e3e3e;
            padding: 10px;
            width: 100%;
            margin: -10px;
            margin-bottom: 15px;
            height: 150px;
            text-align: start;
        }
        .left {
            float: left;
            width: 200px;
        }
        .right {
            float: right;
            width: 150px;
            text-align: start;
            margin-right: 0.2cm;
        }
        .center {
            vertical-align: middle;
            text-align: left !important;
            width: calc(100% - 350px - 0.2cm);
            margin: 0 0 auto 16px;
            height: 150px;
        }
        .header-table {
            border-collapse: collapse;
            margin-bottom: 10px;
            height: 150px;
            width: 100%;
        }
        .header-table td {
            vertical-align: middle;
            padding: 0;
            border: none;
        }
        .arrow {
            display: inline-block;
            margin: 15px 0;
            padding: 9px 40px;
            background-color: #e9540d;
            font-weight: bold;
            text-align: start;
        }
        .title {
            font-weight: bold;
            font-style: normal;
            color: #fff;
            font-size: 8pt;
            margin: 0;
        }
        .event-info {
            margin-top: 5px;
        }
        .line {
            white-space: nowrap;
            font-size: 8pt;
            text-align: start;
            text-transform: uppercase;
            padding: 0 8px;
            min-height: 15px;
        }
        .line p {
            display: inline-block;
            font-weight: bold;
            color: rgb(216, 93, 16);
            margin: 0 5px 0 0;
        }
        .event-data {
            font-weight: 700;
            color: #fff;
            margin-left: 4px;
            display: inline-table;
        }

        /* Seção de Trechos / Pernas de Voo (Card Executivo) */
        .flight-legs-box {
            width: 100%;
            margin-top: 12px;
            margin-bottom: 14px;
            border: 1px solid #CBD5E1;
            border-collapse: collapse;
        }
        .flight-legs-box th {
            background-color: #F1F5F9;
            color: #1E293B;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 8px;
            border-bottom: 2px solid #CBD5E1;
            text-align: left;
        }
        .flight-legs-box td {
            vertical-align: middle;
            padding: 7px 8px;
            border-bottom: 1px solid #E2E8F0;
            background-color: #FFFFFF;
        }
        .flight-legs-box tr.leg-even td {
            background-color: #F8FAFC;
        }
        .flight-legs-box tr:last-child td {
            border-bottom: none;
        }
        .leg-date-title {
            font-weight: bold;
            font-size: 8.5pt;
            color: #0F172A;
            line-height: 1.2;
        }
        .leg-flight-num {
            font-size: 6.8pt;
            color: #0284C7;
            font-weight: bold;
            margin-top: 2px;
        }
        .leg-iata {
            font-weight: bold;
            font-size: 11pt;
            color: #0F172A;
            line-height: 1;
        }
        .leg-airport {
            font-size: 7.2pt;
            color: #475569;
            margin-top: 2px;
            line-height: 1.2;
        }
        .leg-arrow {
            text-align: center;
            font-size: 11pt;
            color: #2563EB;
            font-weight: bold;
        }
        .leg-time-main {
            font-weight: bold;
            font-size: 8.5pt;
            color: #0F172A;
            line-height: 1.2;
        }
        .leg-time-sub {
            font-size: 6.5pt;
            color: #64748B;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* Bloco Intermediário: Specs vs Observações */
        .tbl-specs-obs {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .tbl-specs-obs td {
            vertical-align: top;
            padding: 0;
        }
        .spec-row td {
            padding: 3px 0;
            font-size: 8.5pt;
        }
        .spec-label {
            width: 95px;
            font-weight: bold;
            color: #111111;
        }
        .spec-val {
            color: #222222;
        }
        .spec-val-bold {
            font-weight: bold;
            color: #111111;
        }
        .valor-box {
            background-color: #DDE2E5;
            border: 1px solid #CBD5E1;
            margin-top: 8px;
            padding: 6px 12px;
        }
        .valor-label {
            font-weight: bold;
            font-size: 9pt;
            color: #111111;
            display: inline-block;
            width: 75px;
        }
        .valor-val {
            font-weight: bold;
            font-size: 10.5pt;
            color: #111111;
            display: inline-block;
        }

        /* Observações da Proposta */
        .obs-title {
            color: #00875A;
            font-weight: bold;
            font-size: 8.5pt;
            margin-bottom: 4px;
        }
        .obs-content {
            font-size: 7.2pt;
            color: #00875A;
            line-height: 1.4;
        }

        /* Grid de 4 Fotos 2x2 */
        .tbl-photos-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .tbl-photos-grid td {
            width: 50%;
            padding: 3px;
            vertical-align: middle;
        }
        .aircraft-photo {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
            border: 1px solid #CBD5E1;
        }
        .photo-fallback {
            width: 100%;
            height: 150px;
            background-color: #E2E8F0;
            border: 1px dashed #94A3B8;
            text-align: center;
            box-sizing: border-box;
            padding-top: 60px;
        }
        .disclaimer-txt {
            font-size: 6.8pt;
            font-style: italic;
            color: #444444;
            margin-top: 2px;
            margin-bottom: 12px;
        }

        /* Seção Notas Legais Padrão */
        .notes-section {
            page-break-inside: avoid;
            margin-top: 6px;
        }
        .notes-hdr {
            background-color: #00875A;
            color: #FFFFFF;
            font-size: 8.5pt;
            font-weight: bold;
            padding: 3px 6px;
        }
        .notes-txt {
            font-size: 6.5pt;
            color: #333333;
            line-height: 1.35;
            padding-top: 4px;
        }
        .notes-txt p {
            margin: 0 0 3px 0;
            text-align: justify;
        }

        /* Barra Verde de Rodapé */
        .footer-green-bar {
            margin-top: 15px;
            height: 8px;
            background-color: #00875A;
            width: 100%;
        }
    </style>
</head>
<body>

    <!-- CABEÇALHO DA PROPOSTA (PADRÃO 4BTS IDÊNTICO A HOTEL E TRANSPORTE) -->
    <header class="header">
        <table class="header-table" width="100%">
            <tr style="background-color: transparent;">
                <td class="left">
                    <div class="arrow">
                        <div class="title">PROPOSTA N° {{ $event != null ? $event->code : '' }}</div>
                    </div>
                    <div>
                        @if (extension_loaded('gd') && file_exists(public_path('logo.png')))
                            <img style="width: 150px;" src="{{ public_path('logo.png') }}" alt="4BTS">
                        @else
                            <span style="font-weight: bold; font-size: 16px; color: #e9540d;">4BTS</span>
                        @endif
                    </div>
                </td>
                <td class="center">
                    <div class="event-info">
                        <div class="line">
                            <p>Evento:</p>
                            <span class="event-data">{!! quebraTexto($event->name ?? '', 50) !!}</span>
                        </div>
                    </div>
                    <div class="event-info">
                        <div class="line">
                            <p>De:</p>
                            <span class="event-data">{{ $event->date ? date("d/m/Y", strtotime($event->date)) : '-' }}</span>
                            <p>Até:</p>
                            <span class="event-data">{{ $event->date_final ? date("d/m/Y", strtotime($event->date_final)) : '-' }}</span>
                        </div>
                    </div>
                    <div class="event-info">
                        <div class="line">
                            <p>Fornecedor:</p>
                            <span class="event-data">{{ $provider != null ? $provider->name : ($airfares->first()->airline->name ?? ($airfares->first()->provider->name ?? 'FRETAMENTO')) }}</span>
                        </div>
                    </div>
                    <div class="event-info">
                        <div class="line">
                            <p>CC:</p>
                            <span class="event-data">{{ $event->cost_center ?? '' }}</span>

                            @if($event->exchange_rate != null && $event->exchange_rate != 0 && $event->exchange_rate != 1)
                            <p>Câmbio</p> <span class="event-data">{{ $event->exchange_rate }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="event-info">
                        <div class="line">
                            <p>CONSULTOR:</p>
                            <span class="event-data">{{ $operador }}</span>
                        </div>
                    </div>

                </td>
                <td class="right">
                    @if (extension_loaded('gd') && $event != null && $event->customer != null && !empty($event->customer->logo) && file_exists(public_path($event->customer->logo)))
                        <img src="{{ public_path($event->customer->logo) }}" style="max-width: 100px; max-height: 100px;" alt="{{ $event->customer->name }}">
                    @elseif ($event != null && $event->customer != null)
                        <span style="font-weight: bold; font-size: 14px; color: #fff;">{{ $event->customer->name }}</span>
                    @endif
                </td>
            </tr>
        </table>
    </header>

    <!-- CONTEÚDO DA PROPOSTA DE FRETAMENTO -->
    @if($airfares->count() > 0)
        @foreach($airfares as $index => $airfare)
            @php
                $opts = $airfare->eventAirfareOpts ?? collect();
                $custoNet = (float)($airfare->total_net_sem_4bts ?? 0);
                $txUnit = (float)($airfare->taxa_embarque_unit ?? 0);
                $paxTotal = (int)($airfare->total_pax ?: (($airfare->pax_first + $airfare->pax_executiva + $airfare->pax_premium + $airfare->pax_economica) ?: 0));
                $totTaxaEmbarque = $txUnit * $paxTotal;
                $mk = ($airfare->markup && (float)$airfare->markup > 0) ? (float)$airfare->markup : 0.75;
                $vendaEstimada = $custoNet > 0 ? ($custoNet / $mk) : 0;
                $valorFinal = $vendaEstimada + $totTaxaEmbarque;
            @endphp

            <div class="charter-block" style="{{ $index > 0 ? 'page-break-before: always; margin-top: 15px;' : '' }}">
                
                <!-- 1. Linha de Trechos (Card Executivo de Voo) -->
                <table class="flight-legs-box">
                    <thead>
                        <tr>
                            <th style="width: 20%;">DATA</th>
                            <th style="width: 31%;">ORIGEM</th>
                            <th style="width: 3%; text-align: center;">&nbsp;</th>
                            <th style="width: 31%;">DESTINO</th>
                            <th style="width: 15%; text-align: right;">HORÁRIO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($opts as $optIdx => $opt)
                        @php
                            $origemRaw = $opt->outbound_origin ?: '-';
                            $origemCode = '';
                            $origemName = $origemRaw;
                            if (preg_match('/^(.*?)\s*\(([A-Z0-9]{3})\)$/i', trim($origemRaw), $m)) {
                                $origemName = trim($m[1]);
                                $origemCode = strtoupper($m[2]);
                            } elseif (preg_match('/\(([A-Z0-9]{3})\)/i', trim($origemRaw), $m)) {
                                $origemCode = strtoupper($m[1]);
                            }

                            $destRaw = $opt->outbound_destination ?: '-';
                            $destCode = '';
                            $destName = $destRaw;
                            if (preg_match('/^(.*?)\s*\(([A-Z0-9]{3})\)$/i', trim($destRaw), $m)) {
                                $destName = trim($m[1]);
                                $destCode = strtoupper($m[2]);
                            } elseif (preg_match('/\(([A-Z0-9]{3})\)/i', trim($destRaw), $m)) {
                                $destCode = strtoupper($m[1]);
                            }
                        @endphp
                        <tr class="{{ $optIdx % 2 == 1 ? 'leg-even' : '' }}">
                            <td>
                                <div class="leg-date-title">{{ formatDateExtensoBr($opt->outbound_date) }}</div>
                                @if(!empty($opt->outbound_flight_number))
                                    <div class="leg-flight-num">Voo: {{ $opt->outbound_flight_number }}</div>
                                @endif
                            </td>
                            <td>
                                @if($origemCode)
                                    <div class="leg-iata">{{ $origemCode }}</div>
                                    <div class="leg-airport">{{ $origemName }}</div>
                                @else
                                    <div class="leg-date-title">{{ $origemRaw }}</div>
                                @endif
                            </td>
                            <td class="leg-arrow">
                                &rarr;
                            </td>
                            <td>
                                @if($destCode)
                                    <div class="leg-iata">{{ $destCode }}</div>
                                    <div class="leg-airport">{{ $destName }}</div>
                                @else
                                    <div class="leg-date-title">{{ $destRaw }}</div>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if($opt->outbound_departure_time && $opt->outbound_arrival_time)
                                    <div class="leg-time-main">
                                        {{ formatTimePdf($opt->outbound_departure_time) }} <span style="color: #94A3B8; font-weight: normal;">às</span> {{ formatTimePdf($opt->outbound_arrival_time) }}
                                    </div>
                                    <div class="leg-time-sub">Partida &bull; Chegada</div>
                                @elseif($opt->outbound_departure_time)
                                    <div class="leg-time-main">{{ formatTimePdf($opt->outbound_departure_time) }}</div>
                                    <div class="leg-time-sub">Partida</div>
                                @else
                                    <div style="font-size: 7.5pt; color: #64748B; font-style: italic;">A confirmar</div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td>
                                <div class="leg-date-title">{{ formatDateExtensoBr($event->date) }}</div>
                            </td>
                            <td>
                                <div class="leg-date-title">Origem a confirmar</div>
                            </td>
                            <td class="leg-arrow">&rarr;</td>
                            <td>
                                <div class="leg-date-title">Destino a confirmar</div>
                            </td>
                            <td style="text-align: right;">
                                <div style="font-size: 7.5pt; color: #64748B; font-style: italic;">A confirmar</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- 2. Especificações da Aeronave + Valor vs Observações -->
                <table class="tbl-specs-obs">
                    <tr>
                        <!-- Coluna Esquerda: Especificações e Caixa de Valor -->
                        <td style="width: 46%; padding-right: 15px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr class="spec-row">
                                    <td class="spec-label">Aeronave:</td>
                                    <td class="spec-val-bold">{{ $airfare->equipment ?: 'Boeing 737' }}</td>
                                </tr>
                                <tr class="spec-row">
                                    <td class="spec-label" style="font-weight: normal;">Passageiros:</td>
                                    <td class="spec-val">
                                        @if($airfare->total_pax > 0)
                                            {{ $airfare->total_pax }} PAX @if($airfare->pax_executiva > 0) ({{ $airfare->pax_executiva }} em Executiva) @elseif($airfare->pax_economica > 0) ({{ $airfare->pax_economica }} em Econômica) @endif
                                        @elseif($paxTotal > 0)
                                            {{ $paxTotal }} PAX
                                        @else
                                            A confirmar
                                        @endif
                                    </td>
                                </tr>
                                <tr class="spec-row">
                                    <td class="spec-label" style="font-weight: normal;">Bagagem:</td>
                                    <td class="spec-val">
                                        @php
                                            $formatKgPessoa = function($val, $fallback) {
                                                if (empty($val)) return $fallback . ' kg por pessoa';
                                                $trimmed = trim((string)$val);
                                                if (preg_match('/^\d+$/', $trimmed)) {
                                                    return $trimmed . ' kg por pessoa';
                                                }
                                                if (stripos($trimmed, 'kg') !== false) {
                                                    return $trimmed;
                                                }
                                                if (preg_match('/\d+/', $trimmed, $m)) {
                                                    return $m[0] . ' kg por pessoa';
                                                }
                                                return $trimmed;
                                            };
                                            $poraoTxt = $formatKgPessoa($airfare->inc_porao ?? null, '23');
                                            $bordoTxt = !empty($airfare->inc_bagagem_bordo) ? $formatKgPessoa($airfare->inc_bagagem_bordo, '10') : null;
                                        @endphp
                                        {{ $poraoTxt }}@if($bordoTxt) (Bordo: {{ $bordoTxt }})@endif
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding-top: 6px;">
                                        <div class="valor-box">
                                            <span class="valor-label">Valor:</span>
                                            <span class="valor-val">{{ formatCurrencyBr($valorFinal) }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <!-- Coluna Direita: Observações da Proposta -->
                        <td style="width: 54%; padding-left: 15px;">
                            <div class="obs-title">Observações:</div>
                            <div class="obs-content">
                                @if(!empty($airfare->observations))
                                    {!! nl2br(e($airfare->observations)) !!}
                                @else
                                    • Os horários da programação estão sujeitos a disponibilidade de SLOT nos Aeroportos que operam sob esse sistema.<br>
                                    • O valor acima não inclui atendimentos e catering.<br>
                                    • Os valores estão sujeitos a alteração quando for realizada a solicitação de confirmação da aeronave.<br>
                                    • Não inclui taxa de embarque, taxa de serviço (10%) e IOF (3,5%).
                                @endif
                            </div>
                            @if(!empty($airfare->notes))
                                <div style="margin-top: 5px; padding: 4px 6px; background-color: #FFFDE7; border-left: 3px solid #F57F17; font-size: 7pt; color: #333333;">
                                    <strong>Notes (Fretamento):</strong> {{ $airfare->notes }}
                                </div>
                            @endif
                            @if(!empty($airfare->customer_observation))
                                <div style="margin-top: 5px; padding: 4px 6px; background-color: #F1F8E9; border-left: 3px solid #2E7D32; font-size: 7pt; color: #333333;">
                                    <strong>Observação Cliente:</strong> {{ $airfare->customer_observation }}
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>

                <!-- 3. Grid de 4 Fotos da Proposta (2x2) -->
                <table class="tbl-photos-grid">
                    <tr>
                        <td>
                            @php $p1 = getAirfarePhotoSrc($airfare, 1); @endphp
                            @if(extension_loaded('gd') && $p1 && file_exists($p1))
                                <img src="{{ $p1 }}" class="aircraft-photo">
                            @else
                                <div class="photo-fallback">
                                    <span style="font-size: 8.5pt; font-weight: bold; color: #475569;">Aeronave - Foto 1</span>
                                </div>
                            @endif
                        </td>
                        <td>
                            @php $p2 = getAirfarePhotoSrc($airfare, 2); @endphp
                            @if(extension_loaded('gd') && $p2 && file_exists($p2))
                                <img src="{{ $p2 }}" class="aircraft-photo">
                            @else
                                <div class="photo-fallback">
                                    <span style="font-size: 8.5pt; font-weight: bold; color: #475569;">Aeronave - Foto 2</span>
                                </div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>
                            @php $p3 = getAirfarePhotoSrc($airfare, 3); @endphp
                            @if(extension_loaded('gd') && $p3 && file_exists($p3))
                                <img src="{{ $p3 }}" class="aircraft-photo">
                            @else
                                <div class="photo-fallback">
                                    <span style="font-size: 8.5pt; font-weight: bold; color: #475569;">Aeronave - Foto 3</span>
                                </div>
                            @endif
                        </td>
                        <td>
                            @php $p4 = getAirfarePhotoSrc($airfare, 4); @endphp
                            @if(extension_loaded('gd') && $p4 && file_exists($p4))
                                <img src="{{ $p4 }}" class="aircraft-photo">
                            @else
                                <div class="photo-fallback">
                                    <span style="font-size: 8.5pt; font-weight: bold; color: #475569;">Aeronave - Foto 4</span>
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
                <div class="disclaimer-txt">* Imagens meramente ilustrativas</div>

            </div>
        @endforeach
    @endif

    <!-- 4. Seção de Notas Legais Padrão -->
    <div class="notes-section">
        <div class="notes-hdr">Notas</div>
        <div class="notes-txt">
            <p>Todas as ofertas estão sujeitas à aprovação final de conformidade [Risco], disponibilidade da aeronave e da tripulação no momento da confirmação da reserva, programação necessária, a companhia aérea obtendo slots de aeroporto, estacionamento e permissões nos aeroportos solicitados e todas as permissões e autorizações de rota.</p>
            <p>A menos que especificado acima, as ofertas são totalmente inclusivas da aeronave, taxas e encargos operacionais padrão. Excluídos, a menos que indicado acima, estão quaisquer taxas de manuseio fora do horário comercial e taxas de extensão, uso de telefone via satélite (se aplicável), desgelo da aeronave e / ou hangaragem, se necessário, custos excepcionais de catering e quaisquer impostos sobre carbono, ETS e similares cobrados pelo operador.</p>
            <p>Todas as ofertas estão sujeitas a sobretaxas de combustível [e flutuações cambiais] devido a variações de mercado. Os voos são operados de acordo com os termos e condições do nosso contrato padrão de sublocação. As fotos mostradas são apenas para fins de indicação, a aeronave real pode ser diferente.</p>
        </div>
    </div>

    <!-- 5. Rodapé Verde -->
    <div class="footer-green-bar"></div>

</body>
</html>
