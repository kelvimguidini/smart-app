<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventStatus;
use App\Models\ProviderBudget;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function pendingValidate(Request $request)
    {
        $currentUser = Auth::user();
        $isAdmin = Gate::allows('event_admin');
        $isHotelOperator = Gate::allows('hotel_operator');
        $isLandOperator = Gate::allows('land_operator');
        $isAirOperator = Gate::allows('air_operator');

        $query = Event::where(function ($query) {
            $query->whereHas('event_hotels.providerBudget', function ($query) {
                $query->where('evaluated', false);
            })->orWhereHas('event_halls.providerBudget', function ($query) {
                $query->where('evaluated', false);
            })->orWhereHas('event_transports.providerBudget', function ($query) {
                $query->where('evaluated', false);
            })->orWhereHas('event_adds.providerBudget', function ($query) {
                $query->where('evaluated', false);
            })->orWhereHas('event_abs.providerBudget', function ($query) {
                $query->where('evaluated', false);
            });
        });

        if ($isAdmin) {
            // Recuperar todos os eventos para o usuário com permissão de "event_admin"
            return $query->count();
        }

        // Verificar se o usuário possui a permissão de "hotel_operator" ou "land_operator" ou "air_operator"
        $query->where(function ($query) use ($currentUser, $isHotelOperator, $isLandOperator, $isAirOperator) {
            if ($isHotelOperator) {
                $query->where('hotel_operator', $currentUser->id);
            }

            if ($isLandOperator) {
                $query->where('land_operator', $currentUser->id);
            }

            if ($isAirOperator) {
                $query->where('air_operator', $currentUser->id);
            }
            // Verificar se o usuário não possui nenhuma das quatro permissões e retornar 0
            if (!$isHotelOperator && !$isLandOperator && !$isAirOperator) {
                $query->where('id', 0);
            }
        });

        return $query->count();
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function eventStatus(Request $request)
    {
        $query = $this->getQueryEventBase();

        $statusFieldsHotel = [
            'request_hotel' => '`Solicitação`',
            'provider_order_hotel' => '`Pedido de Fornecedor`',
            'briefing_hotel' => '`Briefing`',
            'response_hotel' => '`Resposta`',
            'pricing_hotel' => '`Preço de Hotel`',
            'custumer_send_hotel' => '`Envio ao Cliente`',
            'change_hotel' => '`Alteração`',
            'done_hotel' => '`Concluído`',
        ];

        $statusFieldsTransport = [
            'request_transport' => '`Solicitação`',
            'provider_order_transport' => '`Pedido de Fornecedor`',
            'briefing_transport' => '`Briefing`',
            'response_transport' => '`Resposta`',
            'pricing_transport' => '`Preço`',
            'custumer_send_transport' => '`Envio ao Cliente`',
            'change_transport' => '`Alteração`',
            'done_transport' => '`Concluído`',
        ];

        $query->with(['eventStatus' => function ($query) use ($statusFieldsHotel, $statusFieldsTransport) {
            $selectClause = 'event_id';

            foreach ($statusFieldsHotel as $field => $translation) {
                $selectClause .= ', ' . $field . ' AS ' . $translation;
            }

            foreach ($statusFieldsTransport as $field => $translation) {
                $selectClause .= ', ' . $field . ' AS ' . $translation;
            }

            // Adicione a coluna has_transport usando subconsulta
            $selectClause .= ', (SELECT COUNT(*) FROM event_transport WHERE event_transport.event_id = `event_status`.`event_id`) AS has_transport';

            $selectClause .= ', (SELECT COUNT(*) FROM event_hotel WHERE event_hotel.event_id = `event_status`.`event_id`) +
                       (SELECT COUNT(*) FROM event_ab WHERE event_ab.event_id = `event_status`.`event_id`) +
                       (SELECT COUNT(*) FROM event_hall WHERE event_hall.event_id = `event_status`.`event_id`) AS has_hotel';


            $query->selectRaw($selectClause);
        }]);


        $events = $query->get();

        $statusCountsHotel = [];
        $statusCountsTransport = [];

        foreach ($events as $event) {
            if ($event->eventStatus && $event->eventStatus->has_transport > 0) {
                $statusCountsTransport[$event->id] = "Novo";
            }

            if ($event->eventStatus && $event->eventStatus->has_hotel > 0) {
                $statusCountsHotel[$event->id] = "Novo";
            }

            foreach ($statusFieldsHotel as $field) {
                $field = str_replace('`', '', $field);
                if ($event->eventStatus && $event->eventStatus->$field) {
                    $statusCountsHotel[$event->id] = $field;
                }
            }

            foreach ($statusFieldsTransport as $field) {

                $field = str_replace('`', '', $field);
                if ($event->eventStatus && $event->eventStatus->$field) {
                    $statusCountsTransport[$event->id] = $field;
                }
            }
        }

        $percentagesHotel = [];
        $percentagesTransport = [];

        // Agrupar os valores
        $groupedCountsHotel = array_count_values($statusCountsHotel);
        $groupedCountsTransport = array_count_values($statusCountsTransport);

        // Calcular o percentual de cada valor
        $percentagesHotel = array();

        foreach ($groupedCountsHotel as $status => $count) {
            $percentagesHotel[$status] = $count;
        }

        foreach ($groupedCountsTransport as $status => $count) {
            $percentagesTransport[$status] =  $count;
        }

        $response = [
            'hotel' => $percentagesHotel,
            'transport' => $percentagesTransport,
        ];

        return response()->json($response);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function waitApproval(Request $request)
    {
        $query = $this->getQueryEventBase();

        $queryHotels = $query->where(function ($query) {
            $query->whereHas('eventStatus', function ($query) {
                $query->where('status_hotel', "Aguardando Aprovação");
            });
        });

        $queryTransports = $query->where(function ($query) {
            $query->whereHas('eventStatus', function ($query) {
                $query->where('status_transport', "Aguardando Aprovação");
            });
        });

        $counts = [
            'hotels' => $queryHotels->count(),
            'transports' => $queryTransports->count(),
        ];

        return response()->json($counts);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function linksApproved(Request $request)
    {
        return ProviderBudget::selectRaw('(SUM(approved) / COUNT(*)) * 100 AS budget_approval_rate')
            ->value('budget_approval_rate');
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function byMonths(Request $request)
    {
        $query = $this->getQueryEventBase();

        // Obtém a data do último dia do mês após 6 meses
        $currentDate = Carbon::now();
        $firstDayOfMonth = $currentDate->subMonths(6)->startOfMonth();
        $lastDayOfMonth = $currentDate->copy()->addMonths(11)->endOfMonth();


        $monthsIndex = [
            'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
            'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'
        ];
        // Array de tradução dos meses para português
        $months = [
            'Jan' => 'Jan',
            'Feb' => 'Fev',
            'Mar' => 'Mar',
            'Apr' => 'Abr',
            'May' => 'Mai',
            'Jun' => 'Jun',
            'Jul' => 'Jul',
            'Aug' => 'Ago',
            'Sep' => 'Set',
            'Oct' => 'Out',
            'Nov' => 'Nov',
            'Dec' => 'Dez',
        ];

        // Consulta para recuperar a quantidade de eventos por mês
        $eventCounts = $query->selectRaw('formatar_data_ptbr(date) AS month, COUNT(*) AS event_count')
            ->whereBetween('date', [$firstDayOfMonth, $lastDayOfMonth])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month'); // Indexa os resultados pelo campo 'month'

        // Consulta para recuperar a quantidade de registros por mês
        $registerCounts = $query->selectRaw('formatar_data_ptbr(created_at) AS month, COUNT(*) AS register_count')
            ->whereBetween('created_at', [$firstDayOfMonth, $lastDayOfMonth])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month'); // Indexa os resultados pelo campo 'month'

        $result = (object)array();
        // Preencher os meses ausentes com event_count e register_count igual a zero
        for ($i = 0; $i < 12; $i++) {
            // Obter o mês e o ano
            $month = $firstDayOfMonth->format('M');
            $year = $firstDayOfMonth->format('Y');

            // Concatenar mês e ano traduzidos
            $monthYear = $months[$month] . ' ' . $year;

            $eCount = $eventCounts->has($monthYear) ? $eventCounts->where('month', $monthYear)->first()->event_count : 0;
            $rCount = $registerCounts->has($monthYear) ? $registerCounts->where('month', $monthYear)->first()->register_count : 0;

            if (!$eventCounts->has($monthYear)) {
                $eventCounts->put($monthYear, (object)['month' => $monthYear, 'event_count' => $eCount, 'register_count' => $rCount]);
            } else {
                $eventCounts->get($monthYear)->register_count = $rCount; // Atualiza o valor de register_count
            }

            $firstDayOfMonth->addMonth();
        }


        $eventCounts = $eventCounts->sortBy(function ($result) use ($monthsIndex) {
            $monthYear = $result->month;
            $month = substr($monthYear, 0, 3);
            $year = substr($monthYear, -4);
            $monthIndex = array_search($month, $monthsIndex);
            return $year .  $monthIndex;
        })->values();


        return response()->json($eventCounts);
    }

    /**
     * Display the registration view.
     *
     * Builder $query
     */
    private function getQueryEventBase()
    {
        $currentUser = Auth::user();
        $isAdmin = Gate::allows('event_admin');
        $isHotelOperator = Gate::allows('hotel_operator');
        $isLandOperator = Gate::allows('land_operator');
        $isAirOperator = Gate::allows('air_operator');

        $query = Event::query();
        if (!$isAdmin) {
            $query->where(function ($query) use ($currentUser, $isHotelOperator, $isLandOperator, $isAirOperator) {
                if ($isHotelOperator) {
                    $query->where('hotel_operator', $currentUser->id);
                }

                if ($isLandOperator) {
                    $query->where('land_operator', $currentUser->id);
                }

                if ($isAirOperator) {
                    $query->where('air_operator', $currentUser->id);
                }
                // Verificar se o usuário não possui nenhuma das quatro permissões e retornar 0
                if (!$isHotelOperator && !$isLandOperator && !$isAirOperator) {
                    $query->where('id', 0);
                }
            });
        }

        return $query;
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function userGroups(Request $request)
    {
        $groups = Role::selectRaw('name AS Name, COUNT(user_role.user_id) AS CountUsers, (COUNT(user_role.user_id) / total_users.total) * 100 AS Percentage')
            ->leftJoin('user_role', 'roles.id', '=', 'user_role.role_id')
            ->crossJoin(DB::raw('(SELECT COUNT(id) AS total FROM users) AS total_users'))
            ->groupBy('roles.id')
            ->get();

        return response()->json($groups);
    }
}
