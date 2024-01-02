<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PdfEmail;
use App\Models\StatusHistory;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class StatusHistoryController extends Controller
{
    public function listHistory(Request $request)
    {
        if (!Gate::allows('status_level_1') && !Gate::allows('status_level_2')) {
            abort(403);
        }

        $table = $request->table;
        $tableId = $request->table_id;

        $history = StatusHistory::with('user')->where('table', $table)
            ->where('table_id', $tableId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($history);
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function statusStore(Request $request)
    {
        if (!Gate::allows('status_level_1') && !Gate::allows('status_level_2')) {
            abort(403);
        }

        try {

            $status = StatusHistory::create([
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

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendMail(Request $request)
    {
        if (!Gate::allows('status_level_1') && !Gate::allows('status_level_2')) {
            abort(403);
        }

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
            $user = User::find(Auth::user()->id);
            $data = [
                'body' => $request->messageLink != null ? urldecode($request->messageLink) : "",
                'hasAttachment' => true,
                'signature' => $user->signature != null ? $user->signature : "",
                'subject' => $sub
            ];
            $send = Mail::to(explode(";", $request->emailsLink));

            if ($request->copyMeLink == "true") {
                $send->cc($user->email);
            }

            $send->send(new PdfEmail(null, '', $data, $sub));

            DB::table('email_log')->insert(
                array(
                    'provider_id' => $request->table_id,
                    'sender_id' => $user->id,
                    'body' => urldecode($request->message),
                    'to' => $request->emailsLink,
                    'type' => 'status'
                )
            );
        }
    }
}
