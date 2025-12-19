<div>
    <div class="mb-4 flex justify-between items-center">
        <div>
            <x-input type="text" placeholder="{{ __('modules.hotel.searchRooms') }}"
                wire:model.live.debounce.500ms="search" class="w-64" />
        </div>
        <x-button type="button" wire:click="openAddModal">
            @lang('modules.hotel.addRoom')
        </x-button>
    </div>

    <!-- Rooms Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-white">
                        @lang('modules.hotel.roomName')
                    </th>
                    <th scope="col" class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-white">
                        @lang('modules.hotel.floor')
                    </th>
                    <th scope="col" class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-white">
                        @lang('modules.hotel.details')
                    </th>
                    <th scope="col" class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-white">
                        @lang('modules.hotel.pricing')
                    </th>
                    <th scope="col" class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-white">
                        @lang('app.status')
                    </th>
                    <th scope="col" class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-white">
                        @lang('app.actions')
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800">
                @forelse($rooms as $room)
                <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                    <td class="p-4 text-sm font-normal text-gray-900 whitespace-nowrap dark:text-white">
                        <div class="flex items-center">
                            @if($room->pictures && count($room->pictures) > 0)
                                <img class="h-12 w-12 rounded-lg object-cover mr-3"
                                    src="{{ asset_url_local_s3('hotel-rooms/' . $room->pictures[0]) }}"
                                    alt="{{ $room->name }}">
                            @else
                                <div class="h-12 w-12 rounded-lg bg-gray-200 dark:bg-gray-600 mr-3 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="font-semibold">{{ $room->name }}</div>
                        </div>
                    </td>
                    <td class="p-4 text-sm font-normal text-gray-500 whitespace-nowrap dark:text-gray-400">
                        {{ $room->floor->name ?? '-' }}
                    </td>
                    <td class="p-4 text-sm font-normal text-gray-500 whitespace-nowrap dark:text-gray-400">
                        <div class="text-xs">
                            @if($room->size)
                            <div>@lang('modules.hotel.size'): {{ $room->size }}</div>
                            @endif
                            <div>@lang('modules.hotel.beds'): {{ $room->no_of_beds }}</div>
                            @if($room->pictures)
                            <div>@lang('modules.hotel.images'): {{ count($room->pictures) }}</div>
                            @endif
                        </div>
                    </td>
                    <td class="p-4 text-sm font-normal text-gray-900 whitespace-nowrap dark:text-white">
                        <div class="text-xs">
                            <div class="font-semibold">{{ global_currency_format($room->price) }}</div>
                            @if($room->sale_price)
                            <div class="text-green-600 dark:text-green-400">
                                @lang('modules.hotel.sale'): {{ global_currency_format($room->sale_price) }}
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="p-4 whitespace-nowrap">
                        @if($room->is_active)
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-300">
                            @lang('app.active')
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-300">
                            @lang('app.inactive')
                        </span>
                        @endif
                    </td>
                    <td class="p-4 space-x-2 whitespace-nowrap">
                        <button wire:click="openEditModal({{ $room->id }})"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white rounded-lg bg-blue-700 hover:bg-blue-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                            </svg>
                            @lang('app.edit')
                        </button>
                        <button wire:click="confirmDelete({{ $room->id }})"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            @lang('app.delete')
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-4 text-center text-gray-500 dark:text-gray-400">
                        @lang('modules.hotel.noRoomsFound')
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Add Room Modal -->
    <x-modal wire:model.live="showAddRoom" maxWidth="3xl">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ __("modules.hotel.addRoom") }}
                </h2>
                <button wire:click="closeAddModal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-label for="room_name" value="{{ __('modules.hotel.roomName') }}" required />
                        <x-input id="room_name" type="text" class="mt-1 block w-full" wire:model="name" />
                        <x-input-error for="name" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="hotel_floor_id" value="{{ __('modules.hotel.floor') }}" required />
                        <select id="hotel_floor_id" wire:model="hotel_floor_id"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">{{ __('modules.hotel.selectFloor') }}</option>
                            @foreach($floors as $floor)
                                <option value="{{ $floor->id }}">{{ $floor->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error for="hotel_floor_id" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="room_size" value="{{ __('modules.hotel.size') }}" />
                        <x-input id="room_size" type="text" class="mt-1 block w-full" wire:model="size" placeholder="e.g., 250 sq ft" />
                        <x-input-error for="size" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="no_of_beds" value="{{ __('modules.hotel.numberOfBeds') }}" required />
                        <x-input id="no_of_beds" type="number" class="mt-1 block w-full" wire:model="no_of_beds" min="1" />
                        <x-input-error for="no_of_beds" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="room_price" value="{{ __('modules.hotel.price') }}" required />
                        <x-input id="room_price" type="number" step="0.01" class="mt-1 block w-full" wire:model="price" min="0" />
                        <x-input-error for="price" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="room_sale_price" value="{{ __('modules.hotel.salePrice') }}" />
                        <x-input id="room_sale_price" type="number" step="0.01" class="mt-1 block w-full" wire:model="sale_price" min="0" />
                        <x-input-error for="sale_price" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-label for="room_pictures" value="{{ __('modules.hotel.pictures') }}" />
                        <input type="file" id="room_pictures" wire:model="pictures" accept="image/*" multiple
                            class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" />
                        <x-input-error for="pictures.*" class="mt-2" />
                        @if ($pictures)
                            <div class="mt-2 grid grid-cols-4 gap-2">
                                @foreach($pictures as $picture)
                                    <img src="{{ $picture->temporaryUrl() }}" class="h-24 w-full object-cover rounded-lg">
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div>
                        <x-label for="room_sort_order" value="{{ __('modules.hotel.sortOrder') }}" />
                        <x-input id="room_sort_order" type="number" class="mt-1 block w-full" wire:model="sort_order" min="0" />
                        <x-input-error for="sort_order" class="mt-2" />
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="room_is_active" wire:model="is_active"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <label for="room_is_active" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                            {{ __('app.active') }}
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <x-secondary-button wire:click="closeAddModal">
                        {{ __('app.cancel') }}
                    </x-secondary-button>
                    <x-button type="submit">
                        {{ __('app.save') }}
                    </x-button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Edit Room Modal -->
    <x-modal wire:model.live="showEditRoom" maxWidth="3xl">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ __("modules.hotel.editRoom") }}
                </h2>
                <button wire:click="closeEditModal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="update">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-label for="edit_room_name" value="{{ __('modules.hotel.roomName') }}" required />
                        <x-input id="edit_room_name" type="text" class="mt-1 block w-full" wire:model="name" />
                        <x-input-error for="name" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="edit_hotel_floor_id" value="{{ __('modules.hotel.floor') }}" required />
                        <select id="edit_hotel_floor_id" wire:model="hotel_floor_id"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">{{ __('modules.hotel.selectFloor') }}</option>
                            @foreach($floors as $floor)
                                <option value="{{ $floor->id }}">{{ $floor->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error for="hotel_floor_id" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="edit_room_size" value="{{ __('modules.hotel.size') }}" />
                        <x-input id="edit_room_size" type="text" class="mt-1 block w-full" wire:model="size" placeholder="e.g., 250 sq ft" />
                        <x-input-error for="size" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="edit_no_of_beds" value="{{ __('modules.hotel.numberOfBeds') }}" required />
                        <x-input id="edit_no_of_beds" type="number" class="mt-1 block w-full" wire:model="no_of_beds" min="1" />
                        <x-input-error for="no_of_beds" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="edit_room_price" value="{{ __('modules.hotel.price') }}" required />
                        <x-input id="edit_room_price" type="number" step="0.01" class="mt-1 block w-full" wire:model="price" min="0" />
                        <x-input-error for="price" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="edit_room_sale_price" value="{{ __('modules.hotel.salePrice') }}" />
                        <x-input id="edit_room_sale_price" type="number" step="0.01" class="mt-1 block w-full" wire:model="sale_price" min="0" />
                        <x-input-error for="sale_price" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-label for="edit_room_pictures" value="{{ __('modules.hotel.pictures') }}" />

                        @if($existing_pictures && count($existing_pictures) > 0)
                            <div class="mb-2 grid grid-cols-4 gap-2">
                                @foreach($existing_pictures as $index => $picture)
                                    <div class="relative">
                                        <img src="{{ asset_url_local_s3('hotel-rooms/' . $picture) }}" class="h-24 w-full object-cover rounded-lg">
                                        <button type="button" wire:click="removeExistingPicture({{ $index }})"
                                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <input type="file" id="edit_room_pictures" wire:model="pictures" accept="image/*" multiple
                            class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" />
                        <x-input-error for="pictures.*" class="mt-2" />
                        @if ($pictures)
                            <div class="mt-2 grid grid-cols-4 gap-2">
                                @foreach($pictures as $picture)
                                    <img src="{{ $picture->temporaryUrl() }}" class="h-24 w-full object-cover rounded-lg">
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div>
                        <x-label for="edit_room_sort_order" value="{{ __('modules.hotel.sortOrder') }}" />
                        <x-input id="edit_room_sort_order" type="number" class="mt-1 block w-full" wire:model="sort_order" min="0" />
                        <x-input-error for="sort_order" class="mt-2" />
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="edit_room_is_active" wire:model="is_active"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <label for="edit_room_is_active" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                            {{ __('app.active') }}
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <x-secondary-button wire:click="closeEditModal">
                        {{ __('app.cancel') }}
                    </x-secondary-button>
                    <x-button type="submit">
                        {{ __('app.update') }}
                    </x-button>
                </div>
            </form>
        </div>
    </x-modal>
</div>
