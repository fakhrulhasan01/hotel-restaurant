<div>
    <div class="p-4 bg-white block dark:bg-gray-800">
        <div class="mb-4">
            <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">@lang('modules.hotel.configureHotel')</h1>
        </div>

        <!-- Hotel Selection -->
        <div class="mb-6">
            <label for="hotel-select" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                @lang('modules.hotel.selectHotel')
            </label>
            <select id="hotel-select" wire:model.live="selectedHotelId"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                @foreach($hotels as $hotel)
                    <option value="{{ $hotel->id }}">
                        {{ $hotel->name }} {{ $hotel->is_default ? '(Default)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($selectedHotelId)
            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                    <li class="mr-2">
                        <button wire:click="switchTab('floors')"
                            class="inline-flex items-center justify-center p-4 border-b-2 rounded-t-lg group {{ $activeTab === 'floors' ? 'text-blue-600 border-blue-600 dark:text-blue-500 dark:border-blue-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }}">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"/>
                            </svg>
                            @lang('modules.hotel.floors')
                        </button>
                    </li>
                    <li class="mr-2">
                        <button wire:click="switchTab('rooms')"
                            class="inline-flex items-center justify-center p-4 border-b-2 rounded-t-lg group {{ $activeTab === 'rooms' ? 'text-blue-600 border-blue-600 dark:text-blue-500 dark:border-blue-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }}">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            @lang('modules.hotel.rooms')
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="mt-6">
                @if($activeTab === 'floors')
                    @livewire('ConfigureHotel.FloorsTab', ['hotelId' => $selectedHotelId], key('floors-'.$selectedHotelId))
                @elseif($activeTab === 'rooms')
                    @livewire('ConfigureHotel.RoomsTab', ['hotelId' => $selectedHotelId], key('rooms-'.$selectedHotelId))
                @endif
            </div>
        @else
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                @lang('modules.hotel.noHotelsAvailable')
            </div>
        @endif
    </div>
</div>
