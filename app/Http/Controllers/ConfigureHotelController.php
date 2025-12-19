<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfigureHotelController extends Controller
{
    /**
     * Display the hotel configuration page.
     */
    public function index()
    {
        return view('configure-hotel.index');
    }
}
