<?php

namespace App\Http\Controllers\Api;

use App\Http\Middleware\Constants;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class EventApiController extends BaseApiController
{
    private  $clientesIncluidos = [];
    private $fornecedoresIncluidos = [];

    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="Consulta eventos em XML",
     *     description="Retorna um XML com os eventos cadastrados no sistema, filtrando por data inicial e final.",
     *     tags={"Eventos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         description="Data inicial no formato YYYY-MM-DD",
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         description="Data final no formato YYYY-MM-DD",
     *         @OA\Schema(type="string", format="date", example="2025-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="XML com os eventos",
     *         @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(type="string", format="xml")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *     )
     * )
     */
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $start_date = Carbon::parse($request->start_date, 'America/Sao_Paulo')->startOfDay()->timezone('UTC');
        $end_date   = Carbon::parse($request->end_date, 'America/Sao_Paulo')->endOfDay()->timezone('UTC');

        $eventos = $this->exportarXmlModeloOficial($start_date, $end_date) ?? [];

        $xml = new SimpleXMLElement('<intagi/>');
        $xml->addChild('versaolayout', 'V4.4');
        $clientes = $xml->addChild('clientes');
        $fornecedores = $xml->addChild('fornecedores');
        $vendas = $xml->addChild('vendas');

        foreach ($eventos as $evento) {
            $this->clientesXML($evento->customer?->id, $clientes);

            $tipos = [
                [
                    'rel' => 'event_hotels',
                    'id' => 'hotel',
                    'tipoProvider' => 'hotel',
                    'fornecedor' => fn($item) => $item->hotel,
                    'model' => \App\Models\Provider::class,
                    'tipoctarec' => '01/0002',
                ],
                [
                    'rel' => 'event_abs',
                    'id' => 'ab',
                    'tipoProvider' => 'ab',
                    'fornecedor' => fn($item) => $item->ab,
                    'model' => \App\Models\Provider::class,
                    'tipoctarec' => '01/0005',
                ],
                [
                    'rel' => 'event_halls',
                    'id' => 'salao',
                    'tipoProvider' => 'hall',
                    'fornecedor' => fn($item) => $item->hall,
                    'model' => \App\Models\Provider::class,
                    'tipoctarec' => '01/0003',
                ],
                [
                    'rel' => 'event_transports',
                    'id' => 'transp',
                    'tipoProvider' => 'transport',
                    'fornecedor' => fn($item) => $item->transport,
                    'model' => \App\Models\ProviderTransport::class,
                    'tipoctarec' => '01/0006',
                ],
                [
                    'rel' => 'event_adds',
                    'id' => 'servicos',
                    'tipoProvider' => 'add',
                    'fornecedor' => fn($item) => $item->add,
                    'model' => \App\Models\ProviderServices::class,
                    'tipoctarec' => '01/0005',
                ],
            ];

            foreach ($tipos as $tipo) {
                foreach ($evento->{$tipo['rel']} as $item) {
                    $fornecedor = $tipo['fornecedor']($item);
                    $this->fornecedoresXML($fornecedor->id, $fornecedores, $tipo['model']);
                    $this->vendasXML($evento, $item, $vendas, $tipo);
                }
            }
        }

        return response($xml->asXML(), 200)
            ->header('Content-Type', 'application/xml');
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
                $clienteXml->addChild('tipopessoa', $tipoPessoa);


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
        $venda->addChild('tipoproduto', $tipo['rel'] == 'event_hotels' ? 'HOTEL' : 'DIVERSOS');
        $venda->addChild('idproduto', $tipo['rel'] == 'event_hotels' ? 'HTLI' : 'DIVN');

        $venda->addChild('clasproduto', htmlspecialchars($fornecedor->{$tipo['tipoProvider']}->national ? 'Nacional' : 'Internacional'));

        $venda->addChild('idemissor', htmlspecialchars($evento->user_created ?? ''));
        $venda->addChild(
            'idpromotor',
            htmlspecialchars(
                $fornecedor->table == 'event_transport'
                    ? $evento->land_operator
                    : ($evento->hotel_operator ?? '')
            )
        );
        $venda->addChild('dtemissao', htmlspecialchars(Carbon::parse($evento->date)->format('d/m/Y')));
        $venda->addChild('idcliente', htmlspecialchars($evento->customer?->codestur ?? $evento->customer?->id ?? ''));
        $venda->addChild('idoperador', htmlspecialchars($fornecedor?->broker?->name ?? ''));
        $venda->addChild('idfornecedor', htmlspecialchars($tipo['fornecedor']($fornecedor)->codestur ?? $tipo['fornecedor']($fornecedor)->id ?? ''));
        $venda->addChild('formrec', '1');

        $venda->addChild('vencrec', htmlspecialchars(Carbon::parse($fornecedor->deadline_date)->format('d/m/Y')));
        $venda->addChild('centrocustocli', htmlspecialchars(($evento->cost_center ?? '') . ' - ' . ($evento->crd?->name ?? '')));
        $venda->addChild('setorcli', htmlspecialchars($evento->sector ?? ''));
        $venda->addChild('filialagencia', '');
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

        $venda->addChild('tipoctarec', htmlspecialchars($tipo['tipoctarec'] ?? ''));
        $venda->addChild('tipoctapag', htmlspecialchars($tipo['tipoctarec'] ?? ''));

        $venda->addChild('formpagto', 2); //FATURADO


        $sumTotalHotelSale = 0;
        $movimentosXml = $venda->addChild('movimentos');

        foreach ($fornecedor->eventHotelsOpt ?? $fornecedor->eventAbOpts ?? $fornecedor->eventHallOpts ?? $fornecedor->eventAddOpts ?? $fornecedor->eventTransportOpts ?? [] as $opt) {

            switch ($tipo['rel']) {
                case 'event_hotels':
                    $this->movimentoHotelariaXML($opt, $fornecedor, $evento, $movimentosXml);
                    break;
                default:
                    $this->movimentoDiversosXML($opt, $fornecedor, $evento, $movimentosXml, $tipo['rel']);
                    break;
            }

            // $taxes = $this->sumTaxesProvider($fornecedor, $opt);
            // if ($tipo['rel'] == 'event_hotels') {
            //     $qtdDayle = $opt->count * $this->daysBetween($opt->in, $opt->out);
            // } else {
            //     $qtdDayle = $opt->count * $this->daysBetween1($opt->in, $opt->out);
            // }
            // $sumTotalHotelSale += $this->sumTotal($this->unitSale($opt), $taxes, $qtdDayle);
        }
        // $venda->addChild('entradatotal', $sumTotalHotelSale);
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
        $movimento->addChild('pax', htmlspecialchars($nomeCortado) . ' - ' . $stringNome);
        $movimento->addChild('tipo', 'ADT');
        $movimento->addChild('matricula', '');
        $movimento->addChild('moeda', htmlspecialchars($fornecedor->currency?->sigla ?? ''));
        $movimento->addChild('cambio', htmlspecialchars($evento->exchange_rate ?? '1'));
        $movimento->addChild('cambiofornecedor', htmlspecialchars($opt->cambiofornecedor ?? ''));
        $in = Carbon::parse($opt->in);
        $out = Carbon::parse($opt->out);
        $movimento->addChild('checkin', htmlspecialchars($in->format('d/m/Y') ?? ''));
        $movimento->addChild('checkout', htmlspecialchars($out->format('d/m/Y') ?? ''));
        $movimento->addChild('taxaservico', htmlspecialchars(($opt->received_proposal * $fornecedor->service_percent) / 100 ?? ''));
        $movimento->addChild('taxaservicofor', htmlspecialchars(($this->unitSale($opt) * $fornecedor->service_percent) ?? ''));

        $qtdDayle = $opt->count * $this->daysBetween($opt->in, $opt->out);

        $movimento->addChild('comisrecforvalor', htmlspecialchars(($opt->received_proposal * $qtdDayle * $opt->kickback) / 100 ?? ''));

        // $movimento->addChild('descpagclivalor', htmlspecialchars($opt->received_proposal * $qtdDayle ?? ''));
        $movimento->addChild('observacao', htmlspecialchars($fornecedor->internal_observation  ?? ''));

        $movimento->addChild('valordiaria', htmlspecialchars($opt->received_proposal ?? ''));
        $movimento->addChild('valordiariafornecedor', htmlspecialchars($this->unitSale($opt) ?? ''));
        $movimento->addChild('qtdservico', htmlspecialchars($qtdDayle > 0 ? $qtdDayle : '1'));

        if (isset($fornecedor->status_his)) {
            // $aprovado = collect($fornecedor->status_his)->last(function ($item) {
            //     return $item->status === 'approved_by_manager';
            // });

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

        $movimento->addChild('taxaservico', htmlspecialchars(($opt->received_proposal * $fornecedor->service_percent) / 100 ?? ''));

        $qtdDayle = $opt->count * $this->daysBetween($opt->in, $opt->out);
        $movimento->addChild('comisrecforvalor', htmlspecialchars(($opt->received_proposal * $qtdDayle * $opt->kickback) / 100 ?? ''));
        $movimento->addChild('descpagclivalor', htmlspecialchars($opt->received_proposal * $qtdDayle ?? ''));
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

        $aptoXml->addChild('valordiaria', htmlspecialchars($opt->received_proposal ?? ''));
        $aptoXml->addChild('valordiariafornecedor', htmlspecialchars($this->unitSale($opt) ?? ''));
        $aptoXml->addChild('qtddiaria', htmlspecialchars($qtdDayle ?? ''));

        if (isset($fornecedor->status_his)) {
            // $aprovado = collect($fornecedor->status_his)->last(function ($item) {
            //     return $item->status === 'approved_by_manager';
            // });

            $movimento->addChild('confirmadopor', '');
            $movimento->addChild('dataconfirmacao', '');
            $movimento->addChild('numconfirmacao', '');
        }
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
                $fornecedorXml->addChild('tipofornec', $tipoFornec);


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

    private function exportarXmlModeloOficial($start_date, $end_date)
    {
        $eventos = Event::with([
            'customer',
            'crd',

            // Hotéis
            'event_hotels.hotel',
            'event_hotels.eventHotelsOpt' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_hotels.eventHotelsOpt.regime',
            'event_hotels.eventHotelsOpt.apto_hotel',
            'event_hotels.eventHotelsOpt.category_hotel',
            'event_hotels.currency',
            'event_hotels.status_his.user',

            // ABs
            'event_abs.ab',
            'event_abs.eventAbOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_abs.eventAbOpts.Local',
            'event_abs.eventAbOpts.service_type',
            'event_abs.currency',
            'event_abs.status_his.user',

            // Halls
            'event_halls.hall',
            'event_halls.eventHallOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_halls.eventHallOpts.purpose',
            'event_halls.currency',
            'event_halls.status_his.user',

            // Adicionais
            'event_adds.add',
            'event_adds.eventAddOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_adds.eventAddOpts.measure',
            'event_adds.eventAddOpts.service',
            'event_adds.currency',
            'event_adds.status_his.user',

            // Transportes
            'event_transports.transport',
            'event_transports.eventTransportOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_transports.eventTransportOpts.brand',
            'event_transports.eventTransportOpts.vehicle',
            'event_transports.eventTransportOpts.model',
            'event_transports.currency',
            'event_transports.status_his.user',
        ])
            ->where(function ($query) use ($start_date, $end_date) {
                $query->whereHas('event_hotels', function ($q) use ($start_date, $end_date) {
                    $q->whereExists(function ($sub) use ($start_date, $end_date) {
                        $sub->select(DB::raw(1))
                            ->from('status_history')
                            ->whereColumn('status_history.table_id', 'event_hotel.id')
                            ->where('status_history.table', 'event_hotels')
                            ->where('status_history.status', 'dating_with_customer')
                            ->whereBetween('status_history.created_at', [$start_date, $end_date]);
                    });
                })->orWhereHas('event_abs', function ($q) use ($start_date, $end_date) {
                    $q->whereExists(function ($sub) use ($start_date, $end_date) {
                        $sub->select(DB::raw(1))
                            ->from('status_history')
                            ->whereColumn('status_history.table_id', 'event_ab.id')
                            ->where('status_history.table', 'event_abs')
                            ->where('status_history.status', 'dating_with_customer')
                            ->whereBetween('status_history.created_at', [$start_date, $end_date]);
                    });
                })->orWhereHas('event_halls', function ($q) use ($start_date, $end_date) {
                    $q->whereExists(function ($sub) use ($start_date, $end_date) {
                        $sub->select(DB::raw(1))
                            ->from('status_history')
                            ->whereColumn('status_history.table_id', 'event_hall.id')
                            ->where('status_history.table', 'event_halls')
                            ->where('status_history.status', 'dating_with_customer')
                            ->whereBetween('status_history.created_at', [$start_date, $end_date]);
                    });
                })->orWhereHas('event_adds', function ($q) use ($start_date, $end_date) {
                    $q->whereExists(function ($sub) use ($start_date, $end_date) {
                        $sub->select(DB::raw(1))
                            ->from('status_history')
                            ->whereColumn('status_history.table_id', 'event_add.id')
                            ->whereIn('status_history.table', ['event_adds', 'EventAdds'])
                            ->where('status_history.status', 'dating_with_customer')
                            ->whereBetween('status_history.created_at', [$start_date, $end_date]);
                    });
                })->orWhereHas('event_transports', function ($q) use ($start_date, $end_date) {
                    $q->whereExists(function ($sub) use ($start_date, $end_date) {
                        $sub->select(DB::raw(1))
                            ->from('status_history')
                            ->whereColumn('status_history.table_id', 'event_transport.id')
                            ->where('status_history.table', 'event_transports')
                            ->where('status_history.status', 'dating_with_customer')
                            ->whereBetween('status_history.created_at', [$start_date, $end_date]);
                    });
                });
            })
            ->get();

        // Filtrar os itens de cada evento conforme a data
        foreach ($eventos as $evento) {
            // Filtra event_hotels
            if (isset($evento->event_hotels)) {
                $evento->event_hotels = $evento->event_hotels->filter(function ($item) use ($start_date, $end_date) {
                    return $item->status_his->contains(function ($status) use ($start_date, $end_date) {
                        return $status->status === 'dating_with_customer'
                            && $status->created_at >= $start_date
                            && $status->created_at <= $end_date;
                    });
                })->values();
            }
            // Filtra event_abs
            if (isset($evento->event_abs)) {
                $evento->event_abs = collect($evento->event_abs)->filter(function ($item) use ($start_date, $end_date) {
                    return isset($item->status_his) && collect($item->status_his)->contains(function ($status) use ($start_date, $end_date) {
                        return $status->status === 'dating_with_customer'
                            && $status->created_at >= $start_date
                            && $status->created_at <= $end_date;
                    });
                })->values();
            }
            // Repita para event_halls, event_adds, event_transports...
            if (isset($evento->event_halls)) {
                $evento->event_halls = $evento->event_halls->filter(function ($item) use ($start_date, $end_date) {
                    return $item->status_his->contains(function ($status) use ($start_date, $end_date) {
                        return $status->status === 'dating_with_customer'
                            && $status->created_at >= $start_date
                            && $status->created_at <= $end_date;
                    });
                })->values();
            }
            if (isset($evento->event_adds)) {
                $evento->event_adds = $evento->event_adds->filter(function ($item) use ($start_date, $end_date) {
                    return $item->status_his->contains(function ($status) use ($start_date, $end_date) {
                        return $status->status === 'dating_with_customer'
                            && $status->created_at >= $start_date
                            && $status->created_at <= $end_date;
                    });
                })->values();
            }
            if (isset($evento->event_transports)) {
                $evento->event_transports = $evento->event_transports->filter(function ($item) use ($start_date, $end_date) {
                    return $item->status_his->contains(function ($status) use ($start_date, $end_date) {
                        return $status->status === 'dating_with_customer'
                            && $status->created_at >= $start_date
                            && $status->created_at <= $end_date;
                    });
                })->values();
            }
        }

        return $eventos;
    }

    private function unitSale($opt)
    {
        if ($opt->received_proposal_percent == 0) {
            return $opt->received_proposal;
        }

        return ceil($opt->received_proposal / $opt->received_proposal_percent);
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
        return ceil($interval->days);
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
        return ceil($interval->days) + 1;
    }
}
