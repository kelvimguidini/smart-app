<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PdfEmail;
use App\Models\Event;
use App\Models\EventAB;
use App\Models\EventABOpt;
use App\Models\EventAdd;
use App\Models\EventAddOpt;
use App\Models\EventHall;
use App\Models\EventHallOpt;
use App\Models\EventHotel;
use App\Models\EventHotelOpt;
use App\Models\ProviderBudget;
use App\Models\ProviderBudgetItem;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{


    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function createLink(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator') && !Gate::allows('land_operator')) {
            abort(403);
        }
        $provider = $request->provider_id;
        $event = $request->event_id;

        if ($request->download == "true" || $request->attachment == "true") {

            $eventBank = Event::with('customer')->with([
                'event_hotels.hotel' => function ($query) use ($provider) {
                    $query->where('id', '=', $provider);
                },
                'event_abs.ab' => function ($query) use ($provider) {
                    $query->where('id', '=', $provider);
                },
                'event_halls.hall' => function ($query) use ($provider) {
                    $query->where('id', '=', $provider);
                },
                'event_adds.add' => function ($query) use ($provider) {
                    $query->where('id', '=', $provider);
                },

                'event_hotels.eventHotelsOpt' => function ($query) use ($provider) {
                    $query->whereHas('event_hotel', function ($query) use ($provider) {
                        $query->where('hotel_id', '=', $provider);
                    });
                },
                'event_abs.eventAbOpts' => function ($query) use ($provider) {
                    $query->whereHas('event_ab', function ($query) use ($provider) {
                        $query->where('ab_id', '=', $provider);
                    });
                },
                'event_halls.eventHallOpts' => function ($query) use ($provider) {
                    $query->whereHas('event_hall', function ($query) use ($provider) {
                        $query->where('hall_id', '=', $provider);
                    });
                },
                'event_adds.eventAddOpts' => function ($query) use ($provider) {
                    $query->whereHas('event_add', function ($query) use ($provider) {
                        $query->where('add_id', '=', $provider);
                    });
                },
            ])->find($event);

            $providers = collect();

            if ($eventBank->event_hotels->isNotEmpty()) {
                $providers = $providers->concat($eventBank->event_hotels->pluck('hotel'));
            }

            if ($eventBank->event_abs->isNotEmpty()) {
                $providers = $providers->concat($eventBank->event_abs->pluck('ab'));
            }

            if ($eventBank->event_halls->isNotEmpty()) {
                $providers = $providers->concat($eventBank->event_halls->pluck('hall'));
            }

            if ($eventBank->event_adds->isNotEmpty()) {
                $providers = $providers->concat($eventBank->event_adds->pluck('add'));
            }

            $providerBank = $providers->filter()->unique()->values()->first();


            $arr = array(
                "providerBank" => $providerBank,
                "eventBank" => $eventBank
            );

            $pdf = $this->createPDF($arr, 1);
            // return $pdf;
            // Renderize o HTML como PDF
            $pdf->render();
            if ($request->download == "true") {
                return $pdf->stream('Orçamento-hotel.pdf');
            }
        }

        $msg = $request->message != null && $request->message != "!" ? urldecode($request->message) : "";
        if ($request->linkEmail == "true" || $request->linkEmail == "1") {
            $msg .= '<div style="text-align: center;">
                        <a href="' . route('budget', ['token' => $request->link]) . '" class="btn btn-sm btn-primary" style="display: inline-block; padding: 10px 20px; border-radius: 5px; color: #fff; background-color: #007bff; border-color: #007bff; text-decoration: none;">Abrir o formulário</a>
                        <p style="font-size: 12px; margin-top: 10px;">Se o botão não aparecer, copie e cole esta URL: <a href="' . route('budget', ['token' => $request->link]) . '" style="text-decoration: underline; color: #007bff;">' . route('budget', ['token' => $request->link]) . '</a></p>
                    </div>';
        }

        $user = User::find(Auth::user()->id);
        $data = [
            'body' => $msg,
            'hasAttachment' => $request->attachment == "true",
            'signature' => $user->signature != null ? $user->signature : "",
            'subject' => "Orçamento de hotel"
        ];
        $send = Mail::to(explode(";", $request->emails));

        if ($request->copyMe == "true") {
            $send->cc($user->email);
        }

        if ($request->attachment == "true") {
            $send->send(new PdfEmail($pdf->output(), 'Orçamento-hotel.pdf', $data, "Orçamento de hotel"));
        } else {
            $send->send(new PdfEmail(null, 'Orçamento-hotel.pdf', $data, "Orçamento de hotel"));
        }

        DB::table('email_log')->insert(
            array(
                'event_id' => $event,
                'provider_id' => $provider,
                'sender_id' => $user->id,
                'body' => $msg,
                'attachment' => $request->attachment == "true" ? $pdf->output() : '',
                'type' => 'budget',
                'to' => $request->emails,
            )
        );

        // Encontre o registro existente com base no event_id e provider_id
        $eventHotel = EventHotel::where('event_id', $event)->where('hotel_id', $provider)->first();

        if ($eventHotel) {
            $eventHotel->sended_mail_link = true;
            $eventHotel->token_budget = $request->link;
            $eventHotel->update();
        }
        // Encontre o registro existente com base no event_id e provider_id
        $eventAb = EventAB::where('event_id', $event)->where('ab_id', $provider)->first();

        if ($eventAb) {
            $eventAb->sended_mail_link = true;
            $eventAb->token_budget = $request->link;
            $eventAb->update();
        }
        // Encontre o registro existente com base no event_id e provider_id
        $eventHall = EventHall::where('event_id', $event)->where('hall_id', $provider)->first();

        if ($eventHall) {
            $eventHall->sended_mail_link = true;
            $eventHall->token_budget = $request->link;
            $eventHall->update();
        }
        // Encontre o registro existente com base no event_id e provider_id
        $eventAdd = EventAdd::where('event_id', $event)->where('add_id', $provider)->first();

        if ($eventAdd) {
            $eventAdd->sended_mail_link = true;
            $eventAdd->token_budget = $request->link;
            $eventAdd->update();
        }

        return redirect()->back()->with('flash', ['message' => 'E-mail enviado com sucesso! Sua URL é:' . route('budget', ['token' => $request->link]), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function budget(Request $request)
    {
        $eventHotel = EventHotel::with(['hotel', 'eventHotelsOpt', 'eventHotelsOpt.regime', 'eventHotelsOpt.purpose', 'eventHotelsOpt.apto_hotel', 'eventHotelsOpt.category_hotel', 'currency'])->where('token_budget', $request->token)->first();
        $eventAb = EventAB::with(['ab', 'eventAbOpts', 'eventAbOpts.Local', 'eventAbOpts.service', 'eventAbOpts.service_type', 'currency'])->where('token_budget', $request->token)->first();
        $eventHall = EventHall::with(['hall', 'eventHallOpts', 'eventHallOpts.purpose', 'currency'])->where('token_budget', $request->token)->first();
        $eventAdd = EventAdd::with(['add', 'eventAddOpts', 'eventAddOpts.service', 'eventAddOpts.measure', 'eventAddOpts.frequency', 'currency'])->where('token_budget', $request->token)->first();

        if ($eventHotel || $eventAb || $eventHall || $eventAdd) {

            $event = 0;

            if ($eventHotel) {
                $providerName = $eventHotel->hotel->name;
                $providerCity = $eventHotel->hotel->city;
                $event = $eventHotel->event_id;
            } else if ($eventAb) {
                $providerName = $eventAb->ab->name;
                $providerCity = $eventAb->ab->city;
                $event = $eventAb->event_id;
            } else if ($eventHall) {
                $providerName = $eventHall->hall->name;
                $providerCity = $eventHall->hall->city;
                $event = $eventHall->event_id;
            } else if ($eventAdd) {
                $providerName = $eventAdd->add->name;
                $providerCity = $eventAdd->add->city;
                $event = $eventAdd->event_id;
            }
            $budget = ProviderBudget::with(['providerBudgetItems'])
                ->when($eventHotel, function ($query) use ($eventHotel) {
                    return $query->where('event_hotel_id', $eventHotel->id);
                })
                ->when($eventAb, function ($query) use ($eventAb) {
                    return $query->where('event_ab_id', $eventAb->id);
                })
                ->when($eventHall, function ($query) use ($eventHall) {
                    return $query->where('event_hall_id', $eventHall->id);
                })
                ->when($eventAdd, function ($query) use ($eventAdd) {
                    return $query->where('event_add_id', $eventAdd->id);
                })
                ->first();


            $eventBank = Event::with('hotelOperator')->find($event);

            return Inertia::render('Auth/Event/Budget', [
                'tokenValid' => true,
                'event' => $eventBank,
                'providerCity' => $providerCity,
                'providerName' => $providerName,
                'eventHotel' => $eventHotel,
                'eventAb' => $eventAb,
                'eventHall' => $eventHall,
                'eventAdd' => $eventAdd,
                'budget' => $budget,
                'prove' => $request->prove,
                'user' => $request->user,
                'token' => $request->token,
                'tokenEvaluated' => $budget && $budget->evaluated
            ]);
        }

        return Inertia::render('Auth/Event/Budget', [
            'tokenValid' => false
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
        try {

            if ($request->id > 0) {
                $budget = ProviderBudget::find($request->id);

                $budget->hosting_fee_hotel = $request->hostingFeeHotel;
                $budget->iss_fee_hotel = $request->ISSFeeHotel;
                $budget->iva_fee_hotel = $request->IVAFeeHotel;
                $budget->comment_hotel = $request->commentHotel;
                $budget->event_hotel_id = $request->eventHotelId;

                $budget->hosting_fee_ab = $request->hostingFeeAb;
                $budget->iss_fee_ab = $request->ISSFeeAb;
                $budget->iva_fee_ab = $request->IVAFeeAb;
                $budget->comment_ab = $request->commentAb;
                $budget->event_ab_id = $request->eventAbId;

                $budget->hosting_fee_add = $request->hostingFeeAdd;
                $budget->iss_fee_add = $request->ISSFeeAdd;
                $budget->iva_fee_add = $request->IVAFeeAdd;
                $budget->comment_add = $request->commentAdd;
                $budget->event_add_id = $request->eventAddId;

                $budget->hosting_fee_hall = $request->hostingFeeHall;
                $budget->iss_fee_hall = $request->ISSFeeHall;
                $budget->iva_fee_hall = $request->IVAFeeHall;
                $budget->comment_hall = $request->commentHall;
                $budget->event_hall_id = $request->eventHallId;

                $budget->save();
            } else {

                $budget = ProviderBudget::create([
                    'hosting_fee_hotel' => $request->hostingFeeHotel,
                    'iss_fee_hotel' => $request->ISSFeeHotel,
                    'iva_fee_hotel' => $request->IVAFeeHotel,
                    'comment_hotel' => $request->commentHotel,
                    'event_hotel_id' => $request->eventHotelId,

                    'hosting_fee_ab' => $request->hostingFeeAb,
                    'iss_fee_ab' => $request->ISSFeeAb,
                    'iva_fee_ab' => $request->IVAFeeAb,
                    'comment_ab' => $request->commentAb,
                    'event_ab_id' => $request->eventAbId,

                    'hosting_fee_add' => $request->hostingFeeAdd,
                    'iss_fee_add' => $request->ISSFeeAdd,
                    'iva_fee_add' => $request->IVAFeeAdd,
                    'comment_add' => $request->commentAdd,
                    'event_add_id' => $request->eventAddId,

                    'hosting_fee_hall' => $request->hostingFeeHall,
                    'iss_fee_hall' => $request->ISSFeeHall,
                    'iva_fee_hall' => $request->IVAFeeHall,
                    'comment_hall' => $request->commentHall,
                    'event_hall_id' => $request->eventHallId,
                ]);
            }

            for ($i = 0; $i < count($request->valuesHotel); $i++) {
                if (isset($request->itemIdsHotel[$i])) {
                    $r = ProviderBudgetItem::find($request->itemIdsHotel[$i]);

                    $r->event_hotel_opt_id = $request->idsOptHotel[$i];
                    $r->comission  = $request->comissionsHotel[$i];
                    $r->value  = $request->valuesHotel[$i];
                    $r->comment  = $request->commentsHotel[$i];
                } else {
                    ProviderBudgetItem::create([
                        'event_hotel_opt_id'  => $request->idsOptHotel[$i],
                        'comission'  => $request->comissionsHotel[$i],
                        'value'  => $request->valuesHotel[$i],
                        'comment'  => $request->commentsHotel[$i],
                        'provider_budget_id' => $budget->id,
                    ]);
                }
            }

            for ($i = 0; $i < count($request->valuesAb); $i++) {
                if (isset($request->itemIdsAb[$i])) {
                    $r = ProviderBudgetItem::find($request->itemIdsAb[$i]);
                    $r->event_ab_opt_id = $request->idsOptAb[$i];
                    $r->comission = $request->comissionsAb[$i];
                    $r->value = $request->valuesAb[$i];
                    $r->comment = $request->commentsAb[$i];
                }

                ProviderBudgetItem::create([
                    'event_ab_opt_id'  => $request->idsOptAb[$i],
                    'comission'  => $request->comissionsAb[$i],
                    'value'  => $request->valuesAb[$i],
                    'comment'  => $request->commentsAb[$i],
                    'provider_budget_id' => $budget->id,
                ]);
            }

            for ($i = 0; $i < count($request->valuesAdd); $i++) {
                if (isset($request->itemIdsAdd[$i])) {
                    $r = ProviderBudgetItem::find($request->itemIdsAdd[$i]);
                    $r->event_add_opt_idd = $request->idsOptAdd[$i];
                    $r->comission = $request->comissionsAdd[$i];
                    $r->value = $request->valuesAdd[$i];
                    $r->comment = $request->commentsAdd[$i];
                }

                ProviderBudgetItem::create([
                    'event_add_opt_idd'  => $request->idsOptAdd[$i],
                    'comission'  => $request->comissionsAdd[$i],
                    'value'  => $request->valuesAdd[$i],
                    'comment'  => $request->commentsAdd[$i],
                    'provider_budget_id' => $budget->id,
                ]);
            }

            for ($i = 0; $i < count($request->valuesHall); $i++) {
                if (isset($request->itemIdsHall[$i])) {
                    $r = ProviderBudgetItem::find($request->itemIdsHall[$i]);
                    $r->event_hall_opt_id = $request->idsOptHall[$i];
                    $r->comission = $request->comissionsHall[$i];
                    $r->value = $request->valuesHall[$i];
                    $r->comment = $request->commentsHall[$i];
                }

                ProviderBudgetItem::create([
                    'event_hall_opt_id'  => $request->idsOptHall[$i],
                    'comission'  => $request->comissionsHall[$i],
                    'value'  => $request->valuesHall[$i],
                    'comment'  => $request->commentsHall[$i],
                    'provider_budget_id' => $budget->id,
                ]);
            }
        } catch (Exception $e) {
            return redirect()->back()->with('flash', ['message' => 'Aconteceu um erro ao salvar: ' . $e->getMessage(), 'type' => 'error', 'show' => true]);
        }

        $event = Event::with('hotelOperator')->find($request->eventId);
        $data = [
            'body' => '<p style="font-size: 16px; line-height: 1.4em; color: #333; font-family: Arial, sans-serif;">
            Prezado(a),<br><br>O fornecedor já preencheu o formulário do link que foi enviado. Para aprovar, entre em sua area do sistema ou acesse o seguinte link:
          </p>
          <a href="' . route('event-list') . '" class="btn btn-sm btn-primary" style="display: inline-block; padding: 10px 20px; border-radius: 5px; color: #fff; background-color: #007bff; border-color: #007bff; text-decoration: none;">Aprovar Orçamento</a>
          <p style="font-size: 12px; margin-top: 10px;">Se o botão não aparecer, copie e cole esta URL: <a href="' . route('event-list')  . '" style="text-decoration: underline; color: #007bff;">' . route('event-list') . '</a></p>
          ',
            'hasAttachment' => false,
            'signature' => '',
            'subject' => "Orçamento de hotel foi preenchido"
        ];
        $send = Mail::to($event->hotelOperator->email);

        $send->send(new PdfEmail(null, '', $data, "Orçamento de hotel foi preenchido"));

        return redirect()->back()->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success', 'show' => true]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function prove(Request $request)
    {
        if (!Gate::allows('save_budget', $request->user)) {
            return redirect()->back()->with('flash', ['message' => 'Você não tem autorização para aprovar esse orçamento!', 'type' => 'error', 'show' => true]);
        }

        try {

            if ($request->id > 0) {

                if ($request->decision == 1) {

                    $budget = ProviderBudget::with(['providerBudgetItems'])->find($request->id);


                    if ($budget->event_hotel_id > 0) {

                        $eventHotel = EventHotel::find($budget->event_hotel_id);

                        $eventHotel->iss_percent = $budget->iss_fee_hotel;
                        $eventHotel->iva_percent = $budget->iva_fee_hotel;
                        $eventHotel->service_percent = $budget->hosting_fee_hotel;
                        $eventHotel->save();
                    }

                    if ($budget->event_ab_id > 0) {

                        $eventPro = EventAB::find($budget->event_ab_id);

                        $eventPro->iss_percent = $budget->iss_fee_ab;
                        $eventPro->iva_percent = $budget->iva_fee_ab;
                        $eventPro->service_percent = $budget->hosting_fee_ab;
                        $eventPro->save();
                    }

                    if ($budget->event_add_id > 0) {

                        $eventPro = EventAdd::find($budget->event_add_id);

                        $eventPro->iss_percent = $budget->iss_fee_add;
                        $eventPro->iva_percent = $budget->iva_fee_add;
                        $eventPro->service_percent = $budget->hosting_fee_add;
                        $eventPro->save();
                    }

                    if ($budget->event_hall_id > 0) {

                        $eventPro = EventHall::find($budget->event_hall_id);

                        $eventPro->iss_percent = $budget->iss_fee_hall;
                        $eventPro->iva_percent = $budget->iva_fee_hall;
                        $eventPro->service_percent = $budget->hosting_fee_hall;
                        $eventPro->save();
                    }

                    foreach ($budget->providerBudgetItems as $item) {

                        if ($item->event_hotel_opt_id > 0) {
                            $opt = EventHotelOpt::find($item->event_hotel_opt_id);

                            $opt->kickback = $item->comission;
                            $opt->received_proposal = $item->value;

                            $opt->save();
                        }

                        if ($item->event_ab_opt_id > 0) {
                            $opt = EventABOpt::find($item->event_ab_opt_id);

                            $opt->kickback = $item->comission;
                            $opt->received_proposal = $item->value;

                            $opt->save();
                        }

                        if ($item->event_hall_opt_id > 0) {
                            $opt = EventHallOpt::find($item->event_hall_opt_id);

                            $opt->kickback = $item->comission;
                            $opt->received_proposal = $item->value;

                            $opt->save();
                        }

                        if ($item->event_add_opt_id > 0) {
                            $opt = EventAddOpt::find($item->event_add_opt_id);

                            $opt->kickback = $item->comission;
                            $opt->received_proposal = $item->value;

                            $opt->save();
                        }
                    }
                }

                $budget->evaluated = true;
                $budget->approved = $request->decision == 1;
                $budget->user_id = $request->user;
                $budget->approval_date = new DateTime('now');

                $budget->save();
            }
        } catch (Exception $e) {
            return redirect()->back()->with('flash', ['message' => 'Aconteceu um erro ao salvar: ' . $e->getMessage(), 'type' => 'error', 'show' => true]);
        }

        return redirect()->back()->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success', 'show' => true]);
    }

    private function createPDF(array $paramters, int $type)
    {
        $budgetions = new Options();
        $budgetions->set('isRemoteEnabled', true);

        $path = base_path('public');

        $budgetions->set('chroot', $path);

        $pdf = new Dompdf($budgetions);

        $html = "";
        switch ($type) {
            case 1:
                $html = view('budgetPdf', [
                    'event' => $paramters['eventBank'],
                    'provider' => $paramters['providerBank']
                ])->render();

                break;
            default:
                $html = "<div class=\"text-truncate\">Sem Conteudo a ser apresentado</div>";
                break;
        }
        // return $html;
        // Carregue o HTML no Dompdf
        $pdf->loadHtml($html);

        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }
}
