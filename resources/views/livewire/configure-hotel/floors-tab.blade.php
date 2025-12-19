<div>
    <div class="mb-4 flex justify-between items-center">
        <div>
            <x-input type="text" placeholder="{{ __('modules.hotel.searchFloors') }}"
                wire:model.live.debounce.500ms="search" class="w-64" />
        </div>
        <x-button type="button" wire:click="openAddModal">
            @lang('modules.hotel.addFloor')
        </x-button>
    </div>

    <!-- Floors Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($floors as $floor)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                @if($floor->picture)
                    <img src="{{ $floor->picture_url }}" alt="{{ $floor->name }}" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                        <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                @endif
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $floor->name }}</h3>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                        <span class="inline-flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            {{ $floor->rooms->count() }} @lang('modules.hotel.rooms')
                        </span>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button wire:click="openEditModal({{ $floor->id }})"
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                            </svg>
                        </button>
                        <button wire:click="confirmDelete({{ $floor->id }})"
                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full p-8 text-center text-gray-500 dark:text-gray-400">
                @lang('modules.hotel.noFloorsFound')
            </div>
        @endforelse
    </div>

    <!-- Add Floor Modal -->
    <x-modal wire:model.live="showAddFloor" maxWidth="lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ __("modules.hotel.addFloor") }}
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
                        <x-label for="floor_name" value="{{ __('modules.hotel.floorName') }}" required />
                        <x-input id="floor_name" type="text" class="mt-1 block w-full" wire:model="name" />
                        <x-input-error for="name" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="floor_picture" value="{{ __('modules.hotel.picture') }}" />
                        <input type="file" id="floor_picture" wire:model="picture" accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" />
                        <x-input-error for="picture" class="mt-2" />
                        @if ($picture)
                            <div class="mt-2">
                                <img src="{{ $picture->temporaryUrl() }}" class="h-32 w-full object-cover rounded-lg">
                            </div>
                        @endif
                    </div>

                    <div>
                        <x-label for="floor_sort_order" value="{{ __('modules.hotel.sortOrder') }}" />
                        <x-input id="floor_sort_order" type="number" class="mt-1 block w-full" wire:model="sort_order" min="0" />
                        <x-input-error for="sort_order" class="mt-2" />
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

    <!-- Edit Floor Modal -->
    <x-modal wire:model.live="showEditFloor" maxWidth="lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ __("modules.hotel.editFloor") }}
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
                        <x-label for="edit_floor_name" value="{{ __('modules.hotel.floorName') }}" required />
                        <x-input id="edit_floor_name" type="text" class="mt-1 block w-full" wire:model="name" />
                        <x-input-error for="name" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="edit_floor_picture" value="{{ __('modules.hotel.picture') }}" />
                        @if($existing_picture)
                            <div class="mb-2">
                                <img src="{{ asset_url_local_s3('hotel-floors/' . $existing_picture) }}" class="h-32 w-full object-cover rounded-lg">
                            </div>
                        @endif
                        <input type="file" id="edit_floor_picture" wire:model="picture" accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" />
                        <x-input-error for="picture" class="mt-2" />
                        @if ($picture)
                            <div class="mt-2">
                                <img src="{{ $picture->temporaryUrl() }}" class="h-32 w-full object-cover rounded-lg">
                            </div>
                        @endif
                    </div>

                    <div>
                        <x-label for="edit_floor_sort_order" value="{{ __('modules.hotel.sortOrder') }}" />
                        <x-input id="edit_floor_sort_order" type="number" class="mt-1 block w-full" wire:model="sort_order" min="0" />
                        <x-input-error for="sort_order" class="mt-2" />
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
