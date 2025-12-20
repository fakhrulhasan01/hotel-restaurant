<?php

namespace App\Livewire\ConfigureHotel;

use App\Models\HotelFloor;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Storage;

class FloorsTab extends Component
{
    use LivewireAlert, WithFileUploads;

    public $hotelId;
    public $search = '';
    public $showAddFloor = false;
    public $showEditFloor = false;
    public $editingFloorId = null;

    // Form fields
    public $name;
    public $picture;
    public $existing_picture;
    public $sort_order = 0;

    protected $rules = [
        'name' => 'required|string|max:255',
        'picture' => 'nullable|image|max:2048',
        'sort_order' => 'integer|min:0',
    ];

    protected $messages = [
        'name.required' => 'Floor name is required',
        'name.unique' => 'A floor with this name already exists in this hotel',
        'picture.image' => 'The picture must be an image file',
        'picture.max' => 'The picture may not be greater than 2MB',
    ];

    protected function rules()
    {
        $rules = [
            'picture' => 'nullable|image|max:2048',
            'sort_order' => 'integer|min:0',
        ];

        if ($this->editingFloorId) {
            $rules['name'] = 'required|string|max:255|unique:hotel_floors,name,' . $this->editingFloorId . ',id,hotel_id,' . $this->hotelId;
        } else {
            $rules['name'] = 'required|string|max:255|unique:hotel_floors,name,NULL,id,hotel_id,' . $this->hotelId;
        }

        return $rules;
    }

    #[On('hotelChanged')]
    public function hotelChanged($hotelId)
    {
        $this->hotelId = $hotelId;
        $this->closeAddModal();
        $this->closeEditModal();
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddFloor = true;
    }

    public function closeAddModal()
    {
        $this->resetForm();
        $this->showAddFloor = false;
    }

    public function openEditModal($floorId)
    {
        $floor = HotelFloor::findOrFail($floorId);

        $this->editingFloorId = $floor->id;
        $this->name = $floor->name;
        $this->existing_picture = $floor->picture;
        $this->sort_order = $floor->sort_order;

        $this->showEditFloor = true;
    }

    public function closeEditModal()
    {
        $this->resetForm();
        $this->showEditFloor = false;
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'hotel_id' => $this->hotelId,
                'name' => $this->name,
                'sort_order' => $this->sort_order,
            ];

            // Handle picture upload
            if ($this->picture) {
                $pictureName = time() . '_' . $this->picture->getClientOriginalName();
                $this->picture->storeAs('hotel-floors', $pictureName);
                $data['picture'] = $pictureName;
            }

            HotelFloor::create($data);

            $this->alert('success', __('modules.hotel.floorCreated'), [
                'toast' => true,
                'position' => 'top-end',
            ]);

            $this->closeAddModal();
            $this->dispatch('floorUpdated');
        } catch (\Exception $e) {
            $this->alert('error', __('modules.hotel.errorCreating') . ': ' . $e->getMessage());
        }
    }

    public function update()
    {
        $this->validate();

        try {
            $floor = HotelFloor::findOrFail($this->editingFloorId);

            $data = [
                'name' => $this->name,
                'sort_order' => $this->sort_order,
            ];

            // Handle picture upload
            if ($this->picture) {
                // Delete old picture
                if ($floor->picture) {
                    Storage::delete('hotel-floors/' . $floor->picture);
                }

                $pictureName = time() . '_' . $this->picture->getClientOriginalName();
                $this->picture->storeAs('hotel-floors', $pictureName);
                $data['picture'] = $pictureName;
            }

            $floor->update($data);

            $this->alert('success', __('modules.hotel.floorUpdated'), [
                'toast' => true,
                'position' => 'top-end',
            ]);

            $this->closeEditModal();
            $this->dispatch('floorUpdated');
        } catch (\Exception $e) {
            $this->alert('error', __('modules.hotel.errorUpdating') . ': ' . $e->getMessage());
        }
    }

    #[On('deleteFloor')]
    public function delete($floorId)
    {
        try {
            $floor = HotelFloor::findOrFail($floorId);

            // Check if floor has rooms
            if ($floor->rooms()->count() > 0) {
                $this->alert('error', __('modules.hotel.floorHasRooms'));
                return;
            }

            // Delete picture
            if ($floor->picture) {
                Storage::delete('hotel-floors/' . $floor->picture);
            }

            $floor->delete();

            $this->alert('success', __('modules.hotel.floorDeleted'), [
                'toast' => true,
                'position' => 'top-end',
            ]);

            $this->dispatch('floorUpdated');
        } catch (\Exception $e) {
            $this->alert('error', __('modules.hotel.errorDeleting') . ': ' . $e->getMessage());
        }
    }

    public function confirmDelete($floorId)
    {
        $this->alert('warning', __('modules.hotel.confirmDeleteFloor'), [
            'showConfirmButton' => true,
            'confirmButtonText' => __('app.yes'),
            'showCancelButton' => true,
            'cancelButtonText' => __('app.no'),
            'onConfirmed' => 'deleteFloor',
            'data' => [
                'floorId' => $floorId
            ],
        ]);
    }

    private function resetForm()
    {
        $this->reset([
            'name',
            'picture',
            'existing_picture',
            'sort_order',
            'editingFloorId',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        $floors = HotelFloor::query()
            ->where('hotel_id', $this->hotelId)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.configure-hotel.floors-tab', [
            'floors' => $floors
        ]);
    }
}
