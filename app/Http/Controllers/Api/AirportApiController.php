<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AirfareAirport;
use Illuminate\Http\Request;

class AirportApiController extends Controller
{
    /**
     * Search airports for autocomplete by IATA, name, or city.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $term = $request->get('term', '');

        $airports = AirfareAirport::search($term)
            ->where('active', true)
            ->orderBy('iata_code', 'asc')
            ->limit(30)
            ->get();

        return response()->json($airports);
    }
}
