<?php

namespace App\Domains\Events\Services;

use App\Domains\Events\Repositories\EventApiRepositoryInterface;
use App\Http\Middleware\Constants;
use App\Models\Customer;
use Carbon\Carbon;
use DateTime;
use SimpleXMLElement;

class DefaultEventApiService implements EventApiServiceInterface
{
    protected EventApiRepositoryInterface $eventApiRepository;
    private array $clientesIncluidos = [];
    private array $fornecedoresIncluidos = [];

    public function __construct(EventApiRepositoryInterface $eventApiRepository)
    {
        $this->eventApiRepository = $eventApiRepository;
    }

    /**
     * Generate XML payload for events within the specified date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return string
     */
    public function generateXmlPayload(string $startDate, string $endDate): string
    {
        $start_date = Carbon::parse($startDate, 'America/Sao_Paulo')->startOfDay()->timezone('UTC');
        $end_date   = Carbon::parse($endDate, 'America/Sao_Paulo')->endOfDay()->timezone('UTC');

        $eventos = $this->eventApiRepository->getEventsForExport($start_date, $end_date) ?? [];

        $xml = new SimpleXMLElement('<intagi/>');
        $xml->addChild('versaolayout', 'V4.4');
        $clientes = $xml->addChild('clientes');
        $fornecedores = $xml->addChild('fornecedores');
        $vendas = $xml->addChild('vendas');

        foreach ($eventos as $evento) {
            // $this->clientesXML($evento->customer?->id, $clientes);

            $tipos = [
                [
                    'rel' => 'event_hotels',
                    'id' => 'hotel',
                    'tipoProvider' => 'hotel',
                    'fornecedor' => fn($item) => $item->hotel,
                    'model' => \App\Models\Provider::class,
                    'tipoctarec' => '01/0019',
                    'tipoctapag' => '01/0002',
                ],
                [
                    'rel' => 'event_abs',
                    'id' => 'ab',
                    'tipoProvider' => 'ab',
                    'fornecedor' => fn($item) => $item->ab,
                    'model' => \App\Models\Provider::class,
                    'tipoctarec' => fn($item) => $item->ab->national ? '04/0002' : '04/0001',
                    'tipoctapag' => fn($item) => $item->ab->national ? '24/0002' : '24/0001',
                ],
                [
                    'rel' => 'event_halls',
                    'id' => 'salao',
                    'tipoProvider' => 'hall',
                    'fornecedor' => fn($item) => $item->hall,
                    'model' => \App\Models\Provider::class,
                    'tipoctarec' => '01/0019',
                    'tipoctapag' => '01/0002',
                ],
                [
                    'rel' => 'event_transports',
                    'id' => 'transp',
                    'tipoProvider' => 'transport',
                    'fornecedor' => fn($item) => $item->transport,
                    'model' => \App\Models\ProviderTransport::class,
                    'tipoctarec' => '02/0016',
                    'tipoctapag' => '02/0013',
                ],
                [
                    'rel' => 'event_adds',
                    'id' => 'servicos',
                    'tipoProvider' => 'add',
                    'fornecedor' => fn($item) => $item->add,
                    'model' => \App\Models\ProviderServices::class,
                    'tipoctarec' => '01/0019',
                    'tipoctapag' => '01/0002',
                ],
                [
                    'rel' => 'event_airfares',
                    'id' => 'aereo',
                    'tipoProvider' => 'airline',
                    'fornecedor' => fn($item) => $item->airline ?: $item->provider,
                    'model' => \App\Models\AirfareAirline::class,
                    'tipoctarec' => '02/0016',
                    'tipoctapag' => '02/0013',
                ],
            ];

            foreach ($tipos as $tipo) {
                foreach ($evento->{$tipo['rel']} as $item) {
                    // $fornecedor = $tipo['fornecedor']($item);
                    // $this->fornecedoresXML($fornecedor->id, $fornecedores, $tipo['model']);
                    $this->vendasXML($evento, $item, $vendas, $tipo);
                }
            }
        }

        return $xml->asXML();
    }

