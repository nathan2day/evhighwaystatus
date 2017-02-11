<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ChargerRepository;

class HomeController extends Controller
{

    public function index(ChargerRepository $chargerRepository)
    {
        $providers = $chargerRepository->providers->all();
        $connectors = $chargerRepository->types->all();
        return view('home', compact('providers', 'connectors'));
    }

}
