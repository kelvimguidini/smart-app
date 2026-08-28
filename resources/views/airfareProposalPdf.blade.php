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

function getImgSrc($path) {
    if (empty($path)) return null;
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:image')) {
        return $path;
    }
    $cleanPath = ltrim($path, '/');
    if (file_exists(public_path($cleanPath))) {
        return public_path($cleanPath);
    }
    return $path;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pedido Fretamento</title>
    <style>
        @page {
            margin: 12px 15px 12px 15px;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #222222;
            font-size: 8.5pt;
            margin: 0;
            padding: 0;
        }
        .header-title-bar {
            background-color: #E65100;
            color: #FFFFFF;
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            padding: 5px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .tbl-event {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .tbl-event td {
            border: 1px solid #333333;
            padding: 4px 6px;
            font-size: 8pt;
        }
        .lbl-orange {
            background-color: #F57C00;
            color: #FFFFFF;
            font-weight: bold;
            text-transform: uppercase;
            width: 18%;
        }
        .val-cell {
            background-color: #FFFFFF;
            color: #111111;
        }
        .charter-section {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .charter-title-bar {
            background-color: #FFB74D;
            color: #111111;
            font-size: 9.5pt;
            font-weight: bold;
            text-align: center;
            padding: 4px 0;
            text-transform: uppercase;
            border: 1px solid #333333;
            border-bottom: none;
        }
        .charter-title-bar-alt {
            background-color: #81C784;
            color: #111111;
            font-size: 9.5pt;
            font-weight: bold;
            text-align: center;
            padding: 4px 0;
            text-transform: uppercase;
            border: 1px solid #333333;
            border-bottom: none;
        }
        .tbl-header-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px;
        }
        .tbl-header-info td {
            border: 1px solid #333333;
            padding: 3px 5px;
            font-size: 7.5pt;
            text-align: center;
        }
        .hdr-lbl {
            background-color: #FFE0B2;
            font-weight: bold;
            text-transform: uppercase;
        }
        .hdr-lbl-alt {
            background-color: #C8E6C9;
            font-weight: bold;
            text-transform: uppercase;
        }
        .tbl-legs-inc {
            width: 100%;
            border-collapse: collapse;
            margin-top: -1px;
        }
        .tbl-legs-inc td {
            vertical-align: top;
            padding: 0;
        }
        .tbl-legs {
            width: 100%;
            border-collapse: collapse;
        }
        .tbl-legs th {
            background-color: #FFF3E0;
            border: 1px solid #333333;
            padding: 3px 4px;
            font-size: 7pt;
            text-transform: uppercase;
            text-align: center;
        }
        .tbl-legs-alt th {
            background-color: #E8F5E9;
            border: 1px solid #333333;
            padding: 3px 4px;
            font-size: 7pt;
            text-transform: uppercase;
            text-align: center;
        }
        .tbl-legs td {
            border: 1px solid #333333;
            padding: 3px 4px;
            font-size: 7.5pt;
            text-align: center;
        }
        .tbl-inc {
            width: 100%;
            border-collapse: collapse;
        }
        .tbl-inc th {
            background-color: #FFE0B2;
            border: 1px solid #333333;
            padding: 3px 4px;
            font-size: 7pt;
            text-transform: uppercase;
            text-align: center;
        }
        .tbl-inc-alt th {
            background-color: #C8E6C9;
            border: 1px solid #333333;
            padding: 3px 4px;
            font-size: 7pt;
            text-transform: uppercase;
            text-align: center;
        }
        .tbl-inc td {
            border: 1px solid #333333;
            padding: 2.5px 4px;
            font-size: 7pt;
        }
        .tbl-totals {
            width: 100%;
            border-collapse: collapse;
            margin-top: -1px;
        }
        .tbl-totals td {
            border: 1px solid #333333;
            padding: 3px 5px;
            font-size: 7.5pt;
        }
        .tot-lbl {
            background-color: #FFE0B2;
            font-weight: bold;
            text-transform: uppercase;
        }
        .tot-lbl-alt {
            background-color: #C8E6C9;
            font-weight: bold;
            text-transform: uppercase;
        }
        .photos-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .photo-cell {
            width: 50%;
            padding: 3px;
            text-align: center;
        }
        .photo-box {
            width: 100%;
            height: 140px;
            background-color: #F8FAFC;
            border: 1px solid #CBD5E1;
            overflow: hidden;
            text-align: center;
        }
        .photo-img {
            max-width: 100%;
            max-height: 140px;
            object-fit: cover;
        }
        .disclaimer {
            font-size: 7pt;
            font-style: italic;
            color: #444444;
            margin-top: 2px;
            margin-bottom: 8px;
        }
        .notes-section {
            margin-top: 6px;
        }
        .notes-header {
            background-color: #00875A;
            color: #FFFFFF;
            font-size: 8.5pt;
            font-weight: bold;
            padding: 3px 6px;
        }
        .notes-body {
            font-size: 6.8pt;
            color: #333333;
            line-height: 1.25;
            padding-top: 4px;
        }
        .notes-body p {
            margin: 0 0 4px 0;
            text-align: justify;
        }
        .footer-bar {
            margin-top: 10px;
            background-color: #00875A;
            color: #FFFFFF;
            font-size: 7.5pt;
            font-weight: bold;
            text-align: center;
            padding: 3px 0;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

    <!-- BLOCO 1: CABEÇALHO DO EVENTO (PEDIDO FRETAMENTO) -->
    <div class="header-title-bar">PEDIDO FRETAMENTO</div>

    <table class="tbl-event">
        <tr>
            <td class="lbl-orange">NOME DO EVENTO:</td>
            <td class="val-cell" style="width: 32%;">{{ $event->name ?? '' }}</td>
            <td class="lbl-orange" style="width: 18%;">DATA DO EVENTO:</td>
            <td class="val-cell" style="width: 32%;">{{ formatDateBr($event->date ?? '') }} {{ $event->date_final ? 'a ' . formatDateBr($event->date_final) : '' }}</td>
        </tr>
        <tr>
            <td class="lbl-orange">SOLICITANTE:</td>
            <td class="val-cell">{{ $event->requester ?? '' }}</td>
            <td class="lbl-orange">CLIENTE:</td>
            <td class="val-cell">{{ $event->customer->name ?? '' }}</td>
        </tr>
        <tr>
            <td class="lbl-orange">CRD:</td>
            <td class="val-cell">{{ $event->crd->name ?? '' }}</td>
            <td class="lbl-orange">OPERADOR:</td>
            <td class="val-cell">{{ $event->airOperator->name ?? ($event->hotelOperator->name ?? '') }}</td>
        </tr>
        <tr>
            <td class="lbl-orange">DIVISÃO:</td>
            <td class="val-cell">{{ $event->sector ?? '' }}</td>
            <td class="val-cell" colspan="2" rowspan="3" style="text-align: right; vertical-align: middle; padding-right: 15px;">
                @if(file_exists(public_path('logo.png')))
                    <img src="{{ public_path('logo.png') }}" style="width: 140px;" alt="4BTS">
                @endif
            </td>
        </tr>
        <tr>
            <td class="lbl-orange">BASE DE PAX:</td>
            <td class="val-cell">{{ $event->pax_base ?? '' }}</td>
        </tr>
        <tr>
            <td class="lbl-orange">CENTRO DE CUSTO:</td>
            <td class="val-cell">{{ $event->cost_center ?? '' }}</td>
        </tr>
    </table>

    <!-- BLOCO 2 & BLOCO 3: ORÇAMENTOS DE FRETAMENTO (Repete para cada Cia/Frete) -->
    @if($airfares->count() > 0)
        @foreach($airfares as $index => $airfare)
            <?php
            $isAlt = ($index % 2 !== 0);
            $ciaName = $airfare->airline->name ?? ($airfare->provider->name ?? 'Cia Aérea');
            $opts = $airfare->eventAirfareOpts ?? collect();
            ?>

            <div class="charter-section">
                <div class="{{ $isAlt ? 'charter-title-bar-alt' : 'charter-title-bar' }}">
                    ORÇAMENTO FRETAMENTO {{ $index + 1 }} - {{ mb_strtoupper($ciaName) }}
                </div>

                <!-- Cabeçalho do Frete: Cia, Aeronave, Assentos, Pax, Prazo, Status -->
                <table class="tbl-header-info">
                    <tr>
                        <td class="{{ $isAlt ? 'hdr-lbl-alt' : 'hdr-lbl' }}" style="width: 10%;">CIA:</td>
                        <td style="width: 25%; font-weight: bold;">{{ $ciaName }}</td>
                        <td class="{{ $isAlt ? 'hdr-lbl-alt' : 'hdr-lbl' }}" style="width: 8%;">FIRST</td>
                        <td class="{{ $isAlt ? 'hdr-lbl-alt' : 'hdr-lbl' }}" style="width: 10%;">EXECUTIVA</td>
                        <td class="{{ $isAlt ? 'hdr-lbl-alt' : 'hdr-lbl' }}" style="width: 10%;">PREMIUM</td>
                        <td class="{{ $isAlt ? 'hdr-lbl-alt' : 'hdr-lbl' }}" style="width: 10%;">ECONÔMICA</td>
                        <td class="{{ $isAlt ? 'hdr-lbl-alt' : 'hdr-lbl' }}" style="width: 12%;">TOTAL PAX</td>
                        <td style="width: 5%; font-weight: bold;">{{ $airfare->total_pax ?: (($airfare->pax_first + $airfare->pax_executiva + $airfare->pax_premium + $airfare->pax_economica) ?: '-') }}</td>
                        <td class="{{ $isAlt ? 'hdr-lbl-alt' : 'hdr-lbl' }}" style="width: 10%;">PRAZO DA CIA:</td>
                        <td style="width: 10%;">{{ $airfare->prazo_cia ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="{{ $isAlt ? 'hdr-lbl-alt' : 'hdr-lbl' }}">EQUIPAMENTO:</td>
                        <td style="font-weight: bold;">{{ $airfare->aircraft ?: '-' }}</td>
                        <td>{{ $airfare->pax_first ?: 0 }}</td>
                        <td>{{ $airfare->pax_executiva ?: 0 }}</td>
                        <td>{{ $airfare->pax_premium ?: 0 }}</td>
                        <td>{{ $airfare->pax_economica ?: 0 }}</td>
                        <td colspan="2"></td>
                        <td class="{{ $isAlt ? 'hdr-lbl-alt' : 'hdr-lbl' }}">STATUS CONTRATO:</td>
                        <td>{{ $airfare->status_contrato ?: '-' }}</td>
                    </tr>
                </table>

                <!-- BLOCO 3: Trechos & Tarifas + Painel de Inclusões -->
                <table class="tbl-legs-inc">
                    <tr>
                        <!-- Coluna Esquerda: Tabela de Pernas do Voo -->
                        <td style="width: 72%;">
                            <table class="tbl-legs {{ $isAlt ? 'tbl-legs-alt' : '' }}">
                                <thead>
                                    <tr>
                                        <th style="width: 10%;">CIA</th>
                                        <th style="width: 10%;">VOO</th>
                                        <th style="width: 10%;">DE</th>
                                        <th style="width: 10%;">PARA</th>
                                        <th style="width: 14%;">DATAS</th>
                                        <th style="width: 10%;">SAÍDA</th>
                                        <th style="width: 10%;">CHEGADA</th>
                                        <th style="width: 8%;">MARK UP</th>
                                        <th style="width: 9%;">CUSTO</th>
                                        <th style="width: 9%;">VENDA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($opts->count() > 0)
                                        @foreach($opts as $opt)
                                            <?php
                                            $received = floatval($opt->received_proposal ?? 0);
                                            $percent = floatval($opt->received_proposal_percent ?? 0);
                                            $unitSale = $received;
                                            if ($percent > 0) {
                                                $factor = $percent > 2 ? $percent / 100 : $percent;
                                                $unitSale = ceil($received / $factor);
                                            }
                                            $markupText = $percent > 0 ? number_format($percent, 2, ',', '.') : '-';
                                            ?>
                                            <tr>
                                                <td>{{ $opt->outbound_airline->name ?? $ciaName }}</td>
                                                <td>{{ $opt->outbound_flight_number ?: '-' }}</td>
                                                <td>{{ $opt->outbound_origin ?: '-' }}</td>
                                                <td>{{ $opt->outbound_destination ?: '-' }}</td>
                                                <td>{{ formatDateBr($opt->outbound_date) }}</td>
                                                <td>{{ $opt->outbound_departure_time ?: '-' }}</td>
                                                <td>{{ $opt->outbound_arrival_time ?: '-' }}</td>
                                                <td>{{ $markupText }}</td>
                                                <td>{{ formatCurrencyBr($received) }}</td>
                                                <td>{{ formatCurrencyBr($unitSale) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="10" style="text-align: center; color: #777;">Nenhum trecho de voo cadastrado</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </td>

                        <!-- Coluna Direita: Inclusões e Serviços -->
                        <td style="width: 28%; border-left: 1px solid #333333;">
                            <table class="tbl-inc {{ $isAlt ? 'tbl-inc-alt' : '' }}">
                                <thead>
                                    <tr>
                                        <th style="width: 65%;">INCLUI:</th>
                                        <th style="width: 35%;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Taxas de Embarque</td>
                                        <td style="text-align: center; font-weight: bold;">{{ $airfare->inc_taxa_embarque ? 'SIM' : 'NÃO' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Serviço de Bordo</td>
                                        <td style="text-align: center; font-weight: bold;">{{ $airfare->inc_servico_bordo ? 'SIM' : 'NÃO' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Porão</td>
                                        <td style="text-align: center;">{{ $airfare->inc_porao ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Bagagem a bordo</td>
                                        <td style="text-align: center;">{{ $airfare->inc_bagagem_bordo ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Sala Vip Aeroporto</td>
                                        <td style="text-align: center; font-weight: bold;">{{ $airfare->inc_sala_vip ? 'SIM' : 'NÃO' }}</td>
                                    </tr>
                                    <tr>
                                        <td>FBO Origem</td>
                                        <td style="text-align: center;">{{ $airfare->inc_fbo_origem ?: '0' }}</td>
                                    </tr>
                                    <tr>
                                        <td>FBO Destino</td>
                                        <td style="text-align: center;">{{ $airfare->inc_fbo_destino ?: '0' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Alterações de Nomes</td>
                                        <td style="text-align: center; font-weight: bold;">{{ $airfare->inc_alteracao_nomes ? 'SIM' : 'NÃO' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Painel de Totais Financeiros e Impostos -->
                <table class="tbl-totals">
                    <tr>
                        <td class="{{ $isAlt ? 'tot-lbl-alt' : 'tot-lbl' }}" style="width: 15%;">TAXA DE EMBARQUE</td>
                        <td style="width: 12%;">{{ formatCurrencyBr($airfare->taxa_embarque_unit) }}</td>
                        <td class="{{ $isAlt ? 'tot-lbl-alt' : 'tot-lbl' }}" style="width: 25%;">TOTAL NET COM TXS SEM TX 4BTS</td>
                        <td style="width: 15%;">{{ formatCurrencyBr($airfare->total_net_sem_4bts) }}</td>
                        <td class="{{ $isAlt ? 'tot-lbl-alt' : 'tot-lbl' }}" style="width: 18%;">RESULTADO BRUTO</td>
                        <td style="width: 15%; font-weight: bold;">{{ formatCurrencyBr($airfare->resultado_bruto) }}</td>
                    </tr>
                    <tr>
                        <td class="{{ $isAlt ? 'tot-lbl-alt' : 'tot-lbl' }}">TOTAL TX EMBARQUE</td>
                        <td>{{ formatCurrencyBr($airfare->total_taxa_embarque) }}</td>
                        <td class="{{ $isAlt ? 'tot-lbl-alt' : 'tot-lbl' }}">TOTAL VENDA COM TXS SEM TX 4BTS</td>
                        <td>{{ formatCurrencyBr($airfare->total_venda_sem_4bts) }}</td>
                        <td class="{{ $isAlt ? 'tot-lbl-alt' : 'tot-lbl' }}">TAXA DE SERVIÇOS 4BTS</td>
                        <td>{{ number_format($airfare->taxa_4bts ?: 10, 2, ',', '.') }}%</td>
                    </tr>
                    <tr>
                        <td class="{{ $isAlt ? 'tot-lbl-alt' : 'tot-lbl' }}">MOEDA / CÂMBIO (R$)</td>
                        <td>{{ $airfare->currency->sigla ?? 'REAL' }} ({{ number_format($airfare->exchange_rate_brl ?: 1, 2, ',', '.') }})</td>
                        <td class="{{ $isAlt ? 'tot-lbl-alt' : 'tot-lbl' }}">PRAZO PROPOSTA</td>
                        <td>{{ formatDateBr($airfare->prazo_proposta) }}</td>
                        <td class="{{ $isAlt ? 'tot-lbl-alt' : 'tot-lbl' }}">NOTES</td>
                        <td>{{ $airfare->notes ?: '-' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Exibição de Fotos (se cadastradas no primeiro ou neste frete) -->
            @if($airfare->photo_1 || $airfare->photo_2 || $airfare->photo_3 || $airfare->photo_4)
                <div class="photos-container">
                    <table class="photos-table">
                        <tr>
                            <td class="photo-cell">
                                <div class="photo-box">
                                    @if($airfare->photo_1) <img src="{{ getImgSrc($airfare->photo_1) }}" class="photo-img"> @endif
                                </div>
                            </td>
                            <td class="photo-cell">
                                <div class="photo-box">
                                    @if($airfare->photo_2) <img src="{{ getImgSrc($airfare->photo_2) }}" class="photo-img"> @endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="photo-cell">
                                <div class="photo-box">
                                    @if($airfare->photo_3) <img src="{{ getImgSrc($airfare->photo_3) }}" class="photo-img"> @endif
                                </div>
                            </td>
                            <td class="photo-cell">
                                <div class="photo-box">
                                    @if($airfare->photo_4) <img src="{{ getImgSrc($airfare->photo_4) }}" class="photo-img"> @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div class="disclaimer">* Imagens meramente ilustrativas</div>
                </div>
            @endif
        @endforeach
    @endif

    <!-- Seção de Notas Padrão -->
    <div class="notes-section">
        <div class="notes-header">Notas</div>
        <div class="notes-body">
            <p>Todas as ofertas estão sujeitas à aprovação final de conformidade [Risco], disponibilidade da aeronave e da tripulação no momento da confirmação da reserva, programação necessária, a companhia aérea obtendo slots de aeroporto, estacionamento e permissões nos aeroportos solicitados e todas as permissões e autorizações de rota.</p>
            <p>A menos que especificado acima, as ofertas são totalmente inclusivas da aeronave, taxas e encargos operacionais padrão. Excluídos, a menos que indicado acima, estão quaisquer taxas de manuseio fora do horário comercial e taxas de extensão, uso de telefone via satélite (se aplicável), desgelo da aeronave e / ou hangaragem, se necessário, custos excepcionais de catering e quaisquer impostos sobre carbono, ETS e similares cobrados pelo operador.</p>
            <p>Todas as ofertas estão sujeitas a sobretaxas de combustível [e flutuações cambiais] devido a variações de mercado. Os voos são operados de acordo com os termos e condições do nosso contrato padrão de sublocação. As fotos mostradas são apenas para fins de indicação, a aeronave real pode ser diferente.</p>
        </div>
    </div>

    <!-- Rodapé -->
    <div class="footer-bar">
        APENAS COTAÇÃO – NENHUM BLOQUEIO FOI REALIZADO
    </div>

</body>
</html>
