<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use App\Models\Event;

class EventApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $events = Event::whereBetween('date', [
            $request->start_date,
            $request->end_date
        ])->get();

        // Aqui você pode montar o XML conforme o padrão que irá definir
        $xml = new \SimpleXMLElement('<events/>');
        foreach ($events as $event) {
            $eventXml = $xml->addChild('event');
            $eventXml->addChild('id', $event->id);
            $eventXml->addChild('name', $event->name);
            $eventXml->addChild('date', $event->date);
            // Adicione outros campos conforme necessário
        }

        return response($xml->asXML(), 200)
            ->header('Content-Type', 'application/xml');
    }
}