    private function clientesXML($clienteId, $xml)
    {
        if ($clienteId === null) {
            return; // sem cliente neste evento
        }

        // Se cliente ainda não foi incluído
        if (!isset($this->clientesIncluidos[$clienteId])) {

            // Buscar os dados do cliente no banco
            $cliente = Customer::find($clienteId);

            if ($cliente) {

                // Adiciona cliente no XML
                $clienteXml = $xml->addChild('cliente');

                $clienteXml->addChild('idcliente', htmlspecialchars($cliente->codestur ?? $cliente->id));
                $clienteXml->addChild('cnpjcpf', htmlspecialchars($cliente->document));
                $clienteXml->addChild('razaonome', htmlspecialchars($cliente->name));
                $clienteXml->addChild('nomefantasia', htmlspecialchars($cliente->name));

                $documento = preg_replace('/\D/', '', $cliente->document);
                $tipoPessoa = strlen($documento) === 14 ? 'PJ' : 'PF';
                $clienteXml->addChild('tipopessoa', htmlspecialchars($tipoPessoa));

                if (!empty($cliente->email)) {
                    $clienteXml->addChild('email', htmlspecialchars($cliente->email));
                }

                if (!empty($cliente->phone)) {
                    $telefone = preg_replace('/\D/', '', $cliente->phone);
                    $clienteXml->addChild('telefone', htmlspecialchars($telefone));
                }

                // Marca cliente como incluído
                $this->clientesIncluidos[$clienteId] = true;
            }
        }
    }

