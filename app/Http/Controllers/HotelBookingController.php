<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HotelBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('hotel-bookings.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('hotel-bookings.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('hotel-bookings.show', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('hotel-bookings.edit', compact('id'));
    }
}

