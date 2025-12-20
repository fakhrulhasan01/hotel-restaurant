<div>
    <div class="p-4 bg-white block sm:flex items-center justify-between dark:bg-gray-800 dark:border-gray-700">
        <div class="w-full mb-1">
            <div class="mb-4">
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">@lang('modules.hotel.hotels')</h1>
            </div>

            <div class="items-center justify-between block sm:flex">
                <div class="flex items-center mb-4 sm:mb-0">
                    <form class="ltr:sm:pr-3 rtl:sm:pl-3" action="#" method="GET">
                        <label for="hotel-search" class="sr-only">Search</label>
                        <div class="relative w-48 sm:w-64 xl:w-96">
                            <x-input id="hotel_search" class="block w-full" type="text"
                                placeholder="{{ __('modules.hotel.searchHotels') }}"
                                wire:model.live.debounce.500ms="search" />
                        </div>
                    </form>
                </div>

                <x-button type='button' wire:click="openAddModal">
                    @lang('modules.hotel.addHotel')
                </x-button>
            </div>
        </div>
    </div>

    <!-- Hotels Table -->
    <div class="flex flex-col">
        <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
                <div class="overflow-hidden shadow">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-white">
                                    @lang('modules.hotel.hotelName')
                                </th>
                                <th scope="col" class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-white">
                                    @lang('modules.hotel.address')
                                </th>
                                <th scope="col" class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-white">
                                    @lang('modules.hotel.checkInOut')
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
                            @forelse($hotels as $hotel)
                            <tr wire:key="hotel-{{ $hotel->id }}" class="hover:bg-gray-100 dark:hover:bg-gray-700">
                                <td class="p-4 text-sm font-normal text-gray-900 whitespace-nowrap dark:text-white">
                                    <div class="flex items-center">
                                        @if($hotel->logo)
                                        <img class="h-10 w-10 rounded-lg object-cover mr-3" src="{{ $hotel->logo_url }}" alt="{{ $hotel->name }}">
                                        @else
                                        <div class="h-10 w-10 rounded-lg bg-gray-200 dark:bg-gray-600 mr-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                            </svg>
                                        </div>
                                        @endif
                                        <div>
                                            <div class="font-semibold">{{ $hotel->name }}</div>
                                            @if($hotel->is_default)
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-300">
                                                @lang('modules.hotel.default')
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-sm font-normal text-gray-500 whitespace-nowrap dark:text-gray-400">
                                    {{ Str::limit($hotel->address ?? '-', 50) }}
                                </td>
                                <td class="p-4 text-sm font-normal text-gray-500 whitespace-nowrap dark:text-gray-400">
                                    <div class="text-xs">
                                        <div>@lang('modules.hotel.checkIn'): {{ $hotel->checkin_time ? $hotel->checkin_time->format('h:i A') : '-' }}</div>
                                        <div>@lang('modules.hotel.checkOut'): {{ $hotel->checkout_time ? $hotel->checkout_time->format('h:i A') : '-' }}</div>
                                    </div>
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    @if($hotel->is_active)
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
                                    <button wire:click="openEditModal({{ $hotel->id }})"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                        @lang('app.edit')
                                    </button>
                                    <button wire:click="confirmDelete({{ $hotel->id }})"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        @lang('app.delete')
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                    @lang('modules.hotel.noHotelsFound')
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Hotel Modal -->
    <x-modal id="addHotelModal" wire:model.live="showAddHotel" maxWidth="2xl">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ __("modules.hotel.addHotel") }}
                </h2>
                <button wire:click="closeAddModal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <x-label for="name" value="{{ __('modules.hotel.hotelName') }}" required />
                        <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
                        <x-input-error for="name" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="address" value="{{ __('modules.hotel.address') }}" />
                        <textarea id="address" wire:model="address" rows="2"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                        <x-input-error for="address" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-label for="checkin_time" value="{{ __('modules.hotel.checkInTime') }}" required />
                            <x-input id="checkin_time" type="time" class="mt-1 block w-full" wire:model="checkin_time" />
                            <x-input-error for="checkin_time" class="mt-2" />
                        </div>

                        <div>
                            <x-label for="checkout_time" value="{{ __('modules.hotel.checkOutTime') }}" required />
                            <x-input id="checkout_time" type="time" class="mt-1 block w-full" wire:model="checkout_time" />
                            <x-input-error for="checkout_time" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-label for="logo" value="{{ __('modules.hotel.logo') }}" />
                        <input type="file" id="logo" wire:model="logo" accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" />
                        <x-input-error for="logo" class="mt-2" />
                        @if ($logo)
                            <div class="mt-2">
                                <img src="{{ $logo->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-lg">
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_default" wire:model="is_default"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="is_default" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                            {{ __('modules.hotel.setAsDefault') }}
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" wire:model="is_active"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="is_active" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
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

    <!-- Edit Hotel Modal -->
    @if ($editingHotelId)
    <x-modal id="editHotelModal" wire:model.live="showEditHotel" maxWidth="2xl">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ __("modules.hotel.editHotel") }}
                </h2>
                <button wire:click="closeEditModal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="update">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <x-label for="edit_name" value="{{ __('modules.hotel.hotelName') }}" required />
                        <x-input id="edit_name" type="text" class="mt-1 block w-full" wire:model="name" />
                        <x-input-error for="name" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="edit_address" value="{{ __('modules.hotel.address') }}" />
                        <textarea id="edit_address" wire:model="address" rows="2"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                        <x-input-error for="address" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-label for="edit_checkin_time" value="{{ __('modules.hotel.checkInTime') }}" required />
                            <x-input id="edit_checkin_time" type="time" class="mt-1 block w-full" wire:model="checkin_time" />
                            <x-input-error for="checkin_time" class="mt-2" />
                        </div>

                        <div>
                            <x-label for="edit_checkout_time" value="{{ __('modules.hotel.checkOutTime') }}" required />
                            <x-input id="edit_checkout_time" type="time" class="mt-1 block w-full" wire:model="checkout_time" />
                            <x-input-error for="checkout_time" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-label for="edit_logo" value="{{ __('modules.hotel.logo') }}" />
                        @if($existing_logo)
                            <div class="mb-2">
                                <img src="{{ asset_url_local_s3('hotel-logos/' . $existing_logo) }}" class="h-20 w-20 object-cover rounded-lg">
                            </div>
                        @endif
                        <input type="file" id="edit_logo" wire:model="logo" accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" />
                        <x-input-error for="logo" class="mt-2" />
                        @if ($logo)
                            <div class="mt-2">
                                <img src="{{ $logo->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-lg">
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="edit_is_default" wire:model="is_default"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="edit_is_default" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                            {{ __('modules.hotel.setAsDefault') }}
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="edit_is_active" wire:model="is_active"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="edit_is_active" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
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
    @endif
</div>