    private function vendasXML($evento, $fornecedor, $xml, $tipo)
    {
        $venda = $xml->addChild('venda');
        $venda->addChild('origem', 'SMART4BTS');
        $venda->addChild('idvenda', htmlspecialchars($evento->id . '-' . $fornecedor->id . '-' . $tipo['id']));
        $venda->addChild('idvendapai', htmlspecialchars($evento->code));
        $tipoProduto = $tipo['rel'] == 'event_hotels' ? 'HOTEL' : ($tipo['rel'] == 'event_airfares' ? 'PASSAGEM' : 'DIVERSOS');
        $venda->addChild('tipoproduto', $tipoProduto);
        $providerObj = $tipo['fornecedor']($fornecedor);
        $national = $providerObj->national ?? true;

        switch ($tipo['rel']) {
            case 'event_hotels':
                $idProduto = $national ? 'HOTN' : 'HOTI';
                break;
            case 'event_abs':
                $idProduto = $national ? 'A&amp;BN' : 'A&amp;BI';
                break;
            case 'event_halls':
                $idProduto = $national ? 'SL' : 'LOC';
                break;
            case 'event_airfares':
                $idProduto = $national ? 'AERN' : 'AERI';
                break;
            default:
                $idProduto = $national ? 'DIVN' : 'DIVI';
                break;
        }
        $venda->addChild('idproduto', htmlspecialchars($idProduto));

        $venda->addChild('clasproduto', htmlspecialchars($national ? 'NACIONAL' : 'INTERNACIONAL'));

        if ($tipo['rel'] === 'event_airfares') {
            $idPromotor = $evento->air_operator ?? '';
            $idEmissor = $evento->airOperator?->codigo_stur ?? '';
        } elseif ($tipo['rel'] === 'event_transports') {
            $idPromotor = $evento->land_operator ?? '';
            $idEmissor = $evento->landOperator?->codigo_stur ?? '';
        } else {
            $idPromotor = $evento->hotel_operator ?? '';
            $idEmissor = $evento->hotelOperator?->codigo_stur ?? '';
        }

        $venda->addChild('idpromotor', htmlspecialchars((string)$idPromotor));
        $venda->addChild('idemissor', htmlspecialchars((string)$idEmissor));


        $venda->addChild('dtemissao', htmlspecialchars(Carbon::parse($evento->date)->format('d/m/Y')));
        $venda->addChild('idcliente', htmlspecialchars($evento->customer?->codestur ?? $evento->customer?->id ?? ''));
        $venda->addChild('idoperador', htmlspecialchars($fornecedor?->broker?->name ?? ''));
        $venda->addChild('idfornecedor', htmlspecialchars($providerObj->codestur ?? $providerObj->id ?? ''));
        $venda->addChild('formrec', '2');

        $venda->addChild('vencrec', htmlspecialchars(!empty($fornecedor->deadline_date) ? Carbon::parse($fornecedor->deadline_date)->format('d/m/Y') : Carbon::parse($evento->date)->format('d/m/Y')));
        $venda->addChild('centrocustocli', htmlspecialchars($evento->cost_center ?? ''));
        $venda->addChild('setorcli', htmlspecialchars($evento->sector ?? ''));
        $venda->addChild('filialagencia', htmlspecialchars($evento->crd?->number ?? ''));
        $venda->addChild('centrocusto', htmlspecialchars($evento->crd?->number ?? ''));

        $venda->addChild('solicitante', htmlspecialchars($evento->requester ?? ''));

        if (isset($fornecedor->status_his)) {
            $aprovado = collect($fornecedor->status_his)->last(function ($item) {
                return $item->status === 'approved_by_manager';
            });

            $venda->addChild('autorizadopor', htmlspecialchars($aprovado->user->name ?? ''));
            $venda->addChild('matriculaautorizador', htmlspecialchars($aprovado->id ?? ''));
            $dataAutorizacao = '';
            $horaAutorizacao = '';
            if (!empty($aprovado?->created_at)) {
                $carbon = Carbon::parse($aprovado->created_at);
                $dataAutorizacao = $carbon->format('d/m/Y');
                $horaAutorizacao = $carbon->format('H:i');
            }
            $venda->addChild('dataautorizacao', htmlspecialchars($dataAutorizacao));
            $venda->addChild('horaautorizacao', htmlspecialchars($horaAutorizacao));
        }

        $venda->addChild('projetocodigo', htmlspecialchars($evento->code ?? ''));
        $venda->addChild('projetonome', htmlspecialchars(mb_substr($evento->name ?? '', 0, 30)));

        $tipoCtarec = $tipo['tipoctarec'];
        $tipoCtapag = $tipo['tipoctapag'];

        // Se for uma função, executa. Se já for string, usa direto.
        if (is_callable($tipoCtarec)) {
            $tipoCtarec = $tipoCtarec($fornecedor);
        }
        if (is_callable($tipoCtapag)) {
            $tipoCtapag = $tipoCtapag($fornecedor);
        }

        // Garante que são strings antes de adicionar ao XML
        $venda->addChild('tipoctarec', htmlspecialchars((string) ($tipoCtarec ?? '')));
        $venda->addChild('tipoctapag', htmlspecialchars((string) ($tipoCtapag ?? '')));

        $venda->addChild('formpagto', 2); //FATURADO

        $movimentosXml = $venda->addChild('movimentos');

        $opts = $fornecedor->eventHotelsOpt 
            ?? $fornecedor->eventAbOpts 
            ?? $fornecedor->eventHallOpts 
            ?? $fornecedor->eventAddOpts 
            ?? $fornecedor->eventTransportOpts 
            ?? $fornecedor->eventAirfareOpts;

        if ($tipo['rel'] === 'event_airfares') {
            if (!$opts || (is_countable($opts) && count($opts) === 0)) {
                $opts = [$fornecedor];
            }
        } else {
            $opts = $opts ?? [];
        }

        foreach ($opts as $opt) {
            switch ($tipo['rel']) {
                case 'event_hotels':
                    $this->movimentoHotelariaXML($opt, $fornecedor, $evento, $movimentosXml);
                    break;
                case 'event_airfares':
                    $this->movimentoAereoXML($opt, $fornecedor, $evento, $movimentosXml);
                    break;
                default:
                    $this->movimentoDiversosXML($opt, $fornecedor, $evento, $movimentosXml, $tipo['rel']);
                    break;
            }
        }
    }

