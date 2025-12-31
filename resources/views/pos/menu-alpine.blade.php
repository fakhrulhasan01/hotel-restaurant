<div class="w-full">
    @php
        $orderStats = getRestaurantOrderStats(branch()->id);
        $orderLimitReached = !$orderStats['unlimited'] && $orderStats['current_count'] >= $orderStats['order_limit'];
        $currencySymbol = restaurant()->currency->currency_symbol ?? '$';
    @endphp

    <div x-data="posMenu({
        orderLimitReached: {{ $orderLimitReached ? 'true' : 'false' }},
        currencySymbol: '{{ $currencySymbol }}',
        hideImage: {{ restaurant()->hide_menu_item_image_on_pos ? 'true' : 'false' }},
        currencyId: {{ restaurant()->currency_id }},
        locale: '{{ session('locale', app()->getLocale()) }}'
    })" x-init="init()" class="relative">

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
                <svg class="animate-spin h-10 w-10 text-skin-base" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
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
                            x-model.debounce.300ms="search"
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

                <a href="{{ route('pos.index') }}" class="inline-flex items-center px-3 py-2 gap-1 text-sm bg-skin-base text-white rounded-lg hover:opacity-90">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                    </svg>
                    @lang('app.reset')
                </a>
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
                        <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-full px-1 py-0.5 ml-1" x-text="category.count"></span>
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
                                @click="addToCart(item)"
                                :disabled="orderLimitReached || !item.in_stock || addingItemId === item.id"
                                :class="{
                                    'cursor-pointer hover:shadow-md dark:hover:bg-gray-700/30 active:scale-95': !orderLimitReached && item.in_stock,
                                    'cursor-not-allowed opacity-60': orderLimitReached || !item.in_stock,
                                    'bg-gray-100 dark:bg-gray-800': !item.in_stock,
                                    'bg-white dark:bg-gray-900': item.in_stock && !orderLimitReached,
                                    'bg-gray-200 dark:bg-gray-800': orderLimitReached
                                }"
                                class="block w-full rounded-lg shadow-sm transition-all duration-100 dark:shadow-gray-700 relative outline-none text-left">

                                {{-- Loading Overlay for this item --}}
                                <div x-show="addingItemId === item.id" x-cloak
                                    class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 rounded-lg z-10 flex items-center justify-center">
                                    <svg class="animate-spin h-6 w-6 text-skin-base" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>

                                {{-- Image Section --}}
                                <template x-if="!hideImage">
                                    <div class="relative aspect-square hidden md:block">
                                        <img class="w-full h-full object-cover rounded-t-lg"
                                            :src="item.image"
                                            :alt="item.item_name" />
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
                                        <div class="text-red-500">Out of stock</div>
                                    </template>
                                    <template x-if="!orderLimitReached && item.in_stock">
                                        <div class="mt-1 flex items-center justify-between gap-2">
                                            <template x-if="item.variations_count === 0">
                                                <span class="text-base font-semibold text-gray-900 dark:text-white" x-text="formatCurrency(item.price)"></span>
                                            </template>
                                            <template x-if="item.variations_count > 0">
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
                        <span class="text-sm font-medium" x-text="filteredItems.length + ' items'"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('posMenu', (config) => ({
        // Config
        orderLimitReached: config.orderLimitReached,
        currencySymbol: config.currencySymbol,
        hideImage: config.hideImage,
        currencyId: config.currencyId,
        locale: config.locale,

        // State
        showMenu: false,
        loading: true,
        menus: [],
        categories: [],
        items: [],
        search: '',
        selectedMenuId: null,
        selectedCategoryId: null,
        addingItemId: null,

        // Initialize - load all data once
        async init() {
            this.loading = true;
            try {
                // Load all data in parallel
                const [menusRes, categoriesRes, itemsRes] = await Promise.all([
                    fetch('{{ route("pos-api.menus") }}'),
                    fetch('{{ route("pos-api.categories") }}'),
                    fetch('{{ route("pos-api.items") }}')
                ]);

                this.menus = await menusRes.json();
                this.categories = await categoriesRes.json();
                this.items = await itemsRes.json();
            } catch (error) {
                console.error('Failed to load menu data:', error);
            } finally {
                this.loading = false;
            }
        },

        // Computed: Filter categories based on selected menu
        get filteredCategories() {
            if (!this.selectedMenuId) {
                return this.categories;
            }
            // Get category IDs that have items in the selected menu
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

            // Filter by menu
            if (this.selectedMenuId) {
                filtered = filtered.filter(item => item.menu_id === this.selectedMenuId);
            }

            // Filter by category
            if (this.selectedCategoryId) {
                filtered = filtered.filter(item => item.item_category_id === this.selectedCategoryId);
            }

            // Filter by search
            if (this.search.trim()) {
                const searchLower = this.search.toLowerCase().trim();
                filtered = filtered.filter(item =>
                    item.item_name.toLowerCase().includes(searchLower)
                );
            }

            return filtered;
        },

        // Format currency
        formatCurrency(amount) {
            return this.currencySymbol + parseFloat(amount).toFixed(2);
        },

        // Add item to cart - calls Livewire
        async addToCart(item) {
            if (this.orderLimitReached || !item.in_stock) return;

            this.addingItemId = item.id;
            try {
                // Call Livewire method to add to cart
                await @this.addCartItems(item.id, item.variations_count, item.modifier_groups_count);
            } catch (error) {
                console.error('Failed to add item:', error);
            } finally {
                this.addingItemId = null;
            }
        }
    }));
});
</script>
