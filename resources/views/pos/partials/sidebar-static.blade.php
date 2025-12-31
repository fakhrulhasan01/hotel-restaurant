<aside id="sidebar"
    class="fixed top-0 ltr:left-0 rtl:right-0 z-20 flex flex-col flex-shrink-0 hidden w-64 h-full pt-16 font-normal duration-75 lg:flex transition-width menu-collapsed:hidden"
    aria-label="Sidebar">
    <div class="relative flex flex-col flex-1 min-h-0 pt-0 bg-gray-50 ltr:border-r rtl:border-l border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex flex-col flex-1 pt-5 pb-4 overflow-y-auto mb-16 [&::-webkit-scrollbar]:w-1.5
            [&::-webkit-scrollbar-track]:rounded-xl
            [&::-webkit-scrollbar-thumb]:rounded-xl
            [&::-webkit-scrollbar-track]:bg-gray-300
            [&::-webkit-scrollbar-thumb]:bg-gray-400
            hover:[&::-webkit-scrollbar-thumb]:bg-gray-500
            dark:[&::-webkit-scrollbar-track]:bg-gray-700
            dark:[&::-webkit-scrollbar-thumb]:bg-gray-500
            dark:hover:[&::-webkit-scrollbar-thumb]:bg-gray-400">
            <div class="flex-1 px-3 space-y-1 bg-gray-50 divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">

                {{-- Branch Name Display --}}
                <div class="text-sm text-gray-500 dark:text-gray-400 py-2 px-3 font-semibold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16">
                        <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"/>
                        <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                    </svg>
                    {{ branch()->name }}
                </div>

                <ul class="py-2 space-y-2">

                    {{-- Dashboard --}}
                    <li>
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                            </svg>
                            <span class="ml-3">@lang('menu.dashboard')</span>
                        </a>
                    </li>

                    {{-- POS (Active) --}}
                    <li>
                        <a href="{{ route('pos.index') }}"
                            class="flex items-center p-2 text-base text-gray-900 rounded-lg bg-gray-200 dark:bg-gray-700 group dark:text-white">
                            <svg class="w-6 h-6 text-skin-base" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <span class="ml-3 font-semibold">@lang('menu.pos')</span>
                        </a>
                    </li>

                    {{-- Orders --}}
                    @if (in_array('Order', restaurant_modules()) && user_can('Show Order'))
                    <li>
                        <a href="{{ route('orders.index') }}"
                            class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span class="ml-3">@lang('menu.orders')</span>
                        </a>
                    </li>
                    @endif

                    {{-- KOT --}}
                    @if (in_array('KOT', restaurant_modules()) && user_can('Manage KOT'))
                    <li>
                        <a href="{{ route('kots.index') }}"
                            class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span class="ml-3">@lang('menu.kot')</span>
                        </a>
                    </li>
                    @endif

                    {{-- Customers --}}
                    @if (in_array('Customer', restaurant_modules()) && user_can('Show Customer'))
                    <li>
                        <a href="{{ route('customers.index') }}"
                            class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="ml-3">@lang('menu.customers')</span>
                        </a>
                    </li>
                    @endif

                    {{-- Tables --}}
                    @if (in_array('Table', restaurant_modules()) && user_can('Show Table'))
                    <li>
                        <a href="{{ route('tables.index') }}"
                            class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            <span class="ml-3">@lang('menu.tables')</span>
                        </a>
                    </li>
                    @endif

                    {{-- Menu Items --}}
                    @if (in_array('Menu Item', restaurant_modules()) && user_can('Show Menu Item'))
                    <li>
                        <a href="{{ route('menu-items.index') }}"
                            class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <span class="ml-3">@lang('menu.menuItem')</span>
                        </a>
                    </li>
                    @endif

                    {{-- Reports --}}
                    @if (in_array('Report', restaurant_modules()) && user_can('Show Reports'))
                    <li>
                        <a href="{{ route('reports.sales') }}"
                            class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <span class="ml-3">@lang('menu.reports')</span>
                        </a>
                    </li>
                    @endif

                    {{-- Settings --}}
                    @if (in_array('Settings', restaurant_modules()) && user_can('Manage Settings'))
                    <li>
                        <a href="{{ route('settings.index') }}"
                            class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="ml-3">@lang('menu.settings')</span>
                        </a>
                    </li>
                    @endif

                </ul>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 justify-center w-full p-2 space-x-4 bg-white md:flex dark:bg-gray-800 rtl:space-x-reverse" sidebar-bottom-menu>
            <a href="{{ module_enabled('Subdomain') ? 'https://'.restaurant()->sub_domain : route('shop_restaurant', [restaurant()->hash]) }}" target="_blank"
                class="inline-flex justify-center items-center gap-1 p-2 w-full md:w-auto text-gray-500 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-white">
                @lang('menu.customerSite')
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/>
                    <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/>
                </svg>
            </a>
        </div>
    </div>
</aside>

<div class="fixed inset-0 z-10 hidden bg-gray-900/50 dark:bg-gray-900/90" id="sidebarBackdrop"></div>

<script>
    // Sidebar Toggle for Mobile
    const sidebar = document.getElementById('sidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const toggleSidebarMobile = document.getElementById('toggleSidebarMobile');
    const toggleSidebarMobileHamburger = document.getElementById('toggleSidebarMobileHamburger');
    const toggleSidebarMobileClose = document.getElementById('toggleSidebarMobileClose');
    const toggleSidebar = document.getElementById('toggle-sidebar');
    const toggleSidebarOpen = document.getElementById('toggle-sidebar-open');
    const toggleSidebarClose = document.getElementById('toggle-sidebar-close');

    if (toggleSidebarMobile) {
        toggleSidebarMobile.addEventListener('click', function() {
            sidebar.classList.toggle('hidden');
            sidebarBackdrop.classList.toggle('hidden');
            toggleSidebarMobileHamburger.classList.toggle('hidden');
            toggleSidebarMobileClose.classList.toggle('hidden');
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', function() {
            sidebar.classList.add('hidden');
            sidebarBackdrop.classList.add('hidden');
            toggleSidebarMobileHamburger.classList.remove('hidden');
            toggleSidebarMobileClose.classList.add('hidden');
        });
    }

    if (toggleSidebar) {
        toggleSidebar.addEventListener('click', function() {
            document.body.classList.toggle('menu-collapsed');
            toggleSidebarOpen.classList.toggle('hidden');
            toggleSidebarClose.classList.toggle('hidden');
        });
    }
</script>