    private function movimentoDiversosXML($opt, $fornecedor, $evento, $movimentoXml, $tipo)
    {
        $movimento = $movimentoXml->addChild('diversos');
        $stringNome = '';
        switch ($tipo) {
            case 'event_transports':
                $movimento->addChild('descricao', 'TRANSPORTES');
                $stringNome = 'TRANSPORTES';
                break;
            case 'event_adds':
                $movimento->addChild('descricao', 'ADICIONAIS');
                $stringNome = 'ADICIONAIS';
                break;
            case 'event_abs':
                $movimento->addChild('descricao', 'ALIMENTOS E BEBIDAS');
                $stringNome = 'ALIMENTOS E BEBIDAS';
                break;
            case 'event_halls':
                $movimento->addChild('descricao', 'SALOES E EVENTOS');
                $stringNome = 'SALOES E EVENTOS';
                break;
            default:
                $movimento->addChild('descricao', htmlspecialchars($fornecedor->customer_observation  ?? ''));
                break;
        }

        $nomeCortado = mb_substr($evento->name ?? '', 0, 40 - mb_strlen($stringNome));
        $movimento->addChild('pax', htmlspecialchars($nomeCortado . ' - ' . $stringNome));
        $movimento->addChild('tipo', 'ADT');
        $movimento->addChild('matricula', '');
        $movimento->addChild('moeda', htmlspecialchars($fornecedor->currency?->sigla ?? ''));
        $movimento->addChild('cambio', htmlspecialchars($evento->exchange_rate ?? '1'));
        $movimento->addChild('cambiofornecedor', htmlspecialchars($opt->cambiofornecedor ?? ''));
        $in = Carbon::parse($opt->in);
        $out = Carbon::parse($opt->out);
        $movimento->addChild('checkin', htmlspecialchars($in->format('d/m/Y') ?? ''));
        $movimento->addChild('checkout', htmlspecialchars($out->format('d/m/Y') ?? ''));

        $qtdDayle = $opt->count * $this->daysBetween1($opt->in, $opt->out);

        $movimento->addChild('comisrecforvalor', htmlspecialchars(($opt->received_proposal * $qtdDayle * $opt->kickback) / 100 ?? ''));

        // $movimento->addChild('descpagclivalor', htmlspecialchars($opt->received_proposal * $qtdDayle ?? ''));
        $movimento->addChild('observacao', htmlspecialchars($fornecedor->internal_observation  ?? ''));

        $totais = $this->computeTotals($evento, $fornecedor, $opt);

        $movimento->addChild('taxaservico',  htmlspecialchars($totais['diaria_taxas'] * $qtdDayle ?? ''));
        $movimento->addChild('taxaservicofor', htmlspecialchars($totais['diaria_taxas'] * $qtdDayle ?? ''));

        $movimento->addChild('valordiaria', htmlspecialchars($totais['diaria'] ?? ''));
        $movimento->addChild('valordiariabalcao', htmlspecialchars($totais['diaria'] ?? ''));
        $movimento->addChild('valordiariafornecedor', htmlspecialchars($totais['diaria_fornecedor'] ?? ''));
        $movimento->addChild('qtdservico', htmlspecialchars($qtdDayle > 0 ? $qtdDayle : '1'));

        if (isset($fornecedor->status_his)) {
            $movimento->addChild('confirmadopor', '');
            $movimento->addChild('dataconfirmacao', '');
            $movimento->addChild('numconfirmacao', '');
        }
    }

    private function movimentoHotelariaXML($opt, $fornecedor, $evento, $movimentoXml)
    {
        $movimento = $movimentoXml->addChild('hotelaria');
        $movimento->addChild('pax', htmlspecialchars(mb_substr($evento->name ?? '', 0, 40)));
        $movimento->addChild('tipo', 'ADT');
        $movimento->addChild('motivoviagem', '');
        $movimento->addChild('moeda', htmlspecialchars($fornecedor->currency?->sigla ?? ''));
        $movimento->addChild('cambio', htmlspecialchars($evento->exchange_rate ?? '1'));
        $in = Carbon::parse($opt->in);
        $out = Carbon::parse($opt->out);
        $movimento->addChild('checkin', htmlspecialchars($in->format('d/m/Y') ?? ''));
        $movimento->addChild('checkout', htmlspecialchars($out->format('d/m/Y') ?? ''));

        $qtdDayle = $opt->count * $this->daysBetween($opt->in, $opt->out);
        $movimento->addChild('comisrecforvalor', htmlspecialchars(($opt->received_proposal * $qtdDayle * $opt->kickback) / 100 ?? ''));
        // $movimento->addChild('descpagclivalor', htmlspecialchars($opt->received_proposal * $qtdDayle ?? ''));
        $movimento->addChild('observacao', htmlspecialchars($fornecedor->internal_observation  ?? ''));
        $movimento->addChild('observacao2', htmlspecialchars($fornecedor->customer_observation  ?? ''));
        $movimento->addChild('categapto', htmlspecialchars($opt->category_hotel?->name ?? ''));

        $movimento->addChild('regime', htmlspecialchars($opt->regime?->name ?? ''));

        // Aptos
        $aptos = $movimento->addChild('aptos');

        $aptoXml = $aptos->addChild('apto');

        $tipoApto = strtoupper($opt->apto_hotel?->name ?? '');
        $tiposReconhecidos = ['SGL', 'DBL', 'TPL', 'QPL'];

        if ($tipoApto === 'TWIN') {
            $tipoApto = 'DBL';
        } elseif (!in_array($tipoApto, $tiposReconhecidos)) {
            $tipoApto = 'DBL'; // ou escolha um padrão, se preferir
        }

        $aptoXml->addChild('tipoapto', htmlspecialchars($tipoApto));

        $totais = $this->computeTotals($evento, $fornecedor, $opt);

        $movimento->addChild('taxaservico', htmlspecialchars($totais['diaria_taxas'] * $qtdDayle ?? ''));

        $aptoXml->addChild('valordiaria', htmlspecialchars($totais['diaria'] ?? ''));
        $aptoXml->addChild('valordiariabalcao', htmlspecialchars($totais['diaria'] ?? ''));
        $aptoXml->addChild('valordiariafornecedor', htmlspecialchars($totais['diaria_fornecedor'] ?? ''));
        $aptoXml->addChild('qtddiaria', htmlspecialchars($qtdDayle ?? ''));

        if (isset($fornecedor->status_his)) {

            $movimento->addChild('confirmadopor', '');
            $movimento->addChild('dataconfirmacao', '');
            $movimento->addChild('numconfirmacao', '');
        }
    }

