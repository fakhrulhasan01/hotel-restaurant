<?php

namespace App\Livewire\HotelBooking;

use App\Models\HotelBooking;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;

class BookingList extends Component
{
    use LivewireAlert, WithPagination, WithoutUrlPagination;

    public $search = '';
    public $filterStatus = 'all';
    public $bookingId;
    public $confirmDeleteModal = false;

    #[On('refreshBookings')]
    public function refreshBookings()
    {
        $this->render();
    }

    public function deleteBooking()
    {
        if ($this->bookingId) {
            HotelBooking::destroy($this->bookingId);

            $this->confirmDeleteModal = false;
            $this->bookingId = null;

            $this->alert('success', __('Booking deleted successfully'), [
                'toast' => true,
                'position' => 'top-end',
            ]);
        }
    }

    public function showDeleteConfirm($id)
    {
        $this->bookingId = $id;
        $this->confirmDeleteModal = true;
    }

    public function render()
    {
        $query = HotelBooking::with(['hotel', 'customer', 'bookingRooms.room.floor'])
            ->where(function ($q) {
                $q->whereHas('customer', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('phone', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('hotel', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhere('id', 'like', '%' . $this->search . '%');
            });

        if ($this->filterStatus != 'all') {
            $query->where('status', $this->filterStatus);
        }

        $bookings = $query->orderByDesc('id')->paginate(20);

        return view('livewire.hotel-booking.booking-list', compact('bookings'));
    }
}
