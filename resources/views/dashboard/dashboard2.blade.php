<x-app-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Header Section -->
        <div class="px-6 py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <!-- Left: Weather -->
                <div class="flex items-center space-x-3">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                    </svg>
                    <div>
                        <div class="text-2xl font-semibold text-gray-700 dark:text-gray-300">75° <span class="text-base font-normal">Cloudy</span></div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Southlake, TX</div>
                    </div>
                </div>

                <!-- Center: Greeting -->
                <div class="text-3xl font-light text-gray-700 dark:text-gray-300">
                    Good morning, <span class="font-semibold">{{ user()->name ?? 'Jerry' }}</span>
                </div>

                <!-- Right: Time & Print -->
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Updated at 7:00 AM</div>
                    <button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                    </button>
                    <button class="p-2">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Middle Section: 3 Cards -->
        <div class="px-8 py-6">
            <div class="grid gap-4 mb-6" style="grid-template-columns: 30% 40% 30%;">
                <!-- Availability Card -->
                <div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 h-full">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Availability</h3>
                            <button class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-6">
                            <div class="text-center py-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Available Rooms to Sell</div>
                                <div class="text-4xl font-bold text-gray-800 dark:text-gray-200">60</div>
                            </div>

                            <div class="text-center py-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Best Available Rate</div>
                                <div class="text-xs text-gray-400 mb-1">(NSK1)</div>
                                <div class="text-2xl font-bold text-gray-800 dark:text-gray-200">USD 78.50</div>
                            </div>

                            <button class="w-full py-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium text-sm flex items-center justify-center space-x-2">
                                <span>New Reservation</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Reservations Card -->
                <div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 h-full">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Reservations</h3>
                            <button class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Top Stats -->
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="text-center py-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Arriving</div>
                                <div class="text-3xl font-bold text-gray-800 dark:text-gray-200">35</div>
                            </div>
                            <div class="text-center py-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Departing</div>
                                <div class="text-3xl font-bold text-gray-800 dark:text-gray-200">10</div>
                            </div>
                            <div class="text-center py-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">In-house</div>
                                <div class="text-3xl font-bold text-gray-800 dark:text-gray-200">25</div>
                            </div>
                        </div>

                        <!-- Reservation Types Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-600 dark:text-gray-400">GTD</th>
                                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Non-GTD</th>
                                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Walk-in</th>
                                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Cancelled</th>
                                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Day-use</th>
                                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Comp</th>
                                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Groups</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="py-4 px-4 text-2xl font-semibold text-gray-700 dark:text-gray-300">20</td>
                                        <td class="py-4 px-4 text-2xl font-semibold text-gray-700 dark:text-gray-300">15</td>
                                        <td class="py-4 px-4 text-2xl font-semibold text-gray-700 dark:text-gray-300">0</td>
                                        <td class="py-4 px-4 text-2xl font-semibold text-gray-700 dark:text-gray-300">0</td>
                                        <td class="py-4 px-4 text-2xl font-semibold text-gray-700 dark:text-gray-300">1</td>
                                        <td class="py-4 px-4 text-2xl font-semibold text-gray-700 dark:text-gray-300">2</td>
                                        <td class="py-4 px-4 text-2xl font-semibold text-gray-700 dark:text-gray-300">2</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <button class="mt-6 w-full py-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium text-sm flex items-center justify-center space-x-2">
                            <span>Go To Guest Board</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- To-do Card -->
                <div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 h-full">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">To-do</h3>

                        <div class="space-y-2 overflow-y-auto" style="max-height: 400px;">
                            <!-- Events -->
                            <button class="w-full flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Events (2)</span>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Room Requests -->
                            <button class="w-full flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Room Requests (2)</span>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Wake-up Calls -->
                            <button class="w-full flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Wake-up Calls (30)</span>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Add-ons -->
                            <button class="w-full flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Add-ons (12)</span>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section: 3 Cards -->
            <div class="grid gap-4" style="grid-template-columns: 30% 40% 30%;">
                <!-- Projected Card -->
                <div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 h-full">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Projected</h3>
                            <button class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="text-center py-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Occupancy</div>
                                <div class="text-2xl font-bold text-gray-800 dark:text-gray-200">89%</div>
                            </div>
                            <div class="text-center py-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Room Rev</div>
                                <div class="text-lg font-bold text-gray-800 dark:text-gray-200">USD 27,140.00</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="text-center py-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">ADR</div>
                                <div class="text-lg font-bold text-gray-800 dark:text-gray-200">USD 73.45</div>
                            </div>
                            <div class="text-center py-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Group Rev</div>
                                <div class="text-lg font-bold text-gray-800 dark:text-gray-200">USD 46,700.00</div>
                            </div>
                        </div>

                        <button class="w-full py-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium text-sm flex items-center justify-center space-x-2">
                            <span>Go To Reports</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Room Status Card -->
                <div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 h-full">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Room Status</h3>
                            <button class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-6">
                            <!-- Vacant/Clean -->
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2 w-40">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">50</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Vacant/Clean</span>
                                </div>
                                <div class="flex-1">
                                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500" style="width: 83%"></div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 w-40">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">0</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Occupied/Clean</span>
                                </div>
                            </div>

                            <!-- Vacant/Dirty -->
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2 w-40">
                                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">10</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Vacant/Dirty</span>
                                </div>
                                <div class="flex-1">
                                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500" style="width: 17%"></div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 w-40">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">25</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Occupied/Dirty</span>
                                </div>
                            </div>

                            <!-- Out of Order -->
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2 w-40">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">3</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Out of Order</span>
                                </div>
                                <div class="flex-1">
                                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500" style="width: 5%"></div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 w-40">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">1</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Out of Inventory</span>
                                </div>
                            </div>
                        </div>

                        <button class="mt-6 w-full py-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium text-sm flex items-center justify-center space-x-2">
                            <span>Go To Housekeeping</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Function Rooms Card -->
                <div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 h-full">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Function Rooms</h3>

                        <div class="space-y-3 overflow-y-auto" style="max-height: 400px;">
                            <!-- Dallas Board Room -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-medium text-gray-700 dark:text-gray-300">Dallas Board Room</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                                        </svg>
                                        Dirty
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">1 event start from 1:15 PM</p>
                            </div>

                            <!-- Paris Board Room -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-medium text-gray-700 dark:text-gray-300">Paris Board Room</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Clean
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">1 event start from 9:00 AM</p>
                            </div>

                            <!-- Eiffel Tower -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-medium text-gray-700 dark:text-gray-300">Eiffel Tower</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Clean
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">No events</p>
                            </div>

                            <!-- Redwood Forest -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-medium text-gray-700 dark:text-gray-300">Redwood Forest</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Clean
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">No events</p>
                            </div>
                        </div>

                        <button class="mt-4 w-full py-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium text-sm flex items-center justify-center space-x-2">
                            <span>Go To Function Rooms</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