    private function movimentoAereoXML($opt, $fornecedor, $evento, $movimentoXml)
    {
        $movimento = $movimentoXml->addChild('passagem');
        $movimento->addChild('pax', htmlspecialchars(mb_substr($evento->name ?? '', 0, 40)));
        $movimento->addChild('tipo', 'ADT');
        $movimento->addChild('moeda', htmlspecialchars($fornecedor->currency?->sigla ?? 'BRL'));
        $movimento->addChild('cambio', htmlspecialchars((string)($evento->exchange_rate ?? '1')));

        $ciaCode = $opt->outbound_airline?->code 
            ?? $fornecedor->airline?->code 
            ?? $fornecedor->airline?->name 
            ?? '';
        $movimento->addChild('cia', htmlspecialchars(mb_substr($ciaCode, 0, 10)));

        $flightNo = $opt->outbound_flight_number ?? '';
        $movimento->addChild('voo', htmlspecialchars(mb_substr($flightNo, 0, 10)));

        $origem = $opt->outbound_origin ?? '';
        $movimento->addChild('origem', htmlspecialchars(mb_substr($origem, 0, 5)));

        $destino = $opt->outbound_destination ?? '';
        $movimento->addChild('destino', htmlspecialchars(mb_substr($destino, 0, 5)));

        $dtSaida = !empty($opt->outbound_date)
            ? Carbon::parse($opt->outbound_date)->format('d/m/Y')
            : (!empty($evento->date) ? Carbon::parse($evento->date)->format('d/m/Y') : '');
        $movimento->addChild('dtsaida', htmlspecialchars($dtSaida));

        $totais = $this->computeTotalsAirfare($evento, $fornecedor, $opt);

        $movimento->addChild('tarifa', htmlspecialchars((string)$totais['tarifa']));
        $movimento->addChild('tarifabalcao', htmlspecialchars((string)$totais['tarifa']));
        $movimento->addChild('tarifafornecedor', htmlspecialchars((string)$totais['tarifa_fornecedor']));
        $movimento->addChild('taxaembarque', htmlspecialchars((string)$totais['taxa_embarque']));
        $movimento->addChild('observacao', htmlspecialchars($fornecedor->internal_observation ?? ''));
        $movimento->addChild('observacao2', htmlspecialchars($fornecedor->customer_observation ?? ''));

        if (isset($fornecedor->status_his)) {
            $movimento->addChild('confirmadopor', '');
            $movimento->addChild('dataconfirmacao', '');
            $movimento->addChild('numconfirmacao', '');
        }
    }

