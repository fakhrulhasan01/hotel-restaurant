<?php

namespace App\Livewire\ConfigureHotel;

use App\Models\Hotel;
use Livewire\Component;
use Livewire\Attributes\On;

class ConfigureHotel extends Component
{
    public $selectedHotelId;
    public $activeTab = 'floors';

    public function mount()
    {
        // Set default hotel as selected
        $defaultHotel = Hotel::where('is_default', true)->first();

        if ($defaultHotel) {
            $this->selectedHotelId = $defaultHotel->id;
        } else {
            // If no default, select first active hotel
            $firstHotel = Hotel::where('is_active', true)->first();
            $this->selectedHotelId = $firstHotel?->id;
        }
    }

    public function updatedSelectedHotelId()
    {
        $this->dispatch('hotelChanged', hotelId: $this->selectedHotelId);
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $hotels = Hotel::where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return view('livewire.configure-hotel.configure-hotel', [
            'hotels' => $hotels
        ]);
    }
}
