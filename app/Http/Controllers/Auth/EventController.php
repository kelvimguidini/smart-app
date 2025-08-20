<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Constants;
use App\Models\Apto;
use App\Models\Brand;
use App\Models\Broker;
use App\Models\BrokerTransport;
use App\Models\CarModel;
use App\Models\Category;
use App\Models\CRD;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventAB;
use App\Models\EventAdd;
use App\Models\EventHall;
use App\Models\EventHotel;
use App\Models\EventLocal;
use App\Models\EventStatus;
use App\Models\EventTransport;
use App\Models\Frequency;
use App\Models\Local;
use App\Models\Measure;
use App\Models\Provider;
use App\Models\ProviderServices;
use App\Models\ProviderTransport;
use App\Models\Purpose;
use App\Models\PurposeHall;
use App\Models\Regime;
use App\Models\Service;
use App\Models\ServiceAdd;
use App\Models\ServiceHall;
use App\Models\ServiceType;
use App\Models\StatusHistory;
use App\Models\TransportService;
use App\Models\User;
use App\Models\Vehicle;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function list(Request $request)
    {
        $perPage = 10;
        $page = $request->page ? $request->page : 1;

        $userId = Auth::user()->id;

        // Obter os filtros do request
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $city = $request->city;
        $consultant = $request->consultant;
        $client = $request->client;
        $status = $request->status;
        $eventCode = $request->eventCode;
        $valorFaturamento = $request->valorFaturamento;

        $query = Event::with([
            'crd',
            'customer',
            'event_hotels' => function ($q) {
                $q->with([
                    'hotel',
                    'status_his',
                    'eventHotelsOpt' => function ($q2) {
                        $q2->orderBy('in', 'asc');
                    }
                ]);
            },
            'event_abs' => function ($q) {
                $q->with([
                    'ab',
                    'status_his',
                    'eventAbOpts' => function ($q2) {
                        $q2->orderBy('in', 'asc');
                    }
                ]);
            },
            'event_halls' => function ($q) {
                $q->with([
                    'hall',
                    'status_his',
                    'eventHallOpts' => function ($q2) {
                        $q2->orderBy('in', 'asc');
                    }
                ]);
            },
            'event_adds' => function ($q) {
                $q->with([
                    'add',
                    'status_his',
                    'eventAddOpts' => function ($q2) {
                        $q2->orderBy('in', 'asc');
                    }
                ]);
            },
            'event_transports' => function ($q) {
                $q->with([
                    'transport',
                    'status_his',
                    'providerBudget.user',
                    'eventTransportOpts' => function ($q2) {
                        $q2->orderBy('in', 'asc');
                    }
                ]);
            },
            'event_hotels.providerBudget.user',
            'event_abs.providerBudget.user',
            'event_halls.providerBudget.user',
            'event_adds.providerBudget.user',
            'event_transports.providerBudget.user',
        ]);



        if (Gate::allows('event_admin')) {
            $query
                ->with('hotelOperator')
                ->with('airOperator')
                ->with('landOperator');
        } else if (Gate::allows('hotel_operator') || Gate::allows('land_operator') || Gate::allows('air_operator')) {
            $query
                ->with(['hotelOperator' => function ($query) use ($userId) {
                    $query->where('id', '=', $userId);
                }])
                ->with(['airOperator' => function ($query) use ($userId) {
                    $query->where('id', '=', $userId);
                }])
                ->with(['landOperator' => function ($query) use ($userId) {
                    $query->where('id', '=', $userId);
                }]);
        } else {
            abort(403);
        }


        // Aplicar filtros se estiverem presentes
        if ($startDate && $endDate) {
            $query->where(function ($query) use ($startDate, $endDate) {
                $query->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate)
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->whereDate('date_final', '>=', $startDate)
                            ->whereDate('date_final', '<=', $endDate);
                    })
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->whereDate('date', '<=', $startDate)
                            ->whereDate('date_final', '>=', $endDate);
                    });
            });
        }

        // Aplicar filtro de cidade, se estiver presente
        if ($city) {
            $query->whereHas('eventLocals', function ($query) use ($city) {
                $query->where('cidade', $city);
            });
        }

        // Aplicar filtro de cidade, se estiver presente
        if ($eventCode) {
            $query->where('code', $eventCode);
        }

        if ($valorFaturamento) {
            $query->where('valor_faturamento', $valorFaturamento);
        }

        if ($consultant && $consultant != ".::Selecione::.") {
            $query->where(function ($query) use ($consultant) {
                $query->where('hotel_operator', $consultant)
                    ->orWhere('air_operator', $consultant)
                    ->orWhere('land_operator', $consultant);
            });
        }

        if ($client && $client != ".::Selecione::.") {
            $query->where('customer_id', $client);
        }
        // $query->orderByDesc('created_at')->first('status', $status);
        if ($status && $status != ".::Selecione::.") {

            $query->where(function ($query) use ($status) {

                $query->whereHas('event_hotels', function ($query) use ($status) {
                    $query->where(DB::raw('(select h.status from status_history as h where h.table = "event_hotels" and h.table_id = event_hotel.id order by h.created_at desc limit 1)'), $status);
                });

                $query->orWhereHas('event_abs', function ($query) use ($status) {
                    $query->where(DB::raw('(select h.status from status_history as h where h.table = "event_abs" and h.table_id = event_ab.id order by h.created_at desc limit 1)'), $status);
                });

                $query->orWhereHas('event_halls', function ($query) use ($status) {
                    $query->where(DB::raw('(select h.status from status_history as h where h.table = "event_halls" and h.table_id = event_hall.id order by h.created_at desc limit 1)'), $status);
                });

                $query->orWhereHas('event_adds', function ($query) use ($status) {
                    $query->where(DB::raw('(select h.status from status_history as h where h.table = "event_adds" and h.table_id = event_add.id order by h.created_at desc limit 1)'), $status);
                });

                $query->orWhereHas('event_transports', function ($query) use ($status) {
                    $query->where(DB::raw('(select h.status from status_history as h where h.table = "event_transports" and h.table_id = event_transport.id order by h.created_at desc limit 1)'), $status);
                });
            });
        }

        $events = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
        $user = User::where('is_api_user', false)->get();
        return Inertia::render('Auth/Event/EventList', [
            'events' => $events,
            'filters' => (object)[
                'startDate' => $startDate,
                'endDate' => $endDate,
                'city' => $city,
                'consultant' => $consultant,
                'client' => $client,
            ],
            'customers' => Customer::all(),
            'users' => $user,
            'allStatus' => Constants::STATUS
        ]);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator') && !Gate::allows('land_operator') && !Gate::allows('air_operator')) {
            abort(403);
        }

        $event = Event::with("eventLocals")->find($request->id);

        $crds = CRD::with("customer")->get();
        $customers = Customer::all();
        $users = User::where('is_api_user', false)->get();

        $providers = Provider::with("city")->get();
        $providersService = ProviderServices::with("city")->get();
        $providersTranport = ProviderTransport::with("city")->get();

        $brokers = Broker::all();
        $currencies = Currency::all();
        $regimes = Regime::all();
        $purposes = Purpose::all();

        $services = Service::all();
        $servicesType = ServiceType::all();
        $locals = Local::all();


        $servicesHall = ServiceHall::all();
        $purposesHall = PurposeHall::all();

        $servicesAdd = ServiceAdd::all();
        $frequencies = Frequency::all();
        $measures = Measure::all();

        $brokersT = BrokerTransport::all();
        $vehicles = Vehicle::all();
        $models = CarModel::all();
        $servicesT = TransportService::all();
        $brands = Brand::all();

        $eventHotel = $request->tab == 1
            ? EventHotel::with([
                'eventHotelsOpt' => function ($q) {
                    $q->orderBy('in');
                },
                'hotel.city',
                'currency',
                'event',
                'status_his'
            ])->find($request->ehotel)
            : null;

        $eventHotels = EventHotel::with([
            'eventHotelsOpt' => function ($q) {
                $q->orderBy('in');
            },
            'eventHotelsOpt.broker',
            'eventHotelsOpt.regime',
            'eventHotelsOpt.purpose',
            'eventHotelsOpt.apto_hotel',
            'eventHotelsOpt.category_hotel',
            'hotel.city',
            'currency',
            'event'
        ])->where('event_id', '=', $request->id)->get();


        $eventAB = $request->tab == 2
            ? EventAB::with([
                'eventAbOpts' => function ($q) {
                    $q->orderBy('in');
                },
                'ab.city',
                'currency',
                'event',
                'status_his'
            ])->find($request->ehotel)
            : null;

        $eventABs = EventAB::with([
            'eventAbOpts' => function ($q) {
                $q->orderBy('in');
            },
            'eventAbOpts.broker',
            'eventAbOpts.service',
            'eventAbOpts.service_type',
            'eventAbOpts.local',
            'ab.city',
            'currency',
            'event'
        ])->where('event_id', '=', $request->id)->get();


        $eventHall = $request->tab == 3
            ? EventHall::with([
                'eventHallOpts' => function ($q) {
                    $q->orderBy('in');
                },
                'hall.city',
                'currency',
                'event',
                'status_his'
            ])->find($request->ehotel)
            : null;

        $eventHalls = EventHall::with([
            'eventHallOpts' => function ($q) {
                $q->orderBy('in');
            },
            'eventHallOpts.broker',
            'eventHallOpts.service',
            'eventHallOpts.purpose',
            'hall.city',
            'currency',
            'event'
        ])->where('event_id', '=', $request->id)->get();


        $eventAdd = $request->tab == 4
            ? EventAdd::with([
                'eventAddOpts' => function ($q) {
                    $q->orderBy('in');
                },
                'add.city',
                'currency',
                'event',
                'status_his'
            ])->find($request->ehotel)
            : null;

        $eventAdds = EventAdd::with([
            'eventAddOpts' => function ($q) {
                $q->orderBy('in');
            },
            'eventAddOpts.frequency',
            'eventAddOpts.measure',
            'eventAddOpts.service',
            'add.city',
            'currency',
            'event'
        ])->where('event_id', '=', $request->id)->get();


        $eventTransport = $request->tab == 5
            ? EventTransport::with([
                'eventTransportOpts' => function ($q) {
                    $q->orderBy('in');
                },
                'transport.city',
                'currency',
                'event',
                'status_his'
            ])->find($request->ehotel)
            : null;

        $eventTransports = EventTransport::with([
            'eventTransportOpts' => function ($q) {
                $q->orderBy('in');
            },
            'eventTransportOpts.broker',
            'eventTransportOpts.vehicle',
            'eventTransportOpts.model',
            'eventTransportOpts.service',
            'eventTransportOpts.brand',
            'transport.city',
            'currency',
            'event'
        ])->where('event_id', '=', $request->id)->get();


        return Inertia::render('Auth/Event/EventCreate', [
            'crds' => $crds,
            'customers' => $customers,
            'users' => $users,
            'event' => $event,
            'providers' => $providers,
            'tab' => $request->tab,
            'brokers' => $brokers,
            'currencies' => $currencies,
            'regimes' => $regimes,
            'purposes' => $purposes,
            'eventHotels' => $eventHotels,
            'eventHotel' => $eventHotel,
            'eventAB' => $eventAB,
            'eventABs' => $eventABs,
            'services' => $services,
            'servicesType' => $servicesType,
            'locals' => $locals,
            'servicesHall' => $servicesHall,
            'purposesHall' => $purposesHall,
            'eventHall' => $eventHall,
            'eventHalls' => $eventHalls,
            'servicesAdd' => $servicesAdd,
            'frequencies' => $frequencies,
            'measures' => $measures,
            'eventAdd' => $eventAdd,
            'eventAdds' => $eventAdds,
            'catsHotel' => Category::all(),
            'aptosHotel' => Apto::all(),
            'brokersT' => $brokersT,
            'vehicles' => $vehicles,
            'models' => $models,
            'servicesT' => $servicesT,
            'brands' => $brands,
            'eventTransport' => $eventTransport,
            'eventTransports' => $eventTransports,
            'providersService' => $providersService,
            'providersTranport' => $providersTranport
        ]);
    }


    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        if (!Gate::allows('event_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'customer' => 'required|numeric',
            'code' => 'required|string|max:255',
            'requester' => 'required|string|max:255',
            'paxBase' => 'required|string|max:255',
            'date' => 'required|date',
            'date_final' => 'required|date|after_or_equal:in',
            'crd_id' => 'required|numeric'
        ]);

        try {

            if ($request->id > 0) {
                // Obtém os IDs das tabelas de eventos vinculadas ao evento principal
                $relatedTables = [
                    'event_hotels' => EventHotel::where('event_id', $request->id)->pluck('id')->toArray(),
                    'event_abs' => EventAB::where('event_id', $request->id)->pluck('id')->toArray(),
                    'event_halls' => EventHall::where('event_id', $request->id)->pluck('id')->toArray(),
                    'event_adds' => EventAdd::where('event_id', $request->id)->pluck('id')->toArray(),
                    'event_transports' => EventTransport::where('event_id', $request->id)->pluck('id')->toArray(),
                ];

                // Achata os IDs coletados em um único array
                $allTableIds = array_merge(...array_values($relatedTables));

                $user = User::find(Auth::user()->id);
                if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                    // Busca no StatusHistory se há algum registro com status proibido
                    $historyExists = StatusHistory::whereIn('table', array_keys($relatedTables))
                        ->whereIn('table_id', $allTableIds)
                        ->whereIn('status', ['dating_with_customer', 'Cancelled'])
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->groupBy('table_id') // Agrupa os históricos por `table_id`
                        ->map(fn($history) => $history->first()) // Pega apenas o último registro de cada grupo
                        ->contains(fn($latest) => in_array($latest->status, ['dating_with_customer', 'Cancelled']));


                    if ($historyExists) {
                        return redirect()->back()->with('flash', [
                            'message' => 'Esse evento não pode ser atualizado porque existe um ou mais cadastros finalizados!',
                            'type' => 'danger'
                        ]);
                    }
                }

                $event = Event::find($request->id);

                $event->name = $request->name;
                $event->customer_id = $request->customer;
                $event->code = $request->code;
                $event->requester = $request->requester;
                $event->sector = $request->sector;
                $event->pax_base = $request->paxBase;
                $event->cost_center = $request->cc;
                $event->date = $request->date;
                $event->date_final = $request->date_final;
                $event->crd_id = $request->crd_id;
                $event->hotel_operator = $request->hotel_operator;
                $event->air_operator = $request->air_operator;
                $event->land_operator = $request->land_operator;

                $event->save();

                $savedCountries = $event->eventLocals()->pluck('id')->toArray();
                $submittedCountries = $request->countries;

                // Verificar quais países já estão no banco e tomar ações apropriadas
                foreach ($submittedCountries as $submittedCountry) {
                    // Verificar se o país já está no banco
                    if (in_array($submittedCountry['id'], $savedCountries)) {
                        // Atualizar o país existente no banco
                        $country = EventLocal::find($submittedCountry['id']);

                        $country->pais = $submittedCountry['pais'];
                        $country->cidade = $submittedCountry['cidade'];
                        // Outros campos do país...

                        $country->save();
                    } else {
                        // Criar um novo país no banco
                        $country = new EventLocal();
                        $country->event_id = $event->id;
                        $country->pais = $submittedCountry['pais'];
                        $country->cidade = $submittedCountry['cidade'];

                        $country->save();
                    }
                }

                // Remover os países do banco que não estão mais presentes no formulário
                $removedCountries = array_diff($savedCountries, array_column($submittedCountries, 'id'));
                EventLocal::whereIn('id', $removedCountries)->delete();
            } else {

                $event = Event::create([
                    'name' => $request->name,
                    'customer_id' => $request->customer,
                    'code' => $request->code,
                    'requester' => $request->requester,
                    'sector' => $request->sector,
                    'pax_base' => $request->paxBase,
                    'cost_center' => $request->cc,
                    'date' => $request->date,
                    'date_final' => $request->date_final,
                    'crd_id' => $request->crd_id,
                    'hotel_operator' => $request->hotel_operator,
                    'air_operator' => $request->air_operator,
                    'land_operator' => $request->land_operator,
                ]);
                foreach ($request->countries as $country) {
                    EventLocal::create([
                        'pais' => $country['pais'],
                        'cidade' => $country['cidade'],
                        'event_id' => $event->id,
                    ]);
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('event-edit',  ['id' => $event->id, 'tab' => 1])->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }


    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('event_admin')) {
            abort(403);
        }
        try {

            // Obtém os IDs das tabelas de eventos vinculadas ao evento principal
            $relatedTables = [
                'event_hotels' => EventHotel::where('event_id', $request->id)->pluck('id')->toArray(),
                'event_abs' => EventAB::where('event_id', $request->id)->pluck('id')->toArray(),
                'event_halls' => EventHall::where('event_id', $request->id)->pluck('id')->toArray(),
                'event_adds' => EventAdd::where('event_id', $request->id)->pluck('id')->toArray(),
                'event_transports' => EventTransport::where('event_id', $request->id)->pluck('id')->toArray(),
            ];

            // Achata os IDs coletados em um único array
            $allTableIds = array_merge(...array_values($relatedTables));

            $user = User::find(Auth::user()->id);

            // Verifica se o usuário tem a permissão 'status_level_2'
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                // Busca no StatusHistory se há algum registro com status proibido
                $historyExists = StatusHistory::whereIn('table', array_keys($relatedTables))
                    ->whereIn('table_id', $allTableIds)
                    ->whereIn('status', ['dating_with_customer'])
                    ->exists();

                if ($historyExists) {
                    return redirect()->back()->with('flash', [
                        'message' => 'Esse evento não pode ser apagado porque existe um ou mais cadastros finalizados!',
                        'type' => 'danger'
                    ]);
                }
            }

            $r = Event::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }

    public function saveExchangeRate(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:event,id',
            'exchange_rate' => 'required|numeric'
        ]);

        $event = Event::find($request->event_id);
        $event->exchange_rate = $request->exchange_rate;
        $event->save();

        return redirect()->back()->with('flash', ['message' => 'Câmbio salvo com sucesso!', 'type' => 'success']);
    }

    public function saveValorFaturamento(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:event,id',
            'vl_faturamento' => 'required|numeric'
        ]);

        $event = Event::find($request->event_id);
        $event->valor_faturamento = $request->vl_faturamento;
        $event->save();

        return redirect()->back()->with('flash', ['message' => 'Valor faturamento salvo com sucesso!', 'type' => 'success']);
    }
}