    private function computeTotalsAirfare($evento, $fornecedor, $opt): array
    {
        $cost = floatval($opt->received_proposal ?? $fornecedor->total_net_sem_4bts ?? 0);
        $taxaEmbarque = floatval($fornecedor->taxa_embarque_unit ?? 0);

        $taxes = $this->sumTaxesProvider($fornecedor, $opt);
        $taxesCost = $this->sumTaxesProviderCost($fornecedor, $opt);

        $saleBase = $this->unitSaleAirfare($fornecedor, $opt);

        $totalCost = $cost + $taxesCost;
        $totalSale = $saleBase + $taxes;

        $iofs = [];
        if (isset($fornecedor->iof) && $fornecedor->iof > 0) $iofs[] = $fornecedor->iof;
        if (isset($evento->iof) && $evento->iof > 0) $iofs[] = $evento->iof;
        $percIOF = count($iofs) ? max($iofs) : 0;

        $totalSaleIOF = ((($totalSale * $percIOF) / 100) + $totalSale);
        $totalCostIOF = ((($totalCost * $percIOF) / 100) + $totalCost);

        return [
            'tarifa_fornecedor' => round($totalCostIOF, 2),
            'tarifa' => round($totalSaleIOF, 2),
            'taxa_embarque' => round($taxaEmbarque, 2),
        ];
    }

    private function unitSaleAirfare($fornecedor, $opt)
    {
        $cost = floatval($opt->received_proposal ?? $fornecedor->total_net_sem_4bts ?? 0);
        if (isset($fornecedor->markup) && floatval($fornecedor->markup) > 0) {
            return $cost * (1 + (floatval($fornecedor->markup) / 100));
        }
        $percent = floatval($opt->received_proposal_percent ?? 0);
        if ($percent > 0) {
            $factor = $percent > 2 ? $percent / 100 : $percent;
            return ceil($cost / $factor);
        }
        if (isset($fornecedor->taxa_4bts) && floatval($fornecedor->taxa_4bts) > 0) {
            return $cost * (1 + (floatval($fornecedor->taxa_4bts) / 100));
        }
        return $cost;
    }

    private function fornecedoresXML($fornecedorId, $xml, $modelClass = \App\Models\Provider::class)
    {
        if ($fornecedorId === null) {
            return; // sem fornecedor neste item
        }

        if (!isset($this->fornecedoresIncluidos[$fornecedorId])) {
            // Buscar o fornecedor no model informado
            $fornecedor = $modelClass::with('city')->find($fornecedorId);

            if ($fornecedor) {
                $fornecedorXml = $xml->addChild('fornecedor');
                $fornecedorXml->addChild('idfornecedor', htmlspecialchars($fornecedor->codestur ?? $fornecedor->id));
                $fornecedorXml->addChild('razaonome', htmlspecialchars($fornecedor->name ?? ''));
                $fornecedorXml->addChild('nomefantasia', htmlspecialchars($fornecedor->fantasy_name ?? $fornecedor->name ?? ''));

                $fornecedorXml->addChild('tipopessoa', 'PJ');
                if ($modelClass === \App\Models\ProviderTransport::class) {
                    $tipoFornec = 'TRANSP';
                } elseif ($modelClass === \App\Models\ProviderServices::class) {
                    $tipoFornec = 'DIV';
                } else {
                    $tipoFornec = 'HOT';
                }
                $fornecedorXml->addChild('tipofornec', htmlspecialchars($tipoFornec));


                if (!empty($fornecedor->email)) {
                    $fornecedorXml->addChild('email', htmlspecialchars($fornecedor->email));
                }
                if (!empty($fornecedor->phone)) {
                    $telefone = preg_replace('/\D/', '', $fornecedor->phone);
                    $fornecedorXml->addChild('telefone', htmlspecialchars($telefone));
                }

                if ($fornecedor->city) {
                    $fornecedorXml->addChild('cidade', htmlspecialchars(mb_substr($fornecedor->city->name ?? '', 0, 20)));
                    $fornecedorXml->addChild('pais', htmlspecialchars(mb_substr($fornecedor->city->country ?? '', 0, 20)));
                    $fornecedorXml->addChild('estadosigla', htmlspecialchars(mb_substr($fornecedor->city->states ?? '', 0, 2)));
                    $estadoNome = '';
                    foreach (Constants::UFS as $estado) {
                        if ($estado['uf'] === ($fornecedor->city->states ?? '')) {
                            $estadoNome = $estado['name'];
                            break;
                        }
                    }
                    $fornecedorXml->addChild('estadonome', htmlspecialchars(mb_substr($estadoNome, 0, 20)));
                } else {
                    $fornecedorXml->addChild('cidade', '');
                    $fornecedorXml->addChild('pais', '');
                    $fornecedorXml->addChild('estadosigla', '');
                    $fornecedorXml->addChild('estadonome', '');
                }

                $this->fornecedoresIncluidos[$fornecedorId] = true;
            }
        }
    }

