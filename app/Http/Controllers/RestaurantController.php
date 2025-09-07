<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RestaurantController extends Controller
{
    public function index()
    {
        return view('restaurants.index');
    }

    public function fetchRestaurants(Request $request)
    {
        $request->validate([
            'city' => 'required|string'
        ]);

        // Automatically append India to city query
        $cityQuery = trim($request->city) . ', India';

        // Step 1: Get city coordinates using Geoapify Geocoding API
        $geoResponse = Http::get("https://api.geoapify.com/v1/geocode/search", [
            'text' => $cityQuery,
            'format' => 'json',
            'apiKey' => env('GEOAPIFY_KEY')
        ]);

        $geoData = $geoResponse->json();

        // Safe check for 'results'
        if ($geoResponse->failed() || !isset($geoData['results']) || count($geoData['results']) == 0) {
            return back()->withErrors([
                'city' => 'Invalid city name or API did not return results. Try a major Indian city like Mumbai, Delhi, Bangalore.'
            ]);
        }

        $cityData = $geoData['results'][0];
        $lat = $cityData['lat'];
        $lon = $cityData['lon'];

        // Step 2: Fetch nearby restaurants using Geoapify Places API
        $placesResponse = Http::get("https://api.geoapify.com/v2/places", [
            'categories' => 'catering.restaurant',
            'filter' => "circle:$lon,$lat,5000", // 5km radius
            'limit' => 10,
            'apiKey' => env('GEOAPIFY_KEY')
        ]);

        $placesData = $placesResponse->json();

        // Map response safely
        $restaurants = collect($placesData['features'] ?? [])->map(function ($place) {
            return [
                'name' => $place['properties']['name'] ?? 'Unnamed Restaurant',
                'lat' => $place['properties']['lat'] ?? null,
                'lon' => $place['properties']['lon'] ?? null,
                'address' => $place['properties']['formatted'] ?? '',
                'phone' => $place['properties']['phone'] ?? '',
                'website' => $place['properties']['url'] ?? ''
            ];
        });

        return view('restaurants.index', [
            'restaurants' => $restaurants,
            'city' => $request->city,
            'lat' => $lat,
            'lon' => $lon
        ]);
    }
}
