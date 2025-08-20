<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Auth\ProviderController;
use App\Http\Middleware\Constants;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventABOpt;
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

        $eventos = $this->exportarXmlModeloOficial($request->start_date, $request->end_date) ?? [];

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
                    'fornecedor' => fn($item) => $item->hotel,
                    'model' => \App\Models\Provider::class,
                    'tipoctarec' => '01/0022',
                    'tipoctapag' => '01/0005',
                ],
                [
                    'rel' => 'event_abs',
                    'fornecedor' => fn($item) => $item->ab,
                    'model' => \App\Models\Provider::class,
                    'tipoctarec' => '01/0022',
                    'tipoctapag' => '01/0005',
                ],
                [
                    'rel' => 'event_halls',
                    'fornecedor' => fn($item) => $item->hall,
                    'model' => \App\Models\Provider::class,
                    'tipoctarec' => '01/0022',
                    'tipoctapag' => '01/0005',
                ],
                [
                    'rel' => 'event_transports',
                    'fornecedor' => fn($item) => $item->transport,
                    'model' => \App\Models\ProviderTransport::class,
                    'tipoctarec' => '01/0022',
                    'tipoctapag' => '01/0005',
                ],
                [
                    'rel' => 'event_adds',
                    'fornecedor' => fn($item) => $item->add,
                    'model' => \App\Models\ProviderServices::class,
                    'tipoctarec' => '01/0022',
                    'tipoctapag' => '01/0005',
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

                $clienteXml->addChild('idcliente', htmlspecialchars($cliente->id));
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
        $venda->addChild('idvenda', htmlspecialchars($evento->id . '-' . $fornecedor->id . '-' . $fornecedor->table));
        $venda->addChild('idvendapai', htmlspecialchars($evento->code));
        $venda->addChild(
            'tipoproduto',
            $fornecedor->table == 'event_transport'
                ? 'LOCACAO'
                : ($fornecedor->table == 'event_hotel' ? 'HOTEL' : 'DIVERSOS')
        );
        $venda->addChild(
            'idproduto',
            $fornecedor->table == 'event_transport'
                ? 'loca'
                : ($fornecedor->table == 'event_hotel' ? 'HTLI' : 'DIVN')
        );
        $venda->addChild('clasproduto', htmlspecialchars($fornecedor->national ? 'Internacional' : 'Nacional'));

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
        $venda->addChild('idcliente', htmlspecialchars($evento->customer?->id ?? ''));
        $venda->addChild('idoperador', htmlspecialchars($fornecedor->id ?? ''));
        $venda->addChild('idfornecedor', htmlspecialchars($fornecedor->id ?? ''));

        $venda->addChild('vencrec', htmlspecialchars(Carbon::parse($fornecedor->deadline_date)->format('d/m/Y')));
        $venda->addChild('centrocustocli', htmlspecialchars($evento->cost_center ?? ''));
        $venda->addChild('setorcli', htmlspecialchars($evento->sector ?? ''));
        $venda->addChild('filialagencia', '');
        $venda->addChild('centrocusto', htmlspecialchars($evento->crd?->number ?? ''));

        $venda->addChild('solicitante', htmlspecialchars($evento->requester ?? ''));

        if (isset($fornecedor->status_his)) {
            $aprovado = collect($fornecedor->status_his)->last(function ($item) {
                return $item->status === 'approved_by_manager';
            });
            $user = User::find($aprovado->user_id ?? null);

            $venda->addChild('autorizadopor', htmlspecialchars($user->name ?? ''));
            $venda->addChild('matriculaautorizador', htmlspecialchars($user->id ?? ''));
            $dataAutorizacao = '';
            $horaAutorizacao = '';
            if (!empty($aprovado?->created_at)) {
                $carbon = Carbon::parse($aprovado->created_at);
                $dataAutorizacao = $carbon->format('d/m/Y');
                $horaAutorizacao = $carbon->format('H:i:s');
            }
            $venda->addChild('dataautorizacao', htmlspecialchars($dataAutorizacao));
            $venda->addChild('horaautorizacao', htmlspecialchars($horaAutorizacao));
        }

        $venda->addChild('projetocodigo', htmlspecialchars($evento->code ?? ''));
        $venda->addChild('projetonome', htmlspecialchars($evento->name ?? ''));

        $venda->addChild('tipoctarec', htmlspecialchars($tipo['tipoctarec'] ?? ''));
        $venda->addChild('tipoctapag', htmlspecialchars($tipo['tipoctapag'] ?? ''));

        $sumTotalHotelSale = 0;
        $movimentos = $venda->addChild('movimentos');

        foreach ($fornecedor->eventHotelsOpt ?? $fornecedor->eventAbOpts ?? $fornecedor->eventHallOpts ?? $fornecedor->eventAddOpts ?? $fornecedor->eventTransportOpts ?? [] as $item) {
            $this->movimentosXML($item, $fornecedor->table, $movimentos);
            $taxes = $this->sumTaxesProvider($fornecedor, $item);
            if ($fornecedor->table == 'event_hotel') {
                $qtdDayle = $item->count * $this->daysBetween($item->in, $item->out);
            } else {
                $qtdDayle = $item->count * $this->daysBetween1($item->in, $item->out);
            }
            $sumTotalHotelSale += $this->sumTotal($this->unitSale($item), $taxes, $qtdDayle);
        }
        $venda->addChild('entradatotal', number_format($sumTotalHotelSale, 2, ',', '.'));
    }

    private function movimentosXML($opt, $table, $movimentoXml)
    {
        switch ($table) {
            case 'event_transport':
                $this->movimentoLocacaoXML($opt, $movimentoXml);
                break;
            case 'event_hotel':
                $this->movimentoHotelariaXML($opt, $movimentoXml);
                break;
            default:
                $this->movimentoDiversosXML($opt, $movimentoXml);
                break;
        }
    }


    private function movimentoLocacaoXML($opt, $movimentoXml)
    {
        $movimento = $movimentoXml->addChild('locacao');
        $movimento->addChild('pax', htmlspecialchars($opt->pax ?? ''));
        $movimento->addChild('tipo', htmlspecialchars($opt->tipo ?? ''));
        $movimento->addChild('matricula', htmlspecialchars($opt->matricula ?? ''));
        $movimento->addChild('sucursal', htmlspecialchars($opt->sucursal ?? ''));
        $movimento->addChild('contacontabil', htmlspecialchars($opt->contacontabil ?? ''));
        $movimento->addChild('reqviagem', htmlspecialchars($opt->reqviagem ?? ''));
        $movimento->addChild('motivoviagem', htmlspecialchars($opt->motivoviagem ?? ''));
        $movimento->addChild('moeda', htmlspecialchars($opt->moeda ?? ''));
        $movimento->addChild('moedafornecedor', htmlspecialchars($opt->moedafornecedor ?? ''));
        $movimento->addChild('cambio', htmlspecialchars($opt->cambio ?? ''));
        $movimento->addChild('cambiofornecedor', htmlspecialchars($opt->cambiofornecedor ?? ''));
        $movimento->addChild('datacambio', htmlspecialchars($opt->datacambio ?? ''));
        $movimento->addChild('datacambiofornecedor', htmlspecialchars($opt->datacambiofornecedor ?? ''));
        $movimento->addChild('checkin', htmlspecialchars($opt->in ?? ''));
        $movimento->addChild('horacheckin', htmlspecialchars($opt->horacheckin ?? ''));
        $movimento->addChild('checkout', htmlspecialchars($opt->out ?? ''));
        $movimento->addChild('horacheckout', htmlspecialchars($opt->horacheckout ?? ''));
        $movimento->addChild('taxaservico', htmlspecialchars($opt->taxaservico ?? ''));
        $movimento->addChild('taxaservicofor', htmlspecialchars($opt->taxaservicofor ?? ''));
        $movimento->addChild('comisrecforvalor', htmlspecialchars($opt->comisrecforvalor ?? ''));
        $movimento->addChild('descpagclivalor', htmlspecialchars($opt->descpagclivalor ?? ''));
        $movimento->addChild('observacao', htmlspecialchars($opt->observacao ?? ''));
        $movimento->addChild('observacao2', htmlspecialchars($opt->observacao2 ?? ''));
        $movimento->addChild('numautorizacao', htmlspecialchars($opt->numautorizacao ?? ''));
        $movimento->addChild('localcheckin', htmlspecialchars(mb_substr($opt->localcheckin ?? '', 0, 20)));
        $movimento->addChild('localcheckout', htmlspecialchars(mb_substr($opt->localcheckout ?? '', 0, 20)));
        $movimento->addChild('veiculo', htmlspecialchars(mb_substr($opt->veiculo ?? '', 0, 15)));
        $movimento->addChild('valordiaria', htmlspecialchars($opt->valordiaria ?? ''));
        $movimento->addChild('valordiariabalcao', htmlspecialchars($opt->valordiariabalcao ?? ''));
        $movimento->addChild('valordiariafornecedor', htmlspecialchars($opt->valordiariafornecedor ?? ''));
        $movimento->addChild('qtddiaria', htmlspecialchars($opt->qtddiaria ?? ''));
        $movimento->addChild('confirmadopor', htmlspecialchars(mb_substr($opt->confirmadopor ?? '', 0, 15)));
        $movimento->addChild('dataconfirmacao', htmlspecialchars($opt->dataconfirmacao ?? ''));
        $movimento->addChild('numconfirmacao', htmlspecialchars(mb_substr($opt->numconfirmacao ?? '', 0, 15)));
        $movimento->addChild('taxaextra', htmlspecialchars($opt->taxaextra ?? ''));
        $movimento->addChild('taxaextrafor', htmlspecialchars($opt->taxaextrafor ?? ''));
        $movimento->addChild('tipopag', htmlspecialchars($opt->tipopag ?? ''));
        $movimento->addChild('emailpax', htmlspecialchars($opt->emailpax ?? ''));
        $movimento->addChild('fonepax', htmlspecialchars($opt->fonepax ?? ''));
        $movimento->addChild('docpax', htmlspecialchars($opt->docpax ?? ''));
        $movimento->addChild('valordescsub', htmlspecialchars($opt->valordescsub ?? ''));
        $movimento->addChild('rateios', htmlspecialchars($opt->rateios ?? ''));
        $movimento->addChild('taxasadicionais', htmlspecialchars($opt->taxasadicionais ?? ''));
        $movimento->addChild('uniqueId', htmlspecialchars($opt->uniqueId ?? ''));
    }

    private function movimentoDiversosXML($opt, $movimentoXml)
    {
        $movimento = $movimentoXml->addChild('diversos');
        $movimento->addChild('pax', htmlspecialchars($opt->pax ?? ''));
        $movimento->addChild('tipo', htmlspecialchars($opt->tipo ?? ''));
        $movimento->addChild('matricula', htmlspecialchars($opt->matricula ?? ''));
        $movimento->addChild('sucursal', htmlspecialchars($opt->sucursal ?? ''));
        $movimento->addChild('contacontabil', htmlspecialchars($opt->contacontabil ?? ''));
        $movimento->addChild('reqviagem', htmlspecialchars($opt->reqviagem ?? ''));
        $movimento->addChild('motivoviagem', htmlspecialchars($opt->motivoviagem ?? ''));
        $movimento->addChild('moeda', htmlspecialchars($opt->moeda ?? ''));
        $movimento->addChild('moedafornecedor', htmlspecialchars($opt->moedafornecedor ?? ''));
        $movimento->addChild('cambio', htmlspecialchars($opt->cambio ?? ''));
        $movimento->addChild('cambiofornecedor', htmlspecialchars($opt->cambiofornecedor ?? ''));
        $movimento->addChild('datacambio', htmlspecialchars($opt->datacambio ?? ''));
        $movimento->addChild('datacambiofornecedor', htmlspecialchars($opt->datacambiofornecedor ?? ''));
        $movimento->addChild('checkin', htmlspecialchars($opt->in ?? ''));
        $movimento->addChild('checkout', htmlspecialchars($opt->out ?? ''));
        $movimento->addChild('taxaservico', htmlspecialchars($opt->taxaservico ?? ''));
        $movimento->addChild('taxaservicofor', htmlspecialchars($opt->taxaservicofor ?? ''));
        $movimento->addChild('comisrecforvalor', htmlspecialchars($opt->comisrecforvalor ?? ''));
        $movimento->addChild('descpagclivalor', htmlspecialchars($opt->descpagclivalor ?? ''));
        $movimento->addChild('observacao', htmlspecialchars($opt->observacao ?? ''));
        $movimento->addChild('observacao2', htmlspecialchars($opt->observacao2 ?? ''));
        $movimento->addChild('numautorizacao', htmlspecialchars($opt->numautorizacao ?? ''));
        $movimento->addChild('descricao', htmlspecialchars(mb_substr($opt->descricao ?? '', 0, 20)));
        $movimento->addChild('valordiaria', htmlspecialchars($opt->valordiaria ?? ''));
        $movimento->addChild('valordiariabalcao', htmlspecialchars($opt->valordiariabalcao ?? ''));
        $movimento->addChild('valordiariafornecedor', htmlspecialchars($opt->valordiariafornecedor ?? ''));
        $movimento->addChild('qtdservico', htmlspecialchars($opt->qtdservico ?? ''));
        $movimento->addChild('confirmadopor', htmlspecialchars($opt->confirmadopor ?? ''));
        $movimento->addChild('dataconfirmacao', htmlspecialchars($opt->dataconfirmacao ?? ''));
        $movimento->addChild('numconfirmacao', htmlspecialchars($opt->numconfirmacao ?? ''));
        $movimento->addChild('taxaextra', htmlspecialchars($opt->taxaextra ?? ''));
        $movimento->addChild('taxaextrafor', htmlspecialchars($opt->taxaextrafor ?? ''));
        $movimento->addChild('tipopag', htmlspecialchars($opt->tipopag ?? ''));
        $movimento->addChild('emailpax', htmlspecialchars($opt->emailpax ?? ''));
        $movimento->addChild('fonepax', htmlspecialchars($opt->fonepax ?? ''));
        $movimento->addChild('docpax', htmlspecialchars($opt->docpax ?? ''));
        $movimento->addChild('valordescsub', htmlspecialchars($opt->valordescsub ?? ''));
        $movimento->addChild('rateios', htmlspecialchars($opt->rateios ?? ''));
        $movimento->addChild('taxasadicionais', htmlspecialchars($opt->taxasadicionais ?? ''));
        $movimento->addChild('uniqueId', htmlspecialchars($opt->uniqueId ?? ''));
    }

    private function movimentoHotelariaXML($opt, $movimentoXml)
    {
        $movimento = $movimentoXml->addChild('hotelaria');
        $movimento->addChild('pax', htmlspecialchars($opt->pax ?? ''));
        $movimento->addChild('tipo', htmlspecialchars($opt->tipo ?? ''));
        $movimento->addChild('matricula', htmlspecialchars($opt->matricula ?? ''));
        $movimento->addChild('sucursal', htmlspecialchars($opt->sucursal ?? ''));
        $movimento->addChild('contacontabil', htmlspecialchars($opt->contacontabil ?? ''));
        $movimento->addChild('reqviagem', htmlspecialchars($opt->reqviagem ?? ''));
        $movimento->addChild('motivoviagem', htmlspecialchars($opt->motivoviagem ?? ''));
        $movimento->addChild('moeda', htmlspecialchars($opt->moeda ?? ''));
        $movimento->addChild('moedafornecedor', htmlspecialchars($opt->moedafornecedor ?? ''));
        $movimento->addChild('cambio', htmlspecialchars($opt->cambio ?? ''));
        $movimento->addChild('cambiofornecedor', htmlspecialchars($opt->cambiofornecedor ?? ''));
        $movimento->addChild('datacambio', htmlspecialchars($opt->datacambio ?? ''));
        $movimento->addChild('datacambiofornecedor', htmlspecialchars($opt->datacambiofornecedor ?? ''));
        $movimento->addChild('checkin', htmlspecialchars($opt->in ?? ''));
        $movimento->addChild('checkout', htmlspecialchars($opt->out ?? ''));
        $movimento->addChild('taxaservico', htmlspecialchars($opt->taxaservico ?? ''));
        $movimento->addChild('taxaservicofor', htmlspecialchars($opt->taxaservicofor ?? ''));
        $movimento->addChild('comisrecforvalor', htmlspecialchars($opt->comisrecforvalor ?? ''));
        $movimento->addChild('descpagclivalor', htmlspecialchars($opt->descpagclivalor ?? ''));
        $movimento->addChild('observacao', htmlspecialchars($opt->observacao ?? ''));
        $movimento->addChild('observacao2', htmlspecialchars($opt->observacao2 ?? ''));
        $movimento->addChild('numautorizacao', htmlspecialchars($opt->numautorizacao ?? ''));
        $movimento->addChild('categapto', htmlspecialchars($opt->categapto ?? ''));
        $movimento->addChild('regime', htmlspecialchars($opt->regime ?? ''));
        $movimento->addChild('taxaextra', htmlspecialchars($opt->taxaextra ?? ''));
        $movimento->addChild('taxaextrafor', htmlspecialchars($opt->taxaextrafor ?? ''));
        $movimento->addChild('tipopag', htmlspecialchars($opt->tipopag ?? ''));

        // Aptos
        $aptos = $movimento->addChild('aptos');
        if (!empty($opt->aptos) && is_array($opt->aptos)) {
            foreach ($opt->aptos as $apto) {
                $aptoXml = $aptos->addChild('apto');
                $aptoXml->addChild('tipoapto', htmlspecialchars($apto['tipoapto'] ?? ''));
                $aptoXml->addChild('valordiaria', htmlspecialchars($apto['valordiaria'] ?? ''));
                $aptoXml->addChild('valordiariabalcao', htmlspecialchars($apto['valordiariabalcao'] ?? ''));
                $aptoXml->addChild('valordiariafornecedor', htmlspecialchars($apto['valordiariafornecedor'] ?? ''));
                $aptoXml->addChild('qtddiaria', htmlspecialchars($apto['qtddiaria'] ?? ''));

                // Outros hóspedes
                if (!empty($apto['outroshospedes'])) {
                    $outros = $aptoXml->addChild('outroshospedes');
                    foreach ($apto['outroshospedes'] as $hospede) {
                        $hospedeXml = $outros->addChild('hospede');
                        $hospedeXml->addChild('nome', htmlspecialchars($hospede['nome'] ?? ''));
                        $hospedeXml->addChild('nomecompleto', htmlspecialchars($hospede['nomecompleto'] ?? ''));
                        $hospedeXml->addChild('dtnascimento', htmlspecialchars($hospede['dtnascimento'] ?? ''));
                        $hospedeXml->addChild('email', htmlspecialchars($hospede['email'] ?? ''));
                        $hospedeXml->addChild('docto', htmlspecialchars($hospede['docto'] ?? ''));
                        $hospedeXml->addChild('cpf', htmlspecialchars($hospede['cpf'] ?? ''));
                        $hospedeXml->addChild('celular', htmlspecialchars($hospede['celular'] ?? ''));
                        $hospedeXml->addChild('complemento', htmlspecialchars($hospede['complemento'] ?? ''));
                    }
                }

                $aptoXml->addChild('uniqueId', htmlspecialchars($apto['uniqueId'] ?? ''));
            }
        }

        $movimento->addChild('confirmadopor', htmlspecialchars($opt->confirmadopor ?? ''));
        $movimento->addChild('dataconfirmacao', htmlspecialchars($opt->dataconfirmacao ?? ''));
        $movimento->addChild('numconfirmacao', htmlspecialchars($opt->numconfirmacao ?? ''));
        $movimento->addChild('valordescsub', htmlspecialchars($opt->valordescsub ?? ''));
        $movimento->addChild('emailpax', htmlspecialchars($opt->emailpax ?? ''));
        $movimento->addChild('fonepax', htmlspecialchars($opt->fonepax ?? ''));
        $movimento->addChild('docpax', htmlspecialchars($opt->docpax ?? ''));
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
                $fornecedorXml->addChild('idfornecedor', htmlspecialchars($fornecedor->id));
                $fornecedorXml->addChild('razaonome', htmlspecialchars($fornecedor->name ?? ''));
                $fornecedorXml->addChild('nomefantasia', htmlspecialchars($fornecedor->fantasy_name ?? $fornecedor->name ?? ''));

                $fornecedorXml->addChild('tipopessoa', 'PJ');
                if ($modelClass === \App\Models\ProviderTransport::class) {
                    $tipoFornec = 'LOC';
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
        return Event::with([
            'customer',
            'crd',

            // Hotéis
            'event_hotels.hotel',
            'event_hotels.eventHotelsOpt' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_hotels.eventHotelsOpt.regime',
            'event_hotels.eventHotelsOpt.apto_hotel',
            'event_hotels.eventHotelsOpt.category_hotel',
            'event_hotels.currency',
            'event_hotels.status_his',

            // ABs
            'event_abs.ab',
            'event_abs.eventAbOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_abs.eventAbOpts.Local',
            'event_abs.eventAbOpts.service_type',
            'event_abs.currency',
            'event_abs.status_his',

            // Halls
            'event_halls.hall',
            'event_halls.eventHallOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_halls.eventHallOpts.purpose',
            'event_halls.currency',
            'event_halls.status_his',

            // Adicionais
            'event_adds.add',
            'event_adds.eventAddOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_adds.eventAddOpts.measure',
            'event_adds.eventAddOpts.service',
            'event_adds.currency',
            'event_adds.status_his',

            // Transportes
            'event_transports.transport',
            'event_transports.eventTransportOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_transports.eventTransportOpts.brand',
            'event_transports.eventTransportOpts.vehicle',
            'event_transports.eventTransportOpts.model',
            'event_transports.currency',
            'event_transports.status_his',
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
