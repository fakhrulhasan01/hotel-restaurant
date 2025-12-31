<div
    x-data="posCartOffline()"
    x-init="init()"
    class="lg:w-6/12 flex flex-col bg-white border-l dark:border-gray-700 min-h-screen h-auto pr-4 px-2 py-4 dark:bg-gray-800"
    @cart-item-added.window="handleItemAdded($event.detail)"
    @cart-item-with-variation.window="handleItemWithVariation($event.detail)"
    @cart-item-with-modifiers.window="handleItemWithModifiers($event.detail)"
    @cart-updated.window="handleCartUpdate($event.detail)"
>
    {{-- Offline Cart Status --}}
    <div class="flex items-center justify-between mb-2 text-xs">
        <div class="flex items-center gap-2">
            <template x-if="!isOnline">
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-orange-100 text-orange-800 rounded-full dark:bg-orange-900 dark:text-orange-200">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l6.921 6.922c.05.062.105.118.168.167l6.91 6.911a1 1 0 001.414-1.414L3.707 2.293zM17.25 9.75a7.5 7.5 0 00-14.484-2.725l1.518 1.518A5.5 5.5 0 0117.25 9.75z" clip-rule="evenodd"/>
                    </svg>
                    @lang('modules.pos.offline')
                </span>
            </template>
            <template x-if="cartItems.length > 0 && !isSynced">
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900 dark:text-blue-200">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12z"/>
                        <path d="M10 5a1 1 0 00-1 1v4l3 2a1 1 0 101-1.73l-2-1.33V6a1 1 0 00-1-1z"/>
                    </svg>
                    @lang('modules.pos.pendingSync')
                </span>
            </template>
        </div>
        <span class="text-gray-500 dark:text-gray-400" x-text="cartItems.length + ' @lang('modules.order.items')'"></span>
    </div>

    {{-- Order Type Indicator --}}
    @if($orderTypeId)
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 pb-2 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-500 dark:text-gray-400">@lang('modules.settings.orderType'):</span>
            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ \App\Models\OrderType::find($orderTypeId)?->order_type_name ?? ucfirst($orderType) }}
            </span>

            @if($orderTypeSlug === 'delivery' && $selectedDeliveryApp)
                <span class="text-xs text-gray-500 dark:text-gray-400 mx-2">•</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Platform:</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                    @if($selectedDeliveryApp === 'default')
                        Default
                    @else
                        {{ \App\Models\DeliveryPlatform::find($selectedDeliveryApp)?->name ?? 'Unknown' }}
                    @endif
                </span>
            @endif
        </div>

        <button type="button" wire:click="changeOrderType" class="text-xs bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-3 py-2 rounded-full transition-all">
            Change
        </button>
    </div>
    @endif

    <div>
        <div class="mt-2">
            @if($customerId)
                <div class="flex items-center gap-2">
                    <div class="font-semibold text-gray-700 dark:text-gray-300">{{ $customer->name }}</div>
                    @if(user_can('Update Order'))
                        <button wire:click="$dispatch('showAddCustomerModal', { customerId: {{ $customerId }} })" title="{{__('modules.order.updateCustomerDetails')}}" class="p-1 text-gray-500 transition-colors bg-gray-100 rounded-md hover:text-gray-700 hover:bg-gray-200 rtl:ml-2 ltr:mr-2 dark:text-gray-300 dark:bg-gray-600 dark:hover:text-gray-200 dark:hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                            </svg>
                        </button>
                    @endif
                </div>
            @else
                <a href="javascript:;" wire:click="$dispatch('showAddCustomerModal')" class="text-sm underline underline-offset-2 dark:text-gray-300">&plus; @lang('modules.order.addCustomerDetails')</a>
            @endif
        </div>

        <div class="flex justify-between my-2 items-center">
            <div class="font-medium py-2 inline-flex items-center gap-1 dark:text-neutral-200">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-receipt w-6 h-6" viewBox="0 0 16 16"><path d="M1.92.506a.5.5 0 0 1 .434.14L3 1.293l.646-.647a.5.5 0 0 1 .708 0L5 1.293l.646-.647a.5.5 0 0 1 .708 0L7 1.293l.646-.647a.5.5 0 0 1 .708 0L9 1.293l.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .801.13l.5 1A.5.5 0 0 1 15 2v12a.5.5 0 0 1-.053.224l-.5 1a.5.5 0 0 1-.8.13L13 14.707l-.646.647a.5.5 0 0 1-.708 0L11 14.707l-.646.647a.5.5 0 0 1-.708 0L9 14.707l-.646.647a.5.5 0 0 1-.708 0L7 14.707l-.646.647a.5.5 0 0 1-.708 0L5 14.707l-.646.647a.5.5 0 0 1-.708 0L3 14.707l-.646.647a.5.5 0 0 1-.801-.13l-.5-1A.5.5 0 0 1 1 14V2a.5.5 0 0 1 .053-.224l.5-1a.5.5 0 0 1 .367-.27m.217 1.338L2 2.118v11.764l.137.274.51-.51a.5.5 0 0 1 .707 0l.646.647.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.509.509.137-.274V2.118l-.137-.274-.51.51a.5.5 0 0 1-.707 0L12 1.707l-.646.647a.5.5 0 0 1-.708 0L10 1.707l-.646.647a.5.5 0 0 1-.708 0L8 1.707l-.646.647a.5.5 0 0 1-.708 0L6 1.707l-.646.647a.5.5 0 0 1-.708 0L4 1.707l-.646.647a.5.5 0 0 1-.708 0z"/><path d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m8-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5"/></svg>

                @if(!isOrderPrefixEnabled())
                    @lang('modules.order.orderNumber') #{{ $orderNumber }}
                @else
                    {{ $formattedOrderNumber }}
                @endif
            </div>

            @if ($orderType == 'dine_in')
                <div class="inline-flex items-center gap-2 dark:text-gray-300">
                    @if (!is_null($tableNo))
                        <svg fill="currentColor" class="w-5 h-5 transition duration-75 group-hover:text-gray-900 dark:text-gray-200 dark:group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 44.999 44.999" xml:space="preserve"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round"/><path d="m42.558 23.378 2.406-10.92a1.512 1.512 0 0 0-2.954-.652l-2.145 9.733h-9.647a1.512 1.512 0 0 0 0 3.026h.573l-3.258 7.713a1.51 1.51 0 0 0 1.393 2.102c.59 0 1.15-.348 1.394-.925l2.974-7.038 4.717.001 2.971 7.037a1.512 1.512 0 1 0 2.787-1.177l-3.257-7.713h.573a1.51 1.51 0 0 0 1.473-1.187m-28.35 1.186h.573a1.512 1.512 0 0 0 0-3.026H5.134L2.99 11.806a1.511 1.511 0 1 0-2.954.652l2.406 10.92a1.51 1.51 0 0 0 1.477 1.187h.573L1.234 32.28a1.51 1.51 0 0 0 .805 1.98 1.515 1.515 0 0 0 1.982-.805l2.971-7.037 4.717-.001 2.972 7.038a1.514 1.514 0 0 0 1.982.805 1.51 1.51 0 0 0 .805-1.98z"/><path d="M24.862 31.353h-.852V18.308h8.13a1.513 1.513 0 1 0 0-3.025H12.856a1.514 1.514 0 0 0 0 3.025h8.13v13.045h-.852a1.514 1.514 0 0 0 0 3.027h4.728a1.513 1.513 0 1 0 0-3.027"/></svg>
                        {{ $tableNo }}
                        <x-secondary-button wire:click="openTableChangeConfirmation">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                                <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                                <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
                            </svg>
                        </x-secondary-button>
                    @else
                        <x-secondary-button wire:click="openTableChangeConfirmation">@lang('modules.order.setTable')</x-secondary-button>
                    @endif
                </div>
            @endif
        </div>

        @if ($orderType == 'dine_in')
            <div class="flex justify-between items-center gap-2">
                <div class="py-2 inline-flex items-center gap-1 text-sm dark:text-gray-300">
                    @lang('modules.order.noOfPax') <x-input type="number" step='1' min='1' class="w-16 text-sm" wire:model.defer='noOfPax' />
                </div>

                <div class="gap-2 inline-flex items-center">
                    <x-secondary-button class="relative" wire:click="$toggle('showKotNote')" :title="__('modules.order.addNote')" data-tooltip-target="tooltip-note">
                        @if ($this->orderNote)
                            <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" fill="currentColor" class="absolute bi bi-circle-fill top-1 right-1 text-skin-base" viewBox="0 0 16 16">
                                <circle cx="8" cy="8" r="8"/>
                            </svg>
                        @endif
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                            <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                        </svg>
                    </x-secondary-button>

                    <div class="inline-flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-200 hidden lg:block" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                        </svg>
                        <span class="text-sm text-gray-600 dark:text-gray-300">@lang('modules.order.waiter'):</span>
                        @if (auth()->user()->roles->pluck('display_name')->contains('Waiter'))
                            <span class="text-sm w-36 px-2 py-1 border border-gray-300 rounded-md bg-gray-100 dark:text-gray-200 dark:bg-gray-600 dark:border-gray-700 truncate" title="{{ $this->users->where('id', $selectWaiter)->first()->name ?? __('modules.order.selectWaiter') }}">
                                {{ $this->users->where('id', $selectWaiter)->first()->name ?? __('modules.order.selectWaiter') }}
                            </span>
                        @else
                            <x-select class="text-sm w-36" wire:model.defer='selectWaiter'>
                                <option value="">@lang('modules.order.selectWaiter')</option>
                                @foreach ($this->users as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </x-select>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($orderType == 'delivery')
            <div class="gap-2 flex justify-between items-center mb-2">
                <div class="inline-flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-700 dark:text-gray-200 hidden lg:block" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                    </svg>
                    <span class="text-sm text-gray-600 dark:text-gray-300">@lang('modules.delivery.executive'):</span>
                    <x-select class="text-sm w-40" wire:model.defer='selectedDeliveryExecutive'>
                        <option value="">@lang('modules.delivery.selectExecutive')</option>
                        @foreach ($this->deliveryExecutives ?? [] as $executive)
                            <option value="{{ $executive->id }}">{{ $executive->name }}</option>
                        @endforeach
                    </x-select>
                </div>
            </div>
        @endif
    </div>

    {{-- Cart Items List --}}
    <div class="flex-grow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700">
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">@lang('modules.menu.item')</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-28">@lang('modules.menu.qty')</th>
                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">@lang('modules.menu.amount')</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-10"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    <template x-if="cartItems.length === 0">
                        <tr>
                            <td colspan="4" class="px-2 py-8 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <div class="text-gray-500 dark:text-gray-400 text-base">
                                        @lang('messages.noItemAdded')
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-for="(item, index) in cartItems" :key="item.cartKey">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-2 py-3">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="item.item_name"></span>
                                    <template x-if="item.variation">
                                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="item.variation.variation_name"></span>
                                    </template>
                                    <template x-if="item.modifiers && item.modifiers.length > 0">
                                        <span class="text-xs text-blue-600 dark:text-blue-400" x-text="item.modifiers.map(m => m.name).join(', ')"></span>
                                    </template>
                                    <template x-if="item.note">
                                        <span class="text-xs text-orange-600 dark:text-orange-400 italic" x-text="'Note: ' + item.note"></span>
                                    </template>
                                </div>
                            </td>
                            <td class="px-2 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="decrementQty(index)" class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <input type="number" x-model.number="item.qty" @change="updateItemQty(index, item.qty)" min="1" class="w-12 text-center text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:ring-skin-base focus:border-skin-base">
                                    <button @click="incrementQty(index)" class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-2 py-3 text-right">
                                <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="formatCurrency(item.amount)"></span>
                            </td>
                            <td class="px-2 py-3 text-center">
                                <button @click="removeItem(index)" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Cart Summary --}}
    <div class="lg:min-w-20">
        <div class="h-auto p-4 mt-3 select-none text-center bg-gray-50 rounded space-y-4 dark:bg-gray-700">
            <template x-if="cartItems.length > 0 && {{ user_can('Add Discount on POS') ? 'true' : 'false' }}">
                <div class="text-left">
                    <x-secondary-button wire:click="showAddDiscount">
                        <svg class="h-5 w-5 text-current me-1" width="24" height="24" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
                            <path d="m7.25 14.25-5.5-5.5 7-7h5.5v5.5z"/>
                            <circle cx="11" cy="5" r=".5" fill="#000"/>
                        </svg>
                        @lang('modules.order.addDiscount')
                    </x-secondary-button>
                </div>
            </template>

            <div class="flex justify-between text-gray-500 text-sm dark:text-neutral-400">
                <div>@lang('modules.order.totalItem')</div>
                <div x-text="cartItems.length"></div>
            </div>
            <div class="flex justify-between text-gray-500 text-sm dark:text-neutral-400">
                <div>@lang('modules.order.subTotal')</div>
                <div x-text="formatCurrency(subTotal)"></div>
            </div>

            {{-- Discount (from Livewire) --}}
            @if ($discountAmount)
                <div wire:key="discountAmount" class="flex justify-between text-green-500 text-sm dark:text-green-400">
                    <div class="inline-flex items-center gap-x-1">
                        @lang('modules.order.discount')
                        @if ($discountType == 'percent') ({{ $discountValue }}%) @endif
                        <span class="text-red-500 hover:scale-110 active:scale-100 cursor-pointer" wire:click="removeCurrentDiscount">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    </div>
                    <div>-{{ currency_format($discountAmount, $restaurant->currency_id) }}</div>
                </div>
            @endif

            @if ($orderType === 'delivery')
                <div class="flex justify-between items-center text-gray-500 text-sm dark:text-neutral-400">
                    <div>
                        @lang('modules.delivery.deliveryFee')
                        @if($deliveryFee == 0)
                            <span class="text-xs text-gray-400">(@lang('modules.delivery.freeDelivery'))</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <x-input type="number" step='1' min='0' class="w-16 text-sm" wire:model.live='deliveryFee' />
                    </div>
                </div>
            @endif

            {{-- Extra Charges (from Livewire) --}}
            @if (!$orderID && count($orderItemList) > 0 && $extraCharges)
                @foreach ($extraCharges as $charge)
                    <div wire:key="extraCharge-{{ $loop->index }}" class="flex justify-between text-gray-500 text-sm dark:text-neutral-400">
                        <div class="inline-flex items-center gap-x-1">
                            {{ $charge->charge_name }}
                            @if ($charge->charge_type == 'percent') ({{ $charge->charge_value }}%) @endif
                            <span class="text-red-500 hover:scale-110 active:scale-100 cursor-pointer" wire:click="removeExtraCharge('{{ $charge->id }}', '{{ $orderType }}')">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        </div>
                        <div>{{ currency_format($charge->getAmount($discountedTotal), $restaurant->currency_id) }}</div>
                    </div>
                @endforeach
            @endif

            {{-- Tax Display --}}
            <template x-if="taxAmount > 0">
                <div class="flex justify-between text-gray-500 text-sm dark:text-neutral-400">
                    <div>
                        @lang('modules.order.totalTax')
                        <span class="text-xs text-gray-400">
                            ({{ $restaurant->tax_inclusive ? __('modules.settings.taxInclusive') : __('modules.settings.taxExclusive') }})
                        </span>
                    </div>
                    <div x-text="formatCurrency(taxAmount)"></div>
                </div>
            </template>

            <div class="flex justify-between font-medium dark:text-neutral-300">
                <div>@lang('modules.order.total')</div>
                <div x-text="formatCurrency(total)"></div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="h-auto pb-4 pt-3 select-none text-center w-full mb-16 md:mb-0">
            @if (in_array('KOT', restaurant_modules()))
                <div class="flex gap-3">
                    <button class="rounded bg-gray-700 text-white w-full p-2 relative" @click="submitOrder('kot')" :disabled="isSubmitting || cartItems.length === 0" :class="{ 'opacity-50 cursor-not-allowed': isSubmitting || cartItems.length === 0 }">
                        <span x-show="!isSubmitting || submitType !== 'kot'">@lang('modules.order.kot')</span>
                        <span x-show="isSubmitting && submitType === 'kot'" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-1 h-4 w-4 inline-flex text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            @lang('modules.order.kot')
                        </span>
                    </button>
                    <button class="rounded bg-gray-700 text-white w-full p-2 relative" @click="submitOrder('kot', 'print')" :disabled="isSubmitting || cartItems.length === 0" :class="{ 'opacity-50 cursor-not-allowed': isSubmitting || cartItems.length === 0 }">
                        <span x-show="!isSubmitting || submitType !== 'kot_print'">@lang('modules.order.kotAndPrint')</span>
                        <span x-show="isSubmitting && submitType === 'kot_print'" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            @lang('modules.order.kotAndPrint')
                        </span>
                    </button>
                    <button class="rounded bg-gray-700 text-white w-full p-2 relative" @click="submitOrder('kot', 'bill', 'payment', 'print')" :disabled="isSubmitting || cartItems.length === 0" :class="{ 'opacity-50 cursor-not-allowed': isSubmitting || cartItems.length === 0 }">
                        <span x-show="!isSubmitting || submitType !== 'kot_bill_payment'">@lang('modules.order.kotBillAndPayment')</span>
                        <span x-show="isSubmitting && submitType === 'kot_bill_payment'" class="inline-flex items-center">
                            <svg class="animate-spin inline-flex -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            @lang('modules.order.kotBillAndPayment')
                        </span>
                    </button>
                </div>
            @endif
            @if (!$orderID)
                <div class="flex gap-3 mt-3">
                    <button class="rounded bg-skin-base text-white w-full p-2 relative" @click="submitOrder('bill')" :disabled="isSubmitting || cartItems.length === 0" :class="{ 'opacity-50 cursor-not-allowed': isSubmitting || cartItems.length === 0 }">
                        <span x-show="!isSubmitting || submitType !== 'bill'">@lang('modules.order.bill')</span>
                        <span x-show="isSubmitting && submitType === 'bill'" class="inline-flex items-center">
                            <svg class="animate-spin inline-flex items-center -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            @lang('modules.order.bill')
                        </span>
                    </button>
                    <button class="rounded bg-green-500 text-white w-full p-2 relative" @click="submitOrder('bill', 'payment')" :disabled="isSubmitting || cartItems.length === 0" :class="{ 'opacity-50 cursor-not-allowed': isSubmitting || cartItems.length === 0 }">
                        <span x-show="!isSubmitting || submitType !== 'bill_payment'">@lang('modules.order.billAndPayment')</span>
                        <span x-show="isSubmitting && submitType === 'bill_payment'" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-flex items-center" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            @lang('modules.order.billAndPayment')
                        </span>
                    </button>
                    <button class="rounded bg-blue-500 text-white w-full p-2 relative" @click="submitOrder('bill', 'print')" :disabled="isSubmitting || cartItems.length === 0" :class="{ 'opacity-50 cursor-not-allowed': isSubmitting || cartItems.length === 0 }">
                        <span x-show="!isSubmitting || submitType !== 'bill_print'">@lang('modules.order.createBillAndPrintReceipt')</span>
                        <span x-show="isSubmitting && submitType === 'bill_print'" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            @lang('modules.order.createBillAndPrintReceipt')
                        </span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

