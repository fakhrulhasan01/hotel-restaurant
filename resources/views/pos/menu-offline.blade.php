<div class="w-full">
    @php
        $orderStats = getRestaurantOrderStats(branch()->id);
        $orderLimitReached = !$orderStats['unlimited'] && $orderStats['current_count'] >= $orderStats['order_limit'];
        $currencySymbol = restaurant()->currency->currency_symbol ?? '$';
    @endphp

    <div x-data="posMenuOffline({
        orderLimitReached: {{ $orderLimitReached ? 'true' : 'false' }},
        currencySymbol: '{{ $currencySymbol }}',
        hideImage: {{ restaurant()->hide_menu_item_image_on_pos ? 'true' : 'false' }},
        currencyId: {{ restaurant()->currency_id }},
        locale: '{{ session('locale', app()->getLocale()) }}',
        baseUrl: '{{ url('/') }}',
        orderTypeId: @js($orderTypeId),
        deliveryAppId: @js($selectedDeliveryApp ?? null)
    })" x-init="init()" class="relative"
        @cart-updated.window="refreshCartDisplay($event.detail)"
        @item-added.window="showItemAddedFeedback($event.detail)">

        <!-- Offline Status Indicator -->
        <div x-show="!isOnline" x-cloak
            class="fixed top-0 left-0 right-0 z-50 bg-yellow-500 text-white text-center py-1 text-sm">
            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"></path>
            </svg>
            @lang('messages.offlineMode') - @lang('messages.dataWillSyncWhenOnline')
        </div>

        <!-- Sync Status -->
        <div x-show="syncing" x-cloak
            class="fixed top-0 left-0 right-0 z-50 bg-blue-500 text-white text-center py-1 text-sm">
            <svg class="animate-spin w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            @lang('messages.syncingData')...
        </div>

        <!-- Mobile Toggle Button -->
        <button
            @click="showMenu = !showMenu"
            class="fixed bottom-6 right-6 z-50 md:hidden bg-skin-base text-white rounded-full shadow-lg p-4 flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-skin-base transition"
            aria-label="Toggle Menu"
            type="button"
        >
            <svg x-show="!showMenu" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            <svg x-show="showMenu" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Menu Panel -->
        <div :class="{'hidden': !showMenu, 'inset-0 z-40 flex': showMenu}"
            class="md:flex flex-col bg-gray-50 lg:h-full w-full py-4 px-3 dark:bg-gray-900 transition-transform duration-300 md:static md:inset-auto md:z-auto md:translate-x-0 overflow-y-auto md:overflow-visible md:max-h-none"
            style="backdrop-filter: blur(2px);" x-cloak>

            {{-- Loading Overlay --}}
            <div x-show="loading" x-cloak class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 z-50 flex items-center justify-center">
                <div class="text-center">
                    <svg class="animate-spin h-10 w-10 text-skin-base mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400" x-text="loadingMessage"></p>
                </div>
            </div>

            {{-- Search and Reset Section --}}
            <div class="flex items-center justify-between gap-3">
                <div class="flex-1">
                    <label for="products-search" class="sr-only">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                            </svg>
                        </div>
                        <input type="text"
                            x-model="search"
                            class="block w-full pl-10 pr-10 py-2 border-gray-200 rounded-lg text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-skin-base focus:border-skin-base"
                            placeholder="{{ __('placeholders.searchMenuItems') }}" />
                        <button x-show="search" x-cloak type="button" @click="search = ''"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-5 h-5" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 20 4 4m16 0L4 20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button @click="forceSync()" :disabled="syncing"
                    class="inline-flex items-center px-3 py-2 gap-1 text-sm bg-skin-base text-white rounded-lg hover:opacity-90 disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" :class="{'animate-spin': syncing}" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                    </svg>
                    <span x-show="!syncing">@lang('app.sync')</span>
                    <span x-show="syncing">...</span>
                </button>
            </div>

            {{-- Menu Tabs --}}
            <div class="flex gap-2 mt-4 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 flex-wrap">
                <button @click="selectedMenuId = null"
                    :class="selectedMenuId === null ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700'"
                    class="px-3 py-1.5 text-sm font-medium rounded-lg whitespace-nowrap transition-colors">
                    @lang('app.showAll')
                </button>
                <template x-for="menu in menus" :key="menu.id">
                    <button @click="selectedMenuId = menu.id"
                        :class="selectedMenuId === menu.id ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700'"
                        class="px-3 py-1.5 text-sm font-medium rounded-lg whitespace-nowrap transition-colors"
                        x-text="menu.menu_name">
                    </button>
                </template>
            </div>

            {{-- Categories Section --}}
            <div class="flex gap-2 mt-4 overflow-x-auto pb-2 border-b dark:border-gray-700 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 flex-wrap">
                <button @click="selectedCategoryId = null"
                    :class="selectedCategoryId === null ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700'"
                    class="px-3 py-1.5 text-sm font-medium rounded-lg whitespace-nowrap transition-colors">
                    @lang('app.showAll')
                </button>
                <template x-for="category in filteredCategories" :key="category.id">
                    <button @click="selectedCategoryId = category.id"
                        :class="selectedCategoryId === category.id ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700'"
                        class="px-3 py-1.5 text-sm font-medium rounded-lg whitespace-nowrap transition-colors">
                        <span x-text="category.category_name"></span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-full px-1 py-0.5 ml-1" x-text="getCategoryItemCount(category.id)"></span>
                    </button>
                </template>
            </div>

            {{-- Menu Items Grid --}}
            <div class="mt-4 overflow-y-auto max-h-[calc(100vh-18.75rem)] md:max-h-[calc(100vh-20rem)] lg:max-h-[calc(100vh-22rem)]
                [&::-webkit-scrollbar]:w-2
                [&::-webkit-scrollbar-track]:bg-gray-300
                [&::-webkit-scrollbar-thumb]:bg-gray-400
                hover:[&::-webkit-scrollbar-thumb]:bg-gray-500
                dark:[&::-webkit-scrollbar-track]:bg-gray-700
                dark:[&::-webkit-scrollbar-thumb]:bg-gray-500
                dark:hover:[&::-webkit-scrollbar-thumb]:bg-gray-400">

                <ul class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-8 gap-3">
                    <template x-for="item in filteredItems" :key="item.id">
                        <li class="group relative">
                            <button type="button"
                                @click="handleItemClick(item)"
                                :disabled="orderLimitReached || !item.in_stock"
                                :class="{
                                    'cursor-pointer hover:shadow-md dark:hover:bg-gray-700/30 active:scale-95': !orderLimitReached && item.in_stock,
                                    'cursor-not-allowed opacity-60': orderLimitReached || !item.in_stock,
                                    'bg-gray-100 dark:bg-gray-800': !item.in_stock,
                                    'bg-white dark:bg-gray-900': item.in_stock && !orderLimitReached,
                                    'bg-gray-200 dark:bg-gray-800': orderLimitReached,
                                    'ring-2 ring-green-500': itemJustAdded === item.id
                                }"
                                class="block w-full rounded-lg shadow-sm transition-all duration-100 dark:shadow-gray-700 relative outline-none text-left">

                                {{-- Image Section --}}
                                <template x-if="!hideImage">
                                    <div class="relative aspect-square hidden md:block">
                                        <img class="w-full h-full object-cover rounded-t-lg"
                                            :src="item.image"
                                            :alt="item.item_name"
                                            loading="lazy" />
                                        <span class="absolute top-1 right-1 bg-white/90 dark:bg-gray-800/90 rounded-full p-1 shadow-sm">
                                            <img :src="'/img/' + item.type + '.svg'"
                                                class="h-4 w-4"
                                                :title="item.type"
                                                alt="" />
                                        </span>
                                    </div>
                                </template>

                                {{-- Content Section --}}
                                <div class="p-2">
                                    <h5 class="text-sm font-medium text-gray-900 dark:text-white min-h-[2.5rem]" x-text="item.item_name"></h5>

                                    <template x-if="orderLimitReached">
                                        <div class="text-red-500 text-xs">@lang('messages.orderLimitReached')</div>
                                    </template>
                                    <template x-if="!orderLimitReached && !item.in_stock">
                                        <div class="text-red-500">@lang('messages.outOfStock')</div>
                                    </template>
                                    <template x-if="!orderLimitReached && item.in_stock">
                                        <div class="mt-1 flex items-center justify-between gap-2">
                                            <template x-if="!item.variations || item.variations.length === 0">
                                                <span class="text-base font-semibold text-gray-900 dark:text-white" x-text="formatCurrency(item.price)"></span>
                                            </template>
                                            <template x-if="item.variations && item.variations.length > 0">
                                                <span class="text-xs text-gray-600 dark:text-gray-300 flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                    </svg>
                                                    @lang('modules.menu.showVariations')
                                                </span>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </button>
                        </li>
                    </template>

                    {{-- Empty State --}}
                    <template x-if="filteredItems.length === 0 && !loading">
                        <li class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                                <p>@lang('messages.noItemAdded')</p>
                            </div>
                        </li>
                    </template>
                </ul>

                {{-- Items count --}}
                <div class="flex items-center justify-center py-6 px-4">
                    <div class="flex items-center gap-x-1 text-gray-500 dark:text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0"/>
                        </svg>
                        <span class="text-sm font-medium" x-text="filteredItems.length + ' @lang('app.items')'"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Variation Modal (Offline) --}}
        <div x-ref="variationModal"
             x-show="offlineVariationModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             @keydown.escape.window="offlineVariationModal = false; $refs.variationModal.style.display = 'none';">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="offlineVariationModal = false; $refs.variationModal.style.display = 'none';"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        @lang('modules.menu.itemVariations')
                        <span x-show="selectedItem" x-text="selectedItem ? ' - ' + selectedItem.item_name : ''"></span>
                    </h3>

                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <template x-for="variation in modalVariations" :key="variation.id">
                            <button type="button" @click.prevent.stop="
                                $refs.variationModal.style.display = 'none';
                                offlineVariationModal = false;
                                handleVariationClick(variation);
                            "
                                class="w-full p-3 text-left border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-gray-900 dark:text-white" x-text="variation.variation_name"></span>
                                    <span class="text-skin-base font-semibold" x-text="formatCurrency(variation.price)"></span>
                                </div>
                            </button>
                        </template>
                    </div>

                    <div x-show="modalVariations.length === 0" class="py-4 text-center text-gray-500">
                        @lang('messages.noVariationsFound')
                    </div>

                    <button type="button" @click="offlineVariationModal = false; $refs.variationModal.style.display = 'none'; clearModalState();"
                        class="mt-4 w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                        @lang('app.cancel')
                    </button>
                </div>
            </div>
        </div>

        {{-- Modifier Modal (Offline) --}}
        <div x-ref="modifierModal"
             x-show="offlineModifierModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             @keydown.escape.window="offlineModifierModal = false; $refs.modifierModal.style.display = 'none';">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="offlineModifierModal = false; $refs.modifierModal.style.display = 'none';"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        @lang('modules.modifier.itemModifiers')
                        <span x-show="selectedItem" x-text="selectedItem ? ' - ' + selectedItem.item_name : ''"></span>
                    </h3>

                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        <template x-for="group in modalModifierGroups" :key="group.id">
                            <div class="border-b dark:border-gray-700 pb-3">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-2" x-text="group.name"></h4>
                                <div class="space-y-2">
                                    <template x-for="option in (group.options || [])" :key="option.id">
                                        <label class="flex items-center gap-3 p-2 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                                            :class="{'border-skin-base bg-skin-base/5': selectedModifiers.includes(option.id)}">
                                            <input type="checkbox" :value="option.id" x-model="selectedModifiers"
                                                class="rounded text-skin-base focus:ring-skin-base">
                                            <span class="flex-1 text-gray-900 dark:text-white" x-text="option.name"></span>
                                            <span class="text-skin-base" x-text="option.price > 0 ? '+' + formatCurrency(option.price) : ''"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="modalModifierGroups.length === 0" class="py-4 text-center text-gray-500">
                        @lang('messages.noModifiersFound')
                    </div>

                    <div class="flex gap-3 mt-4">
                        <button @click="offlineModifierModal = false; $refs.modifierModal.style.display = 'none'; clearModalState();"
                            class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                            @lang('app.cancel')
                        </button>
                        <button @click="addItemWithModifiers()"
                            class="flex-1 px-4 py-2 bg-skin-base text-white rounded-lg hover:opacity-90">
                            @lang('app.add')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('posMenuOffline', (config) => ({
        // Config
        orderLimitReached: config.orderLimitReached,
        currencySymbol: config.currencySymbol,
        hideImage: config.hideImage,
        currencyId: config.currencyId,
        locale: config.locale,
        baseUrl: config.baseUrl,
        orderTypeId: config.orderTypeId,
        deliveryAppId: config.deliveryAppId,

        // State
        showMenu: false,
        loading: true,
        loadingMessage: '{{ __("messages.loadingMenuData") }}...',
        syncing: false,
        isOnline: navigator.onLine,
        menus: [],
        categories: [],
        items: [],
        taxes: [],
        search: '',
        selectedMenuId: null,
        selectedCategoryId: null,
        itemJustAdded: null,

        // Modals (using unique names to avoid Livewire collision)
        offlineVariationModal: false,
        offlineModifierModal: false,
        selectedItem: null,
        selectedVariation: null,
        selectedModifiers: [],
        // Store variations/modifiers separately to avoid reactivity issues
        modalVariations: [],
        modalModifierGroups: [],

        // Initialize
        async init() {
            this.loading = true;

            // Setup online/offline detection
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.syncIfNeeded();
            });
            window.addEventListener('offline', () => {
                this.isOnline = false;
            });

            try {
                // Initialize IndexedDB
                await PosOfflineDB.init();

                // Try to load from cache first for instant display
                await this.loadFromCache();

                // Then sync with server if online and needed
                if (this.isOnline) {
                    await this.syncIfNeeded();
                }
            } catch (error) {
                console.error('Failed to initialize POS:', error);
                // Fallback to direct API call
                await this.loadFromAPI();
            } finally {
                this.loading = false;
            }

            // Initialize cart manager
            PosCartManager.init(sessionStorage.getItem('pos_session_id'));
            if (!sessionStorage.getItem('pos_session_id')) {
                sessionStorage.setItem('pos_session_id', PosCartManager.sessionId);
            }
        },

        async loadFromCache() {
            this.loadingMessage = '{{ __("messages.loadingFromCache") }}...';

            const [menus, categories, items, taxes] = await Promise.all([
                PosOfflineDB.getAll('menus'),
                PosOfflineDB.getAll('categories'),
                PosOfflineDB.getAll('menuItems'),
                PosOfflineDB.getAll('taxes')
            ]);

            if (menus.length > 0) this.menus = menus;
            if (categories.length > 0) this.categories = categories;
            if (items.length > 0) this.items = items;
            if (taxes.length > 0) this.taxes = taxes;

            return menus.length > 0 && items.length > 0;
        },

        async syncIfNeeded() {
            const needsSync = await PosOfflineDB.needsSync(30 * 60 * 1000); // 30 minutes
            if (needsSync || this.items.length === 0) {
                await this.forceSync();
            }
        },

        async forceSync() {
            if (!this.isOnline || this.syncing) return;

            this.syncing = true;
            this.loadingMessage = '{{ __("messages.syncingWithServer") }}...';

            try {
                // Load all data from API including variations
                const [menusRes, categoriesRes, itemsRes, taxesRes] = await Promise.all([
                    fetch(`${this.baseUrl}/api/pos/menus`),
                    fetch(`${this.baseUrl}/api/pos/categories`),
                    fetch(`${this.baseUrl}/api/pos/items-with-variations?order_type_id=${this.orderTypeId || ''}&delivery_app_id=${this.deliveryAppId || ''}`),
                    fetch(`${this.baseUrl}/api/pos/taxes`)
                ]);

                const [menus, categories, items, taxes] = await Promise.all([
                    menusRes.json(),
                    categoriesRes.json(),
                    itemsRes.json(),
                    taxesRes.json()
                ]);

                // Update state
                this.menus = menus;
                this.categories = categories;
                this.items = items;
                this.taxes = taxes;

                // Save to IndexedDB
                await Promise.all([
                    PosOfflineDB.putAll('menus', menus),
                    PosOfflineDB.putAll('categories', categories),
                    PosOfflineDB.putAll('menuItems', items),
                    PosOfflineDB.putAll('taxes', taxes)
                ]);

                await PosOfflineDB.setLastSync();

            } catch (error) {
                console.error('Sync failed:', error);
            } finally {
                this.syncing = false;
            }
        },

        async loadFromAPI() {
            this.loadingMessage = '{{ __("messages.loadingFromServer") }}...';
            try {
                const [menusRes, categoriesRes, itemsRes, taxesRes] = await Promise.all([
                    fetch(`${this.baseUrl}/api/pos/menus`),
                    fetch(`${this.baseUrl}/api/pos/categories`),
                    fetch(`${this.baseUrl}/api/pos/items-with-variations?order_type_id=${this.orderTypeId || ''}&delivery_app_id=${this.deliveryAppId || ''}`),
                    fetch(`${this.baseUrl}/api/pos/taxes`)
                ]);

                this.menus = await menusRes.json();
                this.categories = await categoriesRes.json();
                this.items = await itemsRes.json();
                this.taxes = await taxesRes.json();
            } catch (error) {
                console.error('Failed to load from API:', error);
            }
        },

        // Computed: Filter categories based on selected menu
        get filteredCategories() {
            if (!this.selectedMenuId) {
                return this.categories;
            }
            const categoryIds = new Set(
                this.items
                    .filter(item => item.menu_id === this.selectedMenuId)
                    .map(item => item.item_category_id)
            );
            return this.categories.filter(cat => categoryIds.has(cat.id));
        },

        // Computed: Filter items based on menu, category, and search
        get filteredItems() {
            let filtered = this.items;

            if (this.selectedMenuId) {
                filtered = filtered.filter(item => item.menu_id === this.selectedMenuId);
            }

            if (this.selectedCategoryId) {
                filtered = filtered.filter(item => item.item_category_id === this.selectedCategoryId);
            }

            if (this.search.trim()) {
                const searchLower = this.search.toLowerCase().trim();
                filtered = filtered.filter(item =>
                    item.item_name.toLowerCase().includes(searchLower)
                );
            }

            return filtered;
        },

        getCategoryItemCount(categoryId) {
            let items = this.items;
            if (this.selectedMenuId) {
                items = items.filter(item => item.menu_id === this.selectedMenuId);
            }
            return items.filter(item => item.item_category_id === categoryId).length;
        },

        formatCurrency(amount) {
            return this.currencySymbol + parseFloat(amount || 0).toFixed(2);
        },

        // Handle item click - check for variations/modifiers
        handleItemClick(item) {
            if (this.orderLimitReached || !item.in_stock) return;

            // Play beep
            this.playBeep();

            // Deep copy the item and store in window to survive Livewire re-renders
            const itemCopy = JSON.parse(JSON.stringify(item));
            window._posSelectedItem = itemCopy;
            window._posModalVariations = itemCopy.variations || [];
            window._posModalModifierGroups = itemCopy.modifier_groups || [];

            // Also set in Alpine state for display
            this.selectedItem = itemCopy;
            this.selectedVariation = null;
            this.selectedModifiers = [];
            this.modalVariations = window._posModalVariations;
            this.modalModifierGroups = window._posModalModifierGroups;

            console.log('Item clicked:', itemCopy.item_name, 'Stored in window:', window._posSelectedItem);

            if (this.modalVariations.length > 0) {
                this.offlineVariationModal = true;
                // Reset display style so modal shows
                if (this.$refs.variationModal) {
                    this.$refs.variationModal.style.display = '';
                }
            } else if (this.modalModifierGroups.length > 0) {
                this.offlineModifierModal = true;
                if (this.$refs.modifierModal) {
                    this.$refs.modifierModal.style.display = '';
                }
            } else {
                this.addToCart(itemCopy);
            }
        },

        // Handle variation button click - close modal and add to cart
        handleVariationClick(variation) {
            console.log('handleVariationClick called with variation:', variation);

            // Get item from window storage
            const item = window._posSelectedItem || this.selectedItem;
            console.log('Item from storage:', item);

            if (!item) {
                console.error('No item selected');
                this.clearModalState();
                return;
            }

            this.selectedVariation = variation;
            const modifierGroups = window._posModalModifierGroups || this.modalModifierGroups || [];

            if (modifierGroups.length > 0) {
                // Has modifiers, show modifier modal
                this.offlineModifierModal = true;
                if (this.$refs.modifierModal) {
                    this.$refs.modifierModal.style.display = '';
                }
            } else {
                // No modifiers, add directly to cart
                console.log('Adding to cart directly');
                this.addToCart(item, variation);
                this.clearModalState();
            }
        },

        addItemWithModifiers() {
            const item = window._posSelectedItem || this.selectedItem;
            if (!item) {
                console.error('No item selected');
                return;
            }

            const modifiers = [];
            const modifierGroups = window._posModalModifierGroups || this.modalModifierGroups || [];
            modifierGroups.forEach(group => {
                (group.options || []).forEach(option => {
                    if (this.selectedModifiers.includes(option.id)) {
                        modifiers.push(option);
                    }
                });
            });

            this.addToCart(item, this.selectedVariation, modifiers);

            // Close modifier modal
            this.offlineModifierModal = false;
            if (this.$refs.modifierModal) {
                this.$refs.modifierModal.style.display = 'none';
            }
            this.clearModalState();
        },

        clearModalState() {
            window._posSelectedItem = null;
            window._posModalVariations = [];
            window._posModalModifierGroups = [];
            this.selectedItem = null;
            this.selectedVariation = null;
            this.selectedModifiers = [];
            this.modalVariations = [];
            this.modalModifierGroups = [];

            // Make sure both modals are hidden
            if (this.$refs.variationModal) {
                this.$refs.variationModal.style.display = 'none';
            }
            if (this.$refs.modifierModal) {
                this.$refs.modifierModal.style.display = 'none';
            }
        },

        addToCart(item, variation = null, modifiers = []) {
            if (!item) {
                console.error('No item to add to cart');
                return;
            }

            // Add to local cart
            const cart = PosCartManager.addItem(item, variation, modifiers);

            // Show feedback
            this.showItemAddedFeedback({ itemId: item.id });

            // Dispatch event to update cart display
            this.$dispatch('cart-updated', { cart: cart, taxes: this.taxes });

            // Also notify Livewire for any server-side state that might need updating
            // This is a lightweight notification, not a full sync
            @this.set('orderItemList', this.buildLivewireCartState(cart), false);
        },

        buildLivewireCartState(cart) {
            const state = {};
            cart.forEach((item, index) => {
                state[item.key] = {
                    id: item.item_id,
                    item_name: item.item_name
                };
            });
            return state;
        },

        showItemAddedFeedback(detail) {
            this.itemJustAdded = detail.itemId;
            setTimeout(() => {
                this.itemJustAdded = null;
            }, 300);
        },

        refreshCartDisplay(detail) {
            // Cart updated externally
        },

        playBeep() {
            try {
                const audio = new Audio(`${this.baseUrl}/sound/sound_beep-29.mp3`);
                audio.volume = 0.3;
                audio.play();
            } catch (e) {}
        }
    }));
});
</script>
