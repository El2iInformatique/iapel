<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ClientConfigurationService;

class ClientConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

   public function getBiOptions($client)
    {
        return response()->json(
            ClientConfigurationService::getOptionsBI($client)
        );
    }

    public function updateBiOptions(Request $request, $client)
    {
        // return ClientConfigurationService::updateBiOptions($request, $client);
    }

    public function getBiCasesSupplementaires($client)
    {
        // return ClientConfigurationService::getBiCaseSupplementaires($client);
    }

    public function updateBiCasesSupplementaires(Request $request, $client)
    {
        // return ClientConfigurationService::updateBiCasesSupplementaires($request, $client);
    }

    public function getBiCerfa($client)
    {
        // $service = new \App\Http\Services\ClientConfigurationService();
        // return $service->getBiCerfa($client);
    }

    public function updateBiCerfa(Request $request, $client)
    {
        // $service = new \App\Http\Services\ClientConfigurationService();
        // return $service->updateBiCerfa($request, $client);
    }
}