@script
<script>
Alpine.data('posCartOffline', () => ({
    cartItems: [],
    isOnline: navigator.onLine,
    isSynced: true,
    isSubmitting: false,
    submitType: '',
    taxPercent: {{ $restaurant->tax_percent ?? 0 }},
    taxInclusive: {{ $restaurant->tax_inclusive ? 'true' : 'false' }},
    currencySymbol: '{{ $restaurant->currency->currency_symbol ?? '$' }}',
    currencyPosition: '{{ $restaurant->currency->currency_position ?? 'before' }}',

    get subTotal() {
        return this.cartItems.reduce((sum, item) => sum + item.amount, 0);
    },

    get taxAmount() {
        if (this.taxInclusive) {
            return this.subTotal - (this.subTotal / (1 + this.taxPercent / 100));
        }
        return this.subTotal * (this.taxPercent / 100);
    },

    get total() {
        if (this.taxInclusive) {
            return this.subTotal;
        }
        return this.subTotal + this.taxAmount;
    },

    formatCurrency(amount) {
        const formatted = parseFloat(amount).toFixed(2);
        if (this.currencyPosition === 'before') {
            return this.currencySymbol + formatted;
        }
        return formatted + this.currencySymbol;
    },

    async init() {
        // Load cart from PosCartManager on init
        this.loadCart();

        // Listen for online/offline events
        window.addEventListener('online', () => {
            this.isOnline = true;
        });
        window.addEventListener('offline', () => {
            this.isOnline = false;
        });
    },

    loadCart() {
        if (typeof PosCartManager !== 'undefined' && PosCartManager.cart) {
            // PosCartManager.cart is an array, convert to our format
            this.cartItems = PosCartManager.cart.map(item => ({
                cartKey: item.key,
                id: item.item_id,
                item_name: item.item_name,
                price: item.price,
                qty: item.qty,
                variation: item.variation_id ? { id: item.variation_id, variation_name: item.variation_name } : null,
                modifiers: item.modifiers || [],
                modifiersPrice: item.modifiers_price || 0,
                amount: (item.price + (item.modifiers_price || 0)) * item.qty,
                note: item.note || ''
            }));
        }
    },

    handleCartUpdate(detail) {
        // Cart was updated from menu-offline, sync our state
        if (detail && detail.cart) {
            this.cartItems = detail.cart.map(item => ({
                cartKey: item.key,
                id: item.item_id,
                item_name: item.item_name,
                price: item.price,
                qty: item.qty,
                variation: item.variation_id ? { id: item.variation_id, variation_name: item.variation_name } : null,
                modifiers: item.modifiers || [],
                modifiersPrice: item.modifiers_price || 0,
                amount: (item.price + (item.modifiers_price || 0)) * item.qty,
                note: item.note || ''
            }));
            this.isSynced = false;
        }
    },

    saveCart() {
        // Sync local cart state back to PosCartManager
        if (typeof PosCartManager !== 'undefined') {
            // Convert our format back to PosCartManager format
            PosCartManager.cart = this.cartItems.map(item => ({
                key: item.cartKey,
                item_id: item.id,
                item_name: item.item_name,
                variation_id: item.variation?.id || null,
                variation_name: item.variation?.variation_name || null,
                modifier_ids: (item.modifiers || []).map(m => m.id),
                modifiers: item.modifiers || [],
                price: item.price,
                modifiers_price: item.modifiersPrice || 0,
                qty: item.qty,
                note: item.note || '',
                type: item.type,
                image: item.image
            }));
            PosCartManager.saveToStorage();
            this.isSynced = false;
        }
    },

    handleItemAdded(detail) {
        // Play beep sound
        new Audio("{{ asset('sound/sound_beep-29.mp3') }}").play();

        const { item, variation, modifiers } = detail;

        // Generate unique cart key
        const cartKey = this.generateCartKey(item.id, variation?.id, modifiers);

        // Check if item already exists in cart
        const existingIndex = this.cartItems.findIndex(ci => ci.cartKey === cartKey);

        if (existingIndex >= 0) {
            // Increment quantity
            this.cartItems[existingIndex].qty += 1;
            this.cartItems[existingIndex].amount = this.calculateItemAmount(this.cartItems[existingIndex]);
        } else {
            // Add new item
            const basePrice = variation?.price ?? item.price;
            const modifiersPrice = modifiers ? modifiers.reduce((sum, m) => sum + (m.price || 0), 0) : 0;

            const newItem = {
                cartKey,
                id: item.id,
                item_name: item.item_name,
                price: basePrice,
                qty: 1,
                variation: variation || null,
                modifiers: modifiers || [],
                modifiersPrice,
                amount: basePrice + modifiersPrice,
                note: ''
            };

            this.cartItems.push(newItem);
        }

        this.saveCart();
    },

    handleItemWithVariation(detail) {
        // Variations are handled by the menu-offline Alpine modal
        // This event is just for logging/tracking purposes
        console.log('Item with variation event received:', detail);
    },

    handleItemWithModifiers(detail) {
        // Modifiers are handled by the menu-offline Alpine modal
        // This event is just for logging/tracking purposes
        console.log('Item with modifiers event received:', detail);
    },

    generateCartKey(itemId, variationId, modifiers) {
        let key = `item_${itemId}`;
        if (variationId) {
            key += `_var_${variationId}`;
        }
        if (modifiers && modifiers.length > 0) {
            const modIds = modifiers.map(m => m.id).sort().join('_');
            key += `_mod_${modIds}`;
        }
        return key;
    },

    calculateItemAmount(item) {
        return (item.price + (item.modifiersPrice || 0)) * item.qty;
    },

    incrementQty(index) {
        this.cartItems[index].qty += 1;
        this.cartItems[index].amount = this.calculateItemAmount(this.cartItems[index]);
        this.saveCart();
    },

    decrementQty(index) {
        if (this.cartItems[index].qty > 1) {
            this.cartItems[index].qty -= 1;
            this.cartItems[index].amount = this.calculateItemAmount(this.cartItems[index]);
            this.saveCart();
        } else {
            this.removeItem(index);
        }
    },

    updateItemQty(index, qty) {
        if (qty < 1) qty = 1;
        this.cartItems[index].qty = qty;
        this.cartItems[index].amount = this.calculateItemAmount(this.cartItems[index]);
        this.saveCart();
    },

    removeItem(index) {
        this.cartItems.splice(index, 1);
        this.saveCart();
    },

    async submitOrder(...args) {
        if (this.isSubmitting || this.cartItems.length === 0) return;

        this.isSubmitting = true;
        this.submitType = args.join('_');

        try {
            // Prepare cart data for Livewire bulk submission
            const cartData = this.cartItems.map(item => ({
                menu_item_id: item.id,
                variation_id: item.variation?.id || null,
                qty: item.qty,
                modifiers: item.modifiers.map(m => ({ id: m.id, price: m.price })),
                note: item.note || ''
            }));

            // Call Livewire method with bulk cart data
            await @this.call('submitBulkCart', cartData, args);

            // Clear local cart on successful submission
            this.cartItems = [];
            await this.saveCart();
            this.isSynced = true;

        } catch (error) {
            console.error('Order submission failed:', error);
            // Cart remains intact for retry
        } finally {
            this.isSubmitting = false;
            this.submitType = '';
        }
    }
}));
</script>
@endscript
