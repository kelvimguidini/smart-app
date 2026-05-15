<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PdfEmail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;

class StatusHistoryController extends Controller
{
    protected $statusHistoryRepository;
    protected $userRepository;

    public function __construct(
        StatusHistoryRepositoryInterface $statusHistoryRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->statusHistoryRepository = $statusHistoryRepository;
        $this->userRepository = $userRepository;
    }

    public function listHistory(Request $request)
    {
        if (!Gate::allows('status_level_1') && !Gate::allows('status_level_2')) abort(403);

        $history = $this->statusHistoryRepository->getByTableAndId($request->table, $request->table_id);
        return response()->json($history);
    }

    public function statusStore(Request $request)
    {
        if (!Gate::allows('status_level_1') && !Gate::allows('status_level_2')) abort(403);

        try {
            $this->statusHistoryRepository->create([
                'status' => $request->status_hotel,
                'user_id' => Auth::user()->id,
                'observation' => $request->observation_hotel,
                'table' => $request->table,
                'table_id' => $request->table_id
            ]);

            $this->notify($request);
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->back()->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function sendMail(Request $request)
    {
        if (!Gate::allows('status_level_1') && !Gate::allows('status_level_2')) abort(403);

        try {
            $this->notify($request);
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->back()->with('flash', ['message' => 'Email enviado!', 'type' => 'success']);
    }

    private function notify(Request $request)
    {
        if ($request->notify) {
            $sub = "Atualização da situação de hotel/evento";
            $user = $this->userRepository->find(Auth::user()->id);
            $emailData = [
                'body' => $request->messageLink ? urldecode($request->messageLink) : "",
                'hasAttachment' => false,
                'signature' => $user->signature ?? "",
                'subject' => $sub
            ];
            
            $send = Mail::to(explode(";", $request->emailsLink));
            if ($request->copyMeLink == "true") $send->cc($user->email);
            
            $send->send(new PdfEmail(null, '', $emailData, $sub));

            DB::table('email_log')->insert([
                'provider_id' => $request->table_id,
                'sender_id' => $user->id,
                'body' => urldecode($request->message ?? ''),
                'to' => $request->emailsLink,
                'type' => 'status'
            ]);
        }
    }
}
