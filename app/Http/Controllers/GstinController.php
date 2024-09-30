<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GstinData;
use Illuminate\Support\Facades\Http;

class GstinController extends Controller
{
    public function index()
    {
        return view('gstin.index');
    }

    public function fetchAndStoreGstinData(Request $request)
    {
        // Get GSTIN from the request
        $gstin = $request->input('gstin');

        // API Key and URL
        $apiKey = '7b0389579ed3ea1e6a563a7ae64d58d5';
        $url = "http://sheet.gstincheck.co.in/check/{$apiKey}/{$gstin}";

        // Fetch data from the API
        $response = Http::get($url);

        // Check if the request was successful
        if ($response->ok()) {
            // Decode JSON response
            $data = $response->json();

            // Extract relevant data from the JSON response
            $gstinData = [
                'gstin' => $data['data']['gstin'],
                'trade_name' => $data['data']['tradeNam'],
                'registration_date' => $data['data']['rgdt'],
                'status' => $data['data']['sts'],
                'address' => $data['data']['pradr']['adr'],
                'state' => $data['data']['pradr']['addr']['stcd'],
                'district' => $data['data']['pradr']['addr']['dst'],
                'pincode' => $data['data']['pradr']['addr']['pncd'],
                'legal_name' => $data['data']['lgnm'],
                'business_type' => $data['data']['ctb'],
                'last_updated' => $data['data']['lstupdt'],
                'e_invoice_status' => $data['data']['einvoiceStatus'],
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Store data in the database
            GstinData::create($gstinData);

            // Return success message
        return 'GSTIN data stored successfully';
    } else {
        // Failed to fetch GSTIN data
        return 'Failed to fetch GSTIN data';
    }
    }
}
