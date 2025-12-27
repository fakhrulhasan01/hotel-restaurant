<?php

namespace App\Livewire\HotelBooking;

use App\Models\Customer;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelFloor;
use App\Models\HotelRoom;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;

class BookingForm extends Component
{
    use LivewireAlert;

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

    public function mount()
    {
        $this->hotels = Hotel::where('is_active', true)->orderBy('name')->get();
        $this->customers = Customer::orderBy('name')->get();
        $this->availableRooms = collect();
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

    public function updatedSelectedFloor()
    {
        $this->selectedRoom = null;
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

        // Get booked room IDs for the date range
        $bookedRoomIds = HotelBooking::where('hotel_id', $this->hotel_id)
            ->whereIn('status', ['pending', 'confirmed', 'checked-in'])
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

        // Filter available rooms
        $this->availableRooms = $allRooms->whereNotIn('id', $bookedRoomIds);
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
        $this->selectedFloor = null;
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

        // Check for room conflicts
        $roomIds = collect($this->rooms)->pluck('room_id')->toArray();

        $conflictingBookings = HotelBooking::where('hotel_id', $this->hotel_id)
            ->whereIn('status', ['pending', 'confirmed', 'checked-in'])
            ->where(function ($query) {
                $query->whereBetween('checkin_date', [$this->checkin_date, $this->checkout_date])
                    ->orWhereBetween('checkout_date', [$this->checkin_date, $this->checkout_date])
                    ->orWhere(function ($q) {
                        $q->where('checkin_date', '<=', $this->checkin_date)
                          ->where('checkout_date', '>=', $this->checkout_date);
                    });
            })
            ->whereHas('bookingRooms', function ($q) use ($roomIds) {
                $q->whereIn('room_id', $roomIds);
            })
            ->with(['bookingRooms.room.floor'])
            ->get();

        if ($conflictingBookings->isNotEmpty()) {
            $conflictDetails = [];
            foreach ($conflictingBookings as $booking) {
                foreach ($booking->bookingRooms as $bookingRoom) {
                    if (in_array($bookingRoom->room_id, $roomIds)) {
                        $conflictDetails[] = $bookingRoom->room->floor->name . ' > ' . $bookingRoom->room->name . ' (Booked: ' . date('M d, Y', strtotime($booking->checkin_date)) . ' - ' . date('M d, Y', strtotime($booking->checkout_date)) . ')';
                    }
                }
            }

            $this->alert('error', 'Room(s) already booked for selected dates: ' . implode(', ', $conflictDetails), [
                'toast' => false,
                'position' => 'center',
                'timer' => 5000,
            ]);

            return;
        }

        // Create booking
        $booking = HotelBooking::create([
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

        // Create booking rooms
        foreach ($this->rooms as $room) {
            $booking->bookingRooms()->create([
                'room_id' => $room['room_id'],
                'no_of_people' => $room['no_of_people'],
                'price' => $room['price'],
            ]);
        }

        $this->alert('success', __('Booking created successfully'), [
            'toast' => true,
            'position' => 'top-end',
        ]);

        return redirect()->route('hotel-bookings.index');
    }

    public function render()
    {
        return view('livewire.hotel-booking.booking-form');
    }
}
