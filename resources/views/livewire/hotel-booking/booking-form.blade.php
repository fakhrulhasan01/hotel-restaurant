<div>
    <div class="p-4 bg-white dark:bg-gray-800">
        <div class="mb-4">
            <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Create New Booking</h1>
        </div>

        <form wire:submit="submitForm">
            {{-- Booking Info Section --}}
            <div class="grid grid-cols-1 gap-6 mb-6">
                <div class="p-6 bg-gray-50 rounded-lg dark:bg-gray-700">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Booking Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Hotel Selection --}}
                        <div>
                            <x-label for="hotel_id" value="Hotel *" />
                            <select wire:model.live="hotel_id" id="hotel_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                <option value="">Select Hotel</option>
                                @foreach($hotels as $hotel)
                                    <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="hotel_id" class="mt-2" />
                        </div>

                        {{-- Customer Selection --}}
                        <div>
                            <x-label for="customer_id" value="Customer *" />
                            <div class="flex gap-2 mt-1">
                                <select wire:model="customer_id" id="customer_id"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                                    @endforeach
                                </select>
                                <x-secondary-button type="button" wire:click="$set('showAddCustomerModal', true)">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </x-secondary-button>
                            </div>
                            <x-input-error for="customer_id" class="mt-2" />
                        </div>

                        {{-- Check-in Date & Time --}}
                        <div>
                            <x-label for="checkin_date" value="Check-in Date *" />
                            <x-input wire:model.live="checkin_date" id="checkin_date" type="date" class="mt-1 block w-full" />
                            <x-input-error for="checkin_date" class="mt-2" />
                        </div>

                        <div>
                            <x-label for="checkin_time" value="Check-in Time *" />
                            <x-input wire:model="checkin_time" id="checkin_time" type="time" class="mt-1 block w-full" />
                            <x-input-error for="checkin_time" class="mt-2" />
                        </div>

                        {{-- Check-out Date & Time --}}
                        <div>
                            <x-label for="checkout_date" value="Check-out Date *" />
                            <x-input wire:model.live="checkout_date" id="checkout_date" type="date" class="mt-1 block w-full" />
                            <x-input-error for="checkout_date" class="mt-2" />
                        </div>

                        <div>
                            <x-label for="checkout_time" value="Check-out Time *" />
                            <x-input wire:model="checkout_time" id="checkout_time" type="time" class="mt-1 block w-full" />
                            <x-input-error for="checkout_time" class="mt-2" />
                        </div>

                        {{-- Status --}}
                        <div>
                            <x-label for="status" value="Status *" />
                            <select wire:model="status" id="status"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="checked-in">Checked In</option>
                                <option value="checked-out">Checked Out</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <x-input-error for="status" class="mt-2" />
                        </div>

                        {{-- Notes --}}
                        <div class="md:col-span-2">
                            <x-label for="notes" value="Notes" />
                            <textarea wire:model="notes" id="notes" rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rooms Section --}}
            <div class="p-6 bg-gray-50 rounded-lg dark:bg-gray-700 mb-6">
                <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Add Rooms</h3>

                @if($hotel_id && $checkin_date && $checkout_date)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        {{-- Room Selection (Floor > Room) --}}
                        <div>
                            <x-label for="selectedRoom" value="Room *" />
                            <select wire:model="selectedRoom" id="selectedRoom"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                <option value="">Select Room</option>
                                @foreach($availableRooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->floor->name }} > {{ $room->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="selectedRoom" class="mt-2" />
                        </div>

                        {{-- Number of People --}}
                        <div>
                            <x-label for="noOfPeople" value="No. of People *" />
                            <x-input wire:model="noOfPeople" id="noOfPeople" type="number" min="1" class="mt-1 block w-full" />
                            <x-input-error for="noOfPeople" class="mt-2" />
                        </div>

                        {{-- Add Button --}}
                        <div class="flex items-end">
                            <x-button type="button" wire:click="addRoom" class="w-full">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                                </svg>
                                Add Room
                            </x-button>
                        </div>
                    </div>
                @else
                    <div class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300">
                        Please select hotel, check-in and check-out dates to add rooms.
                    </div>
                @endif

                {{-- Rooms Table --}}
                @if(count($rooms) > 0)
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-100 dark:bg-gray-600">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-300">Room (Floor → Room)</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-300">No. of People</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-300">Price</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase dark:text-gray-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @foreach($rooms as $index => $room)
                                    <tr wire:key="room-{{ $index }}">
                                        <td class="px-4 py-3 text-sm dark:text-white">
                                            {{ $room['floor_name'] }} → {{ $room['room_name'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm dark:text-white">
                                            {{ $room['no_of_people'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium dark:text-white">
                                            ${{ number_format($room['price'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <x-danger-button type="button" wire:click="removeRoom({{ $index }})" class="text-xs">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                            </x-danger-button>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <td colspan="2" class="px-4 py-3 text-right text-sm font-medium dark:text-white">
                                        Total Price:
                                    </td>
                                    <td colspan="2" class="px-4 py-3 text-sm font-bold text-primary-600 dark:text-primary-400">
                                        ${{ number_format($this->totalPrice, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <x-input-error for="rooms" class="mt-2" />
                @else
                    <div class="p-4 text-sm text-gray-500 text-center dark:text-gray-400">
                        No rooms added yet. Please add rooms above.
                    </div>
                @endif
            </div>

            {{-- Submit Buttons --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('hotel-bookings.index') }}">
                    <x-secondary-button type="button">
                        Cancel
                    </x-secondary-button>
                </a>
                <x-button type="submit">
                    Create Booking
                </x-button>
            </div>
        </form>
    </div>

    {{-- Add Customer Modal --}}
    @if($showAddCustomerModal)
        <x-dialog-modal wire:model="showAddCustomerModal">
            <x-slot name="title">
                Add New Customer
            </x-slot>
            <x-slot name="content">
                @livewire('hotel-booking.add-customer-modal')
            </x-slot>
            <x-slot name="footer">
                <x-secondary-button wire:click="$set('showAddCustomerModal', false)">
                    Close
                </x-secondary-button>
            </x-slot>
        </x-dialog-modal>
    @endif
</div>