    private function unitSale($opt)
    {
        $percent = floatval($opt->received_proposal_percent);
        if ($percent == 0) {
            return $opt->received_proposal;
        }

        $factor = $percent > 2 ? $percent / 100 : $percent;
        return ceil($opt->received_proposal / $factor);
    }

    private function sumTotal($rate, $taxes, $qtdDayle)
    {
        return (($rate + $taxes) * $qtdDayle);
    }

    private function sumTaxesProvider($eventP, $opt)
    {
        return (($this->unitSale($opt) * $eventP->iss_percent) / 100)
            + (($this->unitSale($opt) * $eventP->service_percent) / 100)
            + (($this->unitSale($opt) * $eventP->iva_percent) / 100)
            + (($this->unitSale($opt) * $eventP->service_charge) / 100);
    }

    private function daysBetween($date1, $date2)
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
        $days = ceil($interval->days);
        return $days == 0 ? 1 : $days;
    }
    
    private function daysBetween1($date1, $date2)
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
        $days = ceil($interval->days) + 1;
        return $days == 0 ? 1 : $days;
    }

    /**
     * Calcula totais (custo/venda) para um fornecedor em um evento.
     *
     */
    private function computeTotals($evento, $fornecedor, $item): array
    {
        $sumHotelSale = 0; // soma das vendas (base)
        $sumTotalHotelCost = 0; // custo + taxas (por item)
        $sumTotalHotelSale = 0; // venda + taxas (por item)

        // taxes sobre venda (usa unidade de venda)
        $taxes = $this->sumTaxesProvider($fornecedor, $item);

        // taxes sobre custo (usa valor recebido/fornecedor)
        $taxesCost = $this->sumTaxesProviderCost($fornecedor, $item);

        $sumHotelSale += $this->unitSale($item);

        // soma total por item (base + taxas)
        $sumTotalHotelCost += $this->sumTotal($item->received_proposal ?? 0, $taxesCost, 1);
        $sumTotalHotelSale += $this->sumTotal($this->unitSale($item), $taxes, 1);

        // determina IOF: pega o maior IOF disponível entre fornecedor e evento (se existirem)
        $iofs = [];
        if (isset($fornecedor->iof) && $fornecedor->iof > 0) $iofs[] = $fornecedor->iof;
        if (isset($evento->iof) && $evento->iof > 0) $iofs[] = $evento->iof;
        $percIOF = count($iofs) ? max($iofs) : 0;

        // aplica IOF e taxa 4bts (taxa 4bts só para venda)
        $sumTotalHotelSaleTaxasSemTaxa4bts = ((($sumTotalHotelSale * $percIOF) / 100) + $sumTotalHotelSale);
        $sumTotalHotelSaleTaxa4bts = $sumTotalHotelSaleTaxasSemTaxa4bts * ($fornecedor->taxa_4bts / 100);
        $sumTotalHotelCostTaxa = ((($sumTotalHotelCost * $percIOF) / 100) + $sumTotalHotelCost);

        return [
            'diaria_fornecedor' => round($sumTotalHotelCostTaxa, 2),
            'diaria' => round($sumTotalHotelSaleTaxasSemTaxa4bts, 2),
            'diaria_taxas' => round($sumTotalHotelSaleTaxa4bts, 2),
        ];
    }

    /**
     * Taxes calculadas sobre custos (usa received_proposal como base)
     */
    private function sumTaxesProviderCost($eventP, $opt)
    {
        return (($opt->received_proposal ?? 0) * ($eventP->iss_percent ?? 0) / 100)
            + (($opt->received_proposal ?? 0) * ($eventP->service_percent ?? 0) / 100)
            + (($opt->received_proposal ?? 0) * ($eventP->iva_percent ?? 0) / 100)
            + (($opt->received_proposal ?? 0) * ($eventP->service_charge ?? 0) / 100);
    }
}
