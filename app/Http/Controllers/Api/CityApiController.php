<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CityApiController extends Controller
{
    /**
     * Search cities for autocomplete
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $term = $request->get('term', '');
        
        $query = City::withoutGlobalScope('active');
        
        if (!empty($term)) {
            $query->where('name', 'like', '%' . $term . '%')
                  ->orWhere('states', 'like', '%' . $term . '%');
        }

        // Limit results to 20 for performance
        $cities = $query->select('id', 'name', 'states', 'country')
                        ->orderBy('name', 'asc')
                        ->limit(20)
                        ->get();

        return response()->json($cities);
    }
}
