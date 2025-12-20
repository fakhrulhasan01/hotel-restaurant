<?php

namespace App\Livewire\ConfigureHotel;

use App\Models\HotelRoom;
use App\Models\HotelFloor;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Storage;

class RoomsTab extends Component
{
    use LivewireAlert, WithFileUploads;

    public $hotelId;
    public $search = '';
    public $showAddRoom = false;
    public $showEditRoom = false;
    public $editingRoomId = null;

    // Form fields
    public $name;
    public $hotel_floor_id;
    public $size;
    public $no_of_beds = 1;
    public $pictures = [];
    public $existing_pictures = [];
    public $price = 0;
    public $sale_price;
    public $is_active = true;
    public $sort_order = 0;

    protected $rules = [
        'name' => 'required|string|max:255',
        'hotel_floor_id' => 'required|exists:hotel_floors,id',
        'size' => 'nullable|string|max:100',
        'no_of_beds' => 'required|integer|min:1',
        'pictures.*' => 'nullable|image|max:2048',
        'price' => 'required|numeric|min:0',
        'sale_price' => 'nullable|numeric|min:0',
        'is_active' => 'boolean',
        'sort_order' => 'integer|min:0',
    ];

    protected $messages = [
        'name.required' => 'Room name is required',
        'name.unique' => 'A room with this name already exists on this floor',
        'hotel_floor_id.required' => 'Floor is required',
        'hotel_floor_id.exists' => 'Selected floor does not exist',
        'no_of_beds.required' => 'Number of beds is required',
        'no_of_beds.min' => 'At least 1 bed is required',
        'price.required' => 'Price is required',
        'price.min' => 'Price must be at least 0',
        'pictures.*.image' => 'All files must be images',
        'pictures.*.max' => 'Images may not be greater than 2MB',
    ];

    protected function rules()
    {
        $rules = [
            'hotel_floor_id' => 'required|exists:hotel_floors,id',
            'size' => 'nullable|string|max:100',
            'no_of_beds' => 'required|integer|min:1',
            'pictures.*' => 'nullable|image|max:2048',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];

        if ($this->editingRoomId) {
            $rules['name'] = 'required|string|max:255|unique:hotel_rooms,name,' . $this->editingRoomId . ',id,hotel_id,' . $this->hotelId . ',hotel_floor_id,' . $this->hotel_floor_id;
        } else {
            $rules['name'] = 'required|string|max:255|unique:hotel_rooms,name,NULL,id,hotel_id,' . $this->hotelId . ',hotel_floor_id,' . $this->hotel_floor_id;
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
        $this->showAddRoom = true;
    }

    public function closeAddModal()
    {
        $this->resetForm();
        $this->showAddRoom = false;
    }

    public function openEditModal($roomId)
    {
        $room = HotelRoom::with('floor')->findOrFail($roomId);

        $this->editingRoomId = $room->id;
        $this->name = $room->name;
        $this->hotel_floor_id = $room->hotel_floor_id;
        $this->size = $room->size;
        $this->no_of_beds = $room->no_of_beds;
        $this->existing_pictures = $room->pictures ?? [];
        $this->price = $room->price;
        $this->sale_price = $room->sale_price;
        $this->is_active = $room->is_active;
        $this->sort_order = $room->sort_order;

        $this->showEditRoom = true;
    }

    public function closeEditModal()
    {
        $this->resetForm();
        $this->showEditRoom = false;
    }

    public function removeExistingPicture($index)
    {
        if (isset($this->existing_pictures[$index])) {
            unset($this->existing_pictures[$index]);
            $this->existing_pictures = array_values($this->existing_pictures);
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'hotel_id' => $this->hotelId,
                'hotel_floor_id' => $this->hotel_floor_id,
                'name' => $this->name,
                'size' => $this->size,
                'no_of_beds' => $this->no_of_beds,
                'price' => $this->price,
                'sale_price' => $this->sale_price,
                'is_active' => $this->is_active,
                'sort_order' => $this->sort_order,
            ];

            // Handle multiple pictures upload
            if (!empty($this->pictures)) {
                $uploadedPictures = [];
                foreach ($this->pictures as $picture) {
                    $pictureName = time() . '_' . uniqid() . '_' . $picture->getClientOriginalName();
                    $picture->storeAs('hotel-rooms', $pictureName);
                    $uploadedPictures[] = $pictureName;
                }
                $data['pictures'] = $uploadedPictures;
            }

            HotelRoom::create($data);

            $this->alert('success', __('modules.hotel.roomCreated'), [
                'toast' => true,
                'position' => 'top-end',
            ]);

            $this->closeAddModal();
            $this->dispatch('roomUpdated');
        } catch (\Exception $e) {
            $this->alert('error', __('modules.hotel.errorCreating') . ': ' . $e->getMessage());
        }
    }

