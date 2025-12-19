<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HotelController extends Controller
{
    /**
     * Display a listing of the hotels.
     */
    public function index()
    {
        return view('hotels.index');
    }

    /**
     * Show the form for creating a new hotel.
     */
    public function create()
    {
        return view('hotels.create');
    }

    /**
     * Show the form for editing the specified hotel.
     */
    public function edit($id)
    {
        return view('hotels.edit', compact('id'));
    }
}
