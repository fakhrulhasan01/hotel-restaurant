<div>
    <div class="p-4 bg-white block sm:flex items-center justify-between dark:bg-gray-800 dark:border-gray-700">
        <div class="w-full mb-1">
            <div class="sm:flex">
                <div class="items-center hidden mb-3 sm:flex sm:divide-x sm:divide-gray-100 sm:mb-0 dark:divide-gray-700">
                    <div class="lg:pr-3">
                        <label for="bookings-search" class="sr-only">Search</label>
                        <div class="relative mt-1 lg:w-64 xl:w-96">
                            <input type="text" wire:model.live.debounce.300ms="search" id="bookings-search"
                                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Search bookings...">
                        </div>
                    </div>
                    <div class="flex pl-0 mt-3 space-x-1 sm:pl-2 sm:mt-0">
                        <select wire:model.live="filterStatus"
                            class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="checked-in">Checked In</option>
                            <option value="checked-out">Checked Out</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center ml-auto space-x-2 sm:space-x-3">
                    <a href="{{ route('hotel-bookings.create') }}" wire:navigate
                        class="inline-flex items-center justify-center w-1/2 px-3 py-2 text-sm font-medium text-center text-white rounded-lg bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 sm:w-auto dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Add Booking
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col">
        <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
                <div class="overflow-hidden shadow">
                    <table class="min-w-full divide-y divide-gray-200 table-fixed dark:divide-gray-600">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="py-2.5 px-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    ID
                                </th>
                                <th scope="col" class="py-2.5 px-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Hotel
                                </th>
                                <th scope="col" class="py-2.5 px-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Customer
                                </th>
                                <th scope="col" class="py-2.5 px-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Check-in
                                </th>
                                <th scope="col" class="py-2.5 px-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Check-out
                                </th>
                                <th scope="col" class="py-2.5 px-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Rooms
                                </th>
                                <th scope="col" class="py-2.5 px-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Status
                                </th>
                                <th scope="col" class="py-2.5 px-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Price
                                </th>
                                <th scope="col" class="py-2.5 px-4 text-xs font-medium text-gray-500 uppercase dark:text-gray-400 text-right">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @forelse ($bookings as $booking)
                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-700" wire:key='booking-{{ $booking->id }}'>
                                <td class="py-2.5 px-4 text-sm text-gray-900 whitespace-nowrap dark:text-white">
                                    #{{ $booking->id }}
                                </td>
                                <td class="py-2.5 px-4 text-sm text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $booking->hotel->name }}
                                </td>
                                <td class="py-2.5 px-4 text-sm text-gray-900 dark:text-white">
                                    <div>{{ $booking->customer->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $booking->customer->phone }}</div>
                                </td>
                                <td class="py-2.5 px-4 text-sm text-gray-900 dark:text-white">
                                    <div>{{ $booking->checkin_date->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $booking->checkin_time->format('h:i A') }}</div>
                                </td>
                                <td class="py-2.5 px-4 text-sm text-gray-900 dark:text-white">
                                    <div>{{ $booking->checkout_date->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $booking->checkout_time->format('h:i A') }}</div>
                                </td>
                                <td class="py-2.5 px-4 text-sm text-gray-900 dark:text-white">
                                    <div class="space-y-1">
                                        @foreach($booking->bookingRooms as $bookingRoom)
                                        <div class="text-xs">
                                            <span class="font-medium">{{ $bookingRoom->room->floor->name }}</span> →
                                            <span>{{ $bookingRoom->room->name }}</span>
                                            <span class="text-gray-500">({{ $bookingRoom->no_of_people }} people)</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-2.5 px-4 text-sm">
                                    <span @class([
                                        'px-2.5 py-0.5 rounded text-xs font-medium uppercase',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $booking->status === 'pending',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' => $booking->status === 'confirmed',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $booking->status === 'checked-in',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $booking->status === 'checked-out',
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $booking->status === 'cancelled',
                                    ])>
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-sm font-medium text-gray-900 dark:text-white">
                                    ${{ number_format($booking->price, 2) }}
                                </td>
                                <td class="py-2.5 px-4 space-x-2 whitespace-nowrap text-right">
                                    <a href="{{ route('hotel-bookings.edit', $booking->id) }}" wire:navigate>
                                        <x-secondary-button-table>
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                                                <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            Edit
                                        </x-secondary-button-table>
                                    </a>

                                    <x-danger-button-table wire:click="showDeleteConfirm({{ $booking->id }})">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </x-danger-button-table>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="py-8 px-4 text-center text-gray-500">
                                    No bookings found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="sticky bottom-0 right-0 items-center w-full p-4 bg-white border-t border-gray-200 sm:flex sm:justify-between dark:bg-gray-800 dark:border-gray-700">
        {{ $bookings->links() }}
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-confirmation-modal wire:model="confirmDeleteModal">
        <x-slot name="title">
            Delete Booking?
        </x-slot>
        <x-slot name="content">
            Are you sure you want to delete this booking? This action cannot be undone.
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmDeleteModal')">
                Cancel
            </x-secondary-button>
            <x-danger-button class="ml-3" wire:click="deleteBooking">
                Delete
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
