<?php

namespace App\Http\Controllers\Api;

use App\Domains\Shared\Services\CityServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CityApiController extends Controller
{
    protected $cityService;

    public function __construct(CityServiceInterface $cityService)
    {
        $this->cityService = $cityService;
    }

    /**
     * Search cities for autocomplete
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $term = $request->get('term', '');
        
        $cities = $this->cityService->search($term);

        return response()->json($cities);
    }
}
