<?php

namespace App\Livewire\HotelBooking;

use App\Models\Customer;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelRoom;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;

class EditBookingForm extends Component
{
    use LivewireAlert;

    public $bookingId;
    public $hotels;
    public $customers;
    public $availableRooms;

    // Form fields
    public $hotel_id;
    public $customer_id;
    public $checkin_date;
    public $checkin_time = '14:00';
    public $checkout_date;
    public $checkout_time = '12:00';
    public $status = 'pending';
    public $notes;

    // Room management
    public $rooms = [];
    public $selectedRoom;
    public $noOfPeople = 1;

    // Modal
    public $showAddCustomerModal = false;

    public function mount($bookingId)
    {
        $this->bookingId = $bookingId;

        $booking = HotelBooking::with('bookingRooms.room.floor')->findOrFail($bookingId);

        // Load booking data
        $this->hotel_id = $booking->hotel_id;
        $this->customer_id = $booking->customer_id;
        $this->checkin_date = $booking->checkin_date->format('Y-m-d');
        $this->checkin_time = $booking->checkin_time->format('H:i');
        $this->checkout_date = $booking->checkout_date->format('Y-m-d');
        $this->checkout_time = $booking->checkout_time->format('H:i');
        $this->status = $booking->status;
        $this->notes = $booking->notes;

        // Load existing rooms
        foreach ($booking->bookingRooms as $bookingRoom) {
            $this->rooms[] = [
                'room_id' => $bookingRoom->room_id,
                'room_name' => $bookingRoom->room->name,
                'floor_name' => $bookingRoom->room->floor->name,
                'no_of_people' => $bookingRoom->no_of_people,
                'price' => $bookingRoom->price,
            ];
        }

        $this->hotels = Hotel::where('is_active', true)->orderBy('name')->get();
        $this->customers = Customer::orderBy('name')->get();
        $this->loadAvailableRooms();
    }

    #[On('customerAdded')]
    public function customerAdded($customerId)
    {
        $this->customers = Customer::orderBy('name')->get();
        $this->customer_id = $customerId;
        $this->showAddCustomerModal = false;

        $this->alert('success', __('Customer added successfully'), [
            'toast' => true,
            'position' => 'top-end',
        ]);
    }

    public function updatedHotelId()
    {
        if ($this->hotel_id) {
            $this->loadAvailableRooms();
        }
    }

    public function updatedCheckinDate()
    {
        $this->loadAvailableRooms();
    }

    public function updatedCheckoutDate()
    {
        $this->loadAvailableRooms();
    }

    public function loadAvailableRooms()
    {
        if (!$this->hotel_id || !$this->checkin_date || !$this->checkout_date) {
            $this->availableRooms = collect();
            return;
        }

        // Get all rooms for the hotel
        $allRooms = HotelRoom::where('hotel_id', $this->hotel_id)
            ->with('floor')
            ->where('is_active', true)
            ->get();

        // Get booked room IDs for the date range (excluding current booking)
        $bookedRoomIds = HotelBooking::where('hotel_id', $this->hotel_id)
            ->where('id', '!=', $this->bookingId)
            ->whereIn('status', ['confirmed', 'checked-in'])
            ->where(function ($query) {
                $query->whereBetween('checkin_date', [$this->checkin_date, $this->checkout_date])
                    ->orWhereBetween('checkout_date', [$this->checkin_date, $this->checkout_date])
                    ->orWhere(function ($q) {
                        $q->where('checkin_date', '<=', $this->checkin_date)
                          ->where('checkout_date', '>=', $this->checkout_date);
                    });
            })
            ->with('bookingRooms')
            ->get()
            ->pluck('bookingRooms')
            ->flatten()
            ->pluck('room_id')
            ->unique()
            ->toArray();

        // Filter available rooms (exclude booked rooms but include current booking's rooms)
        $currentRoomIds = collect($this->rooms)->pluck('room_id')->toArray();
        $this->availableRooms = $allRooms->filter(function ($room) use ($bookedRoomIds, $currentRoomIds) {
            return !in_array($room->id, $bookedRoomIds) || in_array($room->id, $currentRoomIds);
        });
    }

    public function addRoom()
    {
        $this->validate([
            'selectedRoom' => 'required',
            'noOfPeople' => 'required|integer|min:1',
        ]);

        $room = HotelRoom::with('floor')->find($this->selectedRoom);

        if (!$room) {
            return;
        }

        // Check if room already added
        if (collect($this->rooms)->contains('room_id', $room->id)) {
            $this->alert('warning', __('Room already added'), [
                'toast' => true,
                'position' => 'top-end',
            ]);
            return;
        }

        $this->rooms[] = [
            'room_id' => $room->id,
            'room_name' => $room->name,
            'floor_name' => $room->floor->name,
            'no_of_people' => $this->noOfPeople,
            'price' => $room->price ?? 0,
        ];

        // Reset fields
        $this->selectedRoom = null;
        $this->noOfPeople = 1;
    }

    public function removeRoom($index)
    {
        unset($this->rooms[$index]);
        $this->rooms = array_values($this->rooms);
    }

    public function getTotalPriceProperty()
    {
        return collect($this->rooms)->sum('price');
    }

    public function submitForm()
    {
        $this->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'customer_id' => 'required|exists:customers,id',
            'checkin_date' => 'required|date',
            'checkin_time' => 'required',
            'checkout_date' => 'required|date|after:checkin_date',
            'checkout_time' => 'required',
            'status' => 'required|in:pending,confirmed,checked-in,checked-out,cancelled',
            'rooms' => 'required|array|min:1',
        ]);

        // Update booking
        $booking = HotelBooking::findOrFail($this->bookingId);
        $booking->update([
            'hotel_id' => $this->hotel_id,
            'customer_id' => $this->customer_id,
            'checkin_date' => $this->checkin_date,
            'checkin_time' => $this->checkin_time,
            'checkout_date' => $this->checkout_date,
            'checkout_time' => $this->checkout_time,
            'status' => $this->status,
            'notes' => $this->notes,
            'price' => $this->totalPrice,
        ]);

        // Delete existing booking rooms and create new ones
        $booking->bookingRooms()->delete();

        foreach ($this->rooms as $room) {
            $booking->bookingRooms()->create([
                'room_id' => $room['room_id'],
                'no_of_people' => $room['no_of_people'],
                'price' => $room['price'],
            ]);
        }

        $this->alert('success', __('Booking updated successfully'), [
            'toast' => true,
            'position' => 'top-end',
        ]);

        return redirect()->route('hotel-bookings.index');
    }

    public function render()
    {
        return view('livewire.hotel-booking.edit-booking-form');
    }
}
