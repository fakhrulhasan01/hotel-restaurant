@extends('layouts.app')

@section('content')
    @livewire('hotel-booking.edit-booking-form', ['bookingId' => $id])
@endsection