    public function update()
    {
        $this->validate();

        try {
            $room = HotelRoom::findOrFail($this->editingRoomId);

            $data = [
                'hotel_floor_id' => $this->hotel_floor_id,
                'name' => $this->name,
                'size' => $this->size,
                'no_of_beds' => $this->no_of_beds,
                'price' => $this->price,
                'sale_price' => $this->sale_price,
                'is_active' => $this->is_active,
                'sort_order' => $this->sort_order,
            ];

            // Merge existing and new pictures
            $allPictures = $this->existing_pictures;

            // Handle new pictures upload
            if (!empty($this->pictures)) {
                foreach ($this->pictures as $picture) {
                    $pictureName = time() . '_' . uniqid() . '_' . $picture->getClientOriginalName();
                    $picture->storeAs('hotel-rooms', $pictureName);
                    $allPictures[] = $pictureName;
                }
            }

            // Delete removed pictures
            if ($room->pictures) {
                $removedPictures = array_diff($room->pictures, $this->existing_pictures);
                foreach ($removedPictures as $removedPicture) {
                    Storage::delete('hotel-rooms/' . $removedPicture);
                }
            }

            $data['pictures'] = $allPictures;

            $room->update($data);

            $this->alert('success', __('modules.hotel.roomUpdated'), [
                'toast' => true,
                'position' => 'top-end',
            ]);

            $this->closeEditModal();
            $this->dispatch('roomUpdated');
        } catch (\Exception $e) {
            $this->alert('error', __('modules.hotel.errorUpdating') . ': ' . $e->getMessage());
        }
    }

    #[On('deleteRoom')]
    public function deleteRoom($data)
    {
        $roomId = $data['roomId'] ?? null;

        if (!$roomId) {
            $this->alert('error', __('app.somethingWentWrong'));
            return;
        }

        try {
            $room = HotelRoom::findOrFail($roomId);

            // Delete pictures
            if ($room->pictures) {
                foreach ($room->pictures as $picture) {
                    Storage::delete('hotel-rooms/' . $picture);
                }
            }

            $room->delete();

            $this->alert('success', __('modules.hotel.roomDeleted'), [
                'toast' => true,
                'position' => 'top-end',
            ]);

            $this->dispatch('roomUpdated');
        } catch (\Exception $e) {
            $this->alert('error', __('modules.hotel.errorDeleting') . ': ' . $e->getMessage());
        }
    }

    public function confirmDelete($roomId)
    {
        $this->alert('warning', __('modules.hotel.confirmDeleteRoom'), [
            'showConfirmButton' => true,
            'confirmButtonText' => __('app.yes'),
            'showCancelButton' => true,
            'cancelButtonText' => __('app.no'),
            'onConfirmed' => 'deleteRoom',
            'data' => [
                'roomId' => $roomId
            ],
        ]);
    }

    private function resetForm()
    {
        $this->reset([
            'name',
            'hotel_floor_id',
            'size',
            'no_of_beds',
            'pictures',
            'existing_pictures',
            'price',
            'sale_price',
            'is_active',
            'sort_order',
            'editingRoomId',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        $floors = HotelFloor::where('hotel_id', $this->hotelId)
            ->orderBy('sort_order')
            ->get();

        $rooms = HotelRoom::query()
            ->with('floor')
            ->where('hotel_id', $this->hotelId)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('size', 'like', '%' . $this->search . '%');
            })
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.configure-hotel.rooms-tab', [
            'rooms' => $rooms,
            'floors' => $floors,
        ]);
    }
}
