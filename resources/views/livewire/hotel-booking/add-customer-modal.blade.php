<div>
    <form wire:submit="submitForm">
        <div class="grid grid-cols-1 gap-4">
            {{-- Customer Name --}}
            <div>
                <x-label for="customerName" value="Customer Name *" />
                <x-input wire:model="customerName" id="customerName" type="text" class="mt-1 block w-full" placeholder="Enter customer name" />
                <x-input-error for="customerName" class="mt-2" />
            </div>

            {{-- Phone Number with Country Code --}}
            <div>
                <x-label class="mt-4" for="customerPhone" value="Phone Number *" />
                <div class="flex gap-2 mt-2">
                    <!-- Phone Code Dropdown -->
                    <div x-data="{ isOpen: false }" @click.away="isOpen = false" class="relative w-32">
                        <div @click="isOpen = !isOpen"
                            class="p-2 bg-gray-100 border rounded cursor-pointer dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-gray-600 dark:focus:ring-gray-600">
                            <div class="flex items-center justify-between">
                                <span class="text-sm">
                                    @if($customerPhoneCode)
                                        +{{ $customerPhoneCode }}
                                    @else
                                        Select
                                    @endif
                                </span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Search Input and Options -->
                        <ul x-show="isOpen" x-transition class="absolute z-10 w-full mt-1 overflow-auto bg-white rounded-lg shadow-lg max-h-60 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-gray-600 dark:focus:ring-gray-600">
                            <li class="sticky top-0 px-3 py-2 bg-white dark:bg-gray-900 z-10">
                                <x-input wire:model.live.debounce.300ms="phoneCodeSearch" class="block w-full" type="text" placeholder="Search..." />
                            </li>
                            @forelse ($phonecodes as $phonecode)
                                <li @click="$wire.selectPhoneCode('{{ $phonecode }}'); isOpen = false"
                                    wire:key="phone-code-{{ $phonecode }}"
                                    class="relative py-2 pl-3 text-gray-900 transition-colors duration-150 cursor-pointer select-none pr-9 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800 dark:text-gray-300 dark:focus:border-gray-600 dark:focus:ring-gray-600"
                                    :class="{ 'bg-gray-100 dark:bg-gray-800': '{{ $phonecode }}' === '{{ $customerPhoneCode }}' }" role="option">
                                    <div class="flex items-center">
                                        <span class="block ml-3 text-sm whitespace-nowrap">+{{ $phonecode }}</span>
                                        <span x-show="'{{ $phonecode }}' === '{{ $customerPhoneCode }}'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-black dark:text-gray-300" x-cloak>
                                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </div>
                                </li>
                            @empty
                                <li class="relative py-2 pl-3 text-gray-500 cursor-default select-none pr-9 dark:text-gray-400">
                                    No phone codes found
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Phone Number Input -->
                    <x-input wire:model="customerPhone" id="customerPhone" class="block w-full" type="tel"
                        placeholder="1234567890" />
                </div>

                <x-input-error for="customerPhoneCode" class="mt-2" />
                <x-input-error for="customerPhone" class="mt-2" />
            </div>

            {{-- Email --}}
            <div>
                <x-label for="customerEmail" value="Email" />
                <x-input wire:model="customerEmail" id="customerEmail" type="email" class="mt-1 block w-full" placeholder="customer@example.com" />
                <x-input-error for="customerEmail" class="mt-2" />
            </div>

            {{-- Address --}}
            <div>
                <x-label for="customerAddress" value="Address" />
                <textarea wire:model="customerAddress" id="customerAddress" rows="3"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                    placeholder="Enter customer address"></textarea>
                <x-input-error for="customerAddress" class="mt-2" />
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end mt-4">
                <x-button type="submit">
                    Add Customer
                </x-button>
            </div>
        </div>
    </form>
</div>
