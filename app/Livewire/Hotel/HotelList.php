<?php

namespace App\Livewire\Hotel;

use App\Models\Hotel;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Storage;

class HotelList extends Component
{
    use LivewireAlert, WithFileUploads;

    public $search = '';
    public $showAddHotel = false;
    public $showEditHotel = false;
    public $editingHotelId = null;

    // Form fields
    public $name;
    public $address;
    public $is_default = false;
    public $logo;
    public $existing_logo;
    public $checkin_time = '14:00';
    public $checkout_time = '12:00';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'nullable|string',
        'is_default' => 'boolean',
        'logo' => 'nullable|image|max:2048',
        'checkin_time' => 'required|date_format:H:i',
        'checkout_time' => 'required|date_format:H:i',
        'is_active' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'Hotel name is required',
        'name.unique' => 'A hotel with this name already exists',
        'checkin_time.required' => 'Check-in time is required',
        'checkout_time.required' => 'Check-out time is required',
        'logo.image' => 'The logo must be an image file',
        'logo.max' => 'The logo may not be greater than 2MB',
    ];

    protected function rules()
    {
        $rules = [
            'address' => 'nullable|string',
            'is_default' => 'boolean',
            'logo' => 'nullable|image|max:2048',
            'checkin_time' => 'required|date_format:H:i',
            'checkout_time' => 'required|date_format:H:i',
            'is_active' => 'boolean',
        ];

        if ($this->editingHotelId) {
            $rules['name'] = 'required|string|max:255|unique:hotels,name,' . $this->editingHotelId;
        } else {
            $rules['name'] = 'required|string|max:255|unique:hotels,name';
        }

        return $rules;
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddHotel = true;
    }

    public function closeAddModal()
    {
        $this->resetForm();
        $this->showAddHotel = false;
    }

    public function openEditModal($hotelId)
    {
        $hotel = Hotel::findOrFail($hotelId);

        $this->editingHotelId = $hotel->id;
        $this->name = $hotel->name;
        $this->address = $hotel->address;
        $this->is_default = $hotel->is_default;
        $this->existing_logo = $hotel->logo;
        $this->checkin_time = $hotel->checkin_time ? $hotel->checkin_time->format('H:i') : '14:00';
        $this->checkout_time = $hotel->checkout_time ? $hotel->checkout_time->format('H:i') : '12:00';
        $this->is_active = $hotel->is_active;

        $this->showEditHotel = true;
    }

    public function closeEditModal()
    {
        $this->resetForm();
        $this->showEditHotel = false;
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'address' => $this->address,
                'is_default' => $this->is_default,
                'checkin_time' => $this->checkin_time,
                'checkout_time' => $this->checkout_time,
                'is_active' => $this->is_active,
            ];

            // Handle logo upload
            if ($this->logo) {
                $logoName = time() . '_' . $this->logo->getClientOriginalName();
                $this->logo->storeAs('hotel-logos', $logoName, 'public');
                $data['logo'] = $logoName;
            }

            // If setting as default, remove default from others
            if ($this->is_default) {
                Hotel::where('is_default', true)->update(['is_default' => false]);
            }

            Hotel::create($data);

            $this->alert('success', __('modules.hotel.hotelCreated'), [
                'toast' => true,
                'position' => 'top-end',
            ]);

            $this->closeAddModal();
            $this->dispatch('hotelUpdated');
        } catch (\Exception $e) {
            $this->alert('error', __('modules.hotel.errorCreating') . ': ' . $e->getMessage());
        }
    }

    public function update()
    {
        $this->validate();

        try {
            $hotel = Hotel::findOrFail($this->editingHotelId);

            $data = [
                'name' => $this->name,
                'address' => $this->address,
                'is_default' => $this->is_default,
                'checkin_time' => $this->checkin_time,
                'checkout_time' => $this->checkout_time,
                'is_active' => $this->is_active,
            ];

            // Handle logo upload
            if ($this->logo) {
                // Delete old logo
                if ($hotel->logo) {
                    Storage::disk('public')->delete('hotel-logos/' . $hotel->logo);
                }

                $logoName = time() . '_' . $this->logo->getClientOriginalName();
                $this->logo->storeAs('hotel-logos', $logoName, 'public');
                $data['logo'] = $logoName;
            }

            // If setting as default, remove default from others
            if ($this->is_default && !$hotel->is_default) {
                Hotel::where('is_default', true)->update(['is_default' => false]);
            }

            $hotel->update($data);

            $this->alert('success', __('modules.hotel.hotelUpdated'), [
                'toast' => true,
                'position' => 'top-end',
            ]);

            $this->closeEditModal();
            $this->dispatch('hotelUpdated');
        } catch (\Exception $e) {
            $this->alert('error', __('modules.hotel.errorUpdating') . ': ' . $e->getMessage());
        }
    }

    #[On('deleteHotel')]
    public function delete($hotelId)
    {
        try {
            $hotel = Hotel::findOrFail($hotelId);

            // Delete logo
            if ($hotel->logo) {
                Storage::disk('public')->delete('hotel-logos/' . $hotel->logo);
            }

            $hotel->delete();

            $this->alert('success', __('modules.hotel.hotelDeleted'), [
                'toast' => true,
                'position' => 'top-end',
            ]);

            $this->dispatch('hotelUpdated');
        } catch (\Exception $e) {
            $this->alert('error', __('modules.hotel.errorDeleting') . ': ' . $e->getMessage());
        }
    }

    public function confirmDelete($hotelId)
    {
        $this->alert('warning', __('modules.hotel.confirmDelete'), [
            'showConfirmButton' => true,
            'confirmButtonText' => __('app.yes'),
            'showCancelButton' => true,
            'cancelButtonText' => __('app.no'),
            'onConfirmed' => 'deleteHotel',
            'data' => [
                'hotelId' => $hotelId
            ],
        ]);
    }

    private function resetForm()
    {
        $this->reset([
            'name',
            'address',
            'is_default',
            'logo',
            'existing_logo',
            'checkin_time',
            'checkout_time',
            'is_active',
            'editingHotelId',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        $hotels = Hotel::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('address', 'like', '%' . $this->search . '%');
            })
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.hotel.hotel-list', [
            'hotels' => $hotels
        ]);
    }
}
