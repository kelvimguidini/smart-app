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

        $statusFields = [
            'N' => 'Novo',
            'S' => 'Solicitação',
            'PF' => 'Pedido de Fornecedor',
            'B' => 'Briefing',
            'R' => 'Resposta',
            'P' => 'Preço',
            'EC' => 'Envio ao Cliente',
            'AL' => 'Alteração',
            'CA' => 'Cancelado',
            'A' => 'Aprovado',
            'C' => 'Concluído'
        ];


        $events = $query->with('eventStatus')->get();

        $statusCountsHotel = [];
        $statusCountsTransport = [];

        // Loop through the events and count the occurrences of each status
        foreach ($events as $event) {

            $statusHotel = $event->eventStatus[0]->status_u_hotel ?? 'N';
            $statusTransport = $event->eventStatus[0]->status_u_transport ?? 'N';


            // For Hotel status
            if (isset($statusFields[$statusHotel])) {
                $statusLabelHotel = $statusFields[$statusHotel];

                if (!isset($statusCountsHotel[$statusLabelHotel])) {
                    $statusCountsHotel[$statusLabelHotel] = 1;
                } else {
                    $statusCountsHotel[$statusLabelHotel]++;
                }
            }

            // For Transport status
            if (isset($statusFields[$statusTransport])) {
                $statusLabelTransport = $statusFields[$statusTransport];

                if (!isset($statusCountsTransport[$statusLabelTransport])) {
                    $statusCountsTransport[$statusLabelTransport] = 1;
                } else {
                    $statusCountsTransport[$statusLabelTransport]++;
                }
            }
        }

        // Remove any statuses that don't have any events (null or not found in the result)
        $statusCountsHotel = array_filter($statusCountsHotel);
        $statusCountsTransport = array_filter($statusCountsTransport);


        $response = [
            'hotel' => $statusCountsHotel,
            'transport' => $statusCountsTransport,
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
        $query = $this->getQueryEventBase()->with('eventStatus');

        $queryHotels = $query->where(function ($query) {
            $query->whereHas('eventStatus', function ($query) {
                $query->where('status_hotel', "AA");
            });
        });

        $queryTransports = $query->where(function ($query) {
            $query->whereHas('eventStatus', function ($query) {
                $query->where('status_transport', "AA");
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
            ->where(function ($query) use ($firstDayOfMonth, $lastDayOfMonth) {
                $query->whereDate('date', '>=', $firstDayOfMonth)
                    ->whereDate('date', '<=', $lastDayOfMonth)
                    ->orWhere(function ($query) use ($firstDayOfMonth, $lastDayOfMonth) {
                        $query->whereDate('date_final', '>=', $firstDayOfMonth)
                            ->whereDate('date_final', '<=', $lastDayOfMonth);
                    })
                    ->orWhere(function ($query) use ($firstDayOfMonth, $lastDayOfMonth) {
                        $query->whereDate('date', '<=', $firstDayOfMonth)
                            ->whereDate('date_final', '>=', $lastDayOfMonth);
                    });
            })
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
