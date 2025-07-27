<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q', '');
        $cities = City::with('country')
                     ->where('name', 'like', "%{$query}%")
                     ->take(100)
                     ->get(['id', 'name', 'country_id'])
                     ->map(function ($city) {
                         return [
                             'id' => $city->id,
                             'name' => $city->name,
                             'country' => $city->country?->name ?? '-'
                         ];
                     });
        return response()->json($cities);
    }
}
