<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Menu;
use App\Models\ItemCategory;
use App\Models\MenuItem;
use App\Models\User;
use App\Models\Branch;
use App\Models\RestaurantCharge;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Area;
use App\Models\TableSession;
use App\Models\Reservation;
use App\Models\OrderType;
use App\Models\DeliveryPlatform;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\OrderTax;
use App\Models\OrderCharge;
use App\Models\Kot;
use App\Models\KotItem;
use App\Models\MenuItemVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Tax;

use App\ApiResource\OrderResource;
use App\Enums\OrderStatus;

class PosApiController extends Controller
{

    private function branch()
    {
        return branch();
    }

    private function restaurant()
    {
        return restaurant();
    }

    public function getMenus()
    {
        $branch = $this->branch();
        $menus = cache()->remember('menus_' . $branch->id, 60, function () use ($branch) {
            return Menu::where('branch_id', $branch->id)->get()->map(function ($menu) {
                return [
                    'id' => $menu->id,
                    'menu_name' => $menu->getTranslation('menu_name', session('locale', app()->getLocale())),
                    'sort_order' => $menu->sort_order,
                ];
            });
        });


        return response()->json($menus);
    }

    public function getCategories()
    {
        $branch = $this->branch();
        $categories = cache()->remember('categories_' . $branch->id, 60, function () use ($branch) {
            return ItemCategory::where('branch_id', $branch->id)->get()->map(function ($category) {
                return [
                    'id' => $category->id,
                    'count' => $category->items()->count(),
                    'category_name' => $category->getTranslation('category_name', session('locale', app()->getLocale())),
                    'sort_order' => $category->sort_order,
                ];
            });
        });
        return response()->json($categories);
    }

    public function getMenuItems()
    {
        $branch = $this->branch();
        $menuItems = cache()->remember('menu_items_pos_' . $branch->id, 120, function () use ($branch) {
            // Note: MenuItem model has AvailableMenuItemScope which auto-filters by is_available = true
            return MenuItem::where('branch_id', $branch->id)
                ->with('prices:id,menu_item_id,order_type_id,final_price', 'prices.orderType:id,order_type_name')
                ->withCount('variations', 'modifierGroups')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_name' => $item->item_name, // Uses accessor which handles translations
                        'menu_id' => $item->menu_id,
                        'item_category_id' => $item->item_category_id,
                        'price' => $item->price,
                        'image' => $item->item_photo_url,
                        'type' => $item->type,
                        'in_stock' => (bool) $item->in_stock,
                        'variations_count' => $item->variations_count,
                        'modifier_groups_count' => $item->modifier_groups_count,
                        'sort_order' => $item->sort_order ?? 0,
                    ];
                });
        });
        return response()->json($menuItems);
    }

    /**
     * Get menu items with variations and modifiers included for offline use
     */
    public function getMenuItemsWithVariations(Request $request)
    {
        $branch = $this->branch();
        $orderTypeId = $request->input('order_type_id');
        $deliveryAppId = $request->input('delivery_app_id');

        $cacheKey = 'menu_items_full_' . $branch->id . '_' . ($orderTypeId ?? 'all') . '_' . ($deliveryAppId ?? 'none');

        $menuItems = cache()->remember($cacheKey, 300, function () use ($branch, $orderTypeId, $deliveryAppId) {
            return MenuItem::where('branch_id', $branch->id)
                ->with([
                    'variations' => function ($q) use ($orderTypeId, $deliveryAppId) {
                        $q->with(['prices' => function ($pq) use ($orderTypeId, $deliveryAppId) {
                            $pq->where('status', true);
                            if ($orderTypeId) {
                                $pq->where('order_type_id', $orderTypeId);
                            }
                            if ($deliveryAppId && $deliveryAppId !== 'default') {
                                $pq->where(function ($q) use ($deliveryAppId) {
                                    $q->where('delivery_app_id', $deliveryAppId)
                                      ->orWhereNull('delivery_app_id');
                                });
                            } else {
                                $pq->whereNull('delivery_app_id');
                            }
                        }]);
                    },
                    'modifierGroups.options' => function ($q) use ($orderTypeId, $deliveryAppId) {
                        $q->with(['prices' => function ($pq) use ($orderTypeId, $deliveryAppId) {
                            $pq->where('status', true);
                            if ($orderTypeId) {
                                $pq->where('order_type_id', $orderTypeId);
                            }
                        }]);
                    }
                ])
                ->get()
                ->map(function ($item) use ($orderTypeId, $deliveryAppId) {
                    // Set price context
                    if ($orderTypeId) {
                        $item->setPriceContext($orderTypeId, $deliveryAppId);
                    }

                    return [
                        'id' => $item->id,
                        'item_name' => $item->item_name,
                        'menu_id' => $item->menu_id,
                        'item_category_id' => $item->item_category_id,
                        'price' => $item->price,
                        'image' => $item->item_photo_url,
                        'type' => $item->type,
                        'in_stock' => (bool) $item->in_stock,
                        'sort_order' => $item->sort_order ?? 0,
                        'variations' => $item->variations->map(function ($v) use ($orderTypeId, $deliveryAppId) {
                            if ($orderTypeId) {
                                $v->setPriceContext($orderTypeId, $deliveryAppId);
                            }
                            return [
                                'id' => $v->id,
                                'variation_name' => $v->variation_name,
                                'price' => $v->price,
                            ];
                        })->values(),
                        'modifier_groups' => $item->modifierGroups->map(function ($group) use ($orderTypeId) {
                            return [
                                'id' => $group->id,
                                'name' => $group->name,
                                'required' => (bool) $group->required,
                                'min_selection' => $group->min_selection,
                                'max_selection' => $group->max_selection,
                                'options' => $group->options->map(function ($opt) use ($orderTypeId) {
                                    if ($orderTypeId) {
                                        $opt->setPriceContext($orderTypeId, null);
                                    }
                                    return [
                                        'id' => $opt->id,
                                        'name' => $opt->name,
                                        'price' => $opt->price ?? 0,
                                    ];
                                })->values(),
                            ];
                        })->values(),
                    ];
                });
        });

        return response()->json($menuItems);
    }

    public function getWaiters()
    {
        $branch = $this->branch();
        $restaurant = $this->restaurant();
        $waiters = cache()->remember('waiters_' . $branch->id, 60, function () use ($restaurant) {
            return User::where('restaurant_id', $restaurant->id)->get();
        });
        return response()->json($waiters);
    }

    public function getCustomers(Request $request)
    {
        $restaurant = $this->restaurant();
        $searchQuery = $request->query('search', '');

        $query = Customer::where('restaurant_id', $restaurant->id);

        if (!empty($searchQuery) && strlen($searchQuery) >= 2) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('phone', 'like', '%' . $searchQuery . '%')
                    ->orWhere('email', 'like', '%' . $searchQuery . '%');
            });
        }

        $customers = $query->orderBy('name')->limit(10)->get();

        return response()->json($customers);
    }

    public function getPhoneCodes(Request $request)
    {
        $search = $request->query('search', '');

        $phoneCodes = \App\Models\Country::pluck('phonecode')
            ->unique()
            ->filter()
            ->values();

        if (!empty($search)) {
            $phoneCodes = $phoneCodes->filter(function ($code) use ($search) {
                return str_contains($code, $search);
            })->values();
        }

        return response()->json($phoneCodes);
    }

    public function saveCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_code' => 'required',
            'phone' => 'required',
            'email' => 'nullable|email',
            'address' => 'nullable|string|max:500',
        ]);

        // Check for existing customer by email or phone
        $existingCustomer = null;

        if (!empty($validated['email'])) {
            $existingCustomer = Customer::where('restaurant_id', $this->restaurant()->id)
                ->where('email', $validated['email'])
                ->first();
        }

        if (!$existingCustomer && !empty($validated['phone'])) {
            $existingCustomer = Customer::where('restaurant_id', $this->restaurant()->id)
                ->where('phone', $validated['phone'])
                ->first();
        }

        $customerData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'phone_code' => $validated['phone_code'],
            'email' => $validated['email'] ?? null,
            'delivery_address' => $validated['address'] ?? null,
        ];

        // Update existing customer or create new one
        if ($existingCustomer) {
            $customer = tap($existingCustomer)->update($customerData);
        } else {
            $customerData['restaurant_id'] = $this->restaurant()->id;
            $customer = Customer::create($customerData);
        }

        // Clear cache
        cache()->forget('customers_' . $this->branch()->id);

        return response()->json([
            'success' => true,
            'message' => $existingCustomer ? __('messages.customerUpdated') : __('messages.customerAdded'),
            'customer' => $customer,
        ]);
    }

    public function getExtraCharges($orderType)
    {
        $extraCharges = RestaurantCharge::whereJsonContains('order_types', $orderType)
            ->where('is_enabled', true)
            ->where('restaurant_id', $this->restaurant()->id)
            ->get();

        return response()->json($extraCharges);
    }

    public function getTables()
    {
        // First cleanup expired locks
        Table::cleanupExpiredLocks();

        $user = auth()->user();
        $userId = $user ? $user->id : null;
        $isAdmin = $user ? $user->hasRole('Admin_' . $user->restaurant_id) : false;

        $tables = Table::where('branch_id', $this->branch()->id)
            ->where('available_status', '<>', 'running')
            ->where('status', 'active')
            ->with(['area', 'tableSession.lockedByUser'])
            ->get()
            ->map(function ($table) use ($userId) {
                $session = $table->tableSession;
                $isLocked = $session ? $session->isLocked() : false;
                $isLockedByCurrentUser = $isLocked && $session && $session->locked_by_user_id === $userId;
                $isLockedByOtherUser = $isLocked && $session && $session->locked_by_user_id !== $userId;

                return [
                    'id' => $table->id,
                    'branch_id' => $table->branch_id,
                    'table_code' => $table->table_code,
                    'hash' => $table->hash,
                    'status' => $table->status,
                    'available_status' => $table->available_status,
                    'area_id' => $table->area_id,
                    'area_name' => $table->area ? $table->area->area_name : 'Unknown Area',
                    'seating_capacity' => $table->seating_capacity,
                    'is_locked' => $isLocked,
                    'is_locked_by_current_user' => $isLockedByCurrentUser,
                    'is_locked_by_other_user' => $isLockedByOtherUser,
                    'locked_by_user_id' => $session ? $session->locked_by_user_id : null,
                    'locked_by_user_name' => $session && $session->lockedByUser ? $session->lockedByUser->name : null,
                    'locked_at' => $session && $session->locked_at ? $session->locked_at->format('H:i') : null,
                    'created_at' => $table->created_at,
                    'updated_at' => $table->updated_at,
                ];
            });

        return response()->json([
            'tables' => $tables,
            'is_admin' => $isAdmin,
        ]);
    }

    public function getTodayReservations()
    {
        $reservations = Reservation::where('branch_id', $this->branch()->id)
            ->whereDate('reservation_date_time', today())
            ->whereNotNull('table_id')
            ->with('table')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'table_code' => $reservation->table ? $reservation->table->table_code : 'N/A',
                    'time' => $reservation->reservation_date_time->translatedFormat('h:i A'),
                    'datetime' => $reservation->reservation_date_time->format('M d, Y h:i A'),
                    'date' => $reservation->reservation_date_time->format('M d, Y'),
                    'party_size' => $reservation->party_size,
                    'status' => $reservation->reservation_status,
                ];
            });
        return response()->json($reservations);
    }

    public function forceUnlockTable($tableId)
    {
        $table = Table::find($tableId);

        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => __('messages.tableNotFound'),
            ], 404);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('messages.unauthorized'),
            ], 401);
        }

        $isAdmin = $user->hasRole('Admin_' . $user->restaurant_id);
        $isLockedByCurrentUser = $table->tableSession && $table->tableSession->locked_by_user_id === $user->id;

        if (!($isAdmin || $isLockedByCurrentUser)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.tableUnlockFailed'),
            ], 403);
        }

        $result = $table->unlock(null, true);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => __('messages.tableUnlockedSuccess', ['table' => $table->table_code]),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => __('messages.tableUnlockFailed'),
            ], 500);
        }
    }

    public function getOrderTypes()
    {
        $orderTypes = OrderType::where('branch_id', $this->branch()->id)
            ->where('is_active', true)
            ->orderBy('order_type_name')
            ->get()
            ->map(function ($orderType) {
                return [
                    'id' => $orderType->id,
                    'slug' => $orderType->slug,
                    'order_type_name' => $orderType->translated_name,
                    'type' => $orderType->type,
                ];
            });

        return response()->json($orderTypes);
    }

    public function getDeliveryPlatforms()
    {
        $deliveryPlatforms = DeliveryPlatform::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($platform) {
                return [
                    'id' => $platform->id,
                    'name' => $platform->name,
                    'logo' => $platform->logo,
                    'logo_url' => $platform->logo_url ?? null,
                ];
            });

        return response()->json($deliveryPlatforms);
    }

    public function getOrderNumber()
    {
        $orderNumberData = Order::generateOrderNumber($this->branch());

        $formattedOrderNumber = isOrderPrefixEnabled($this->branch())
            ? $orderNumberData['formatted_order_number']
            : __('modules.order.orderNumber') . ' #' . $orderNumberData['order_number'];

        // Return as array format: [order_number, formatted_order_number]
        return response()->json([
            $orderNumberData['order_number'],
            $formattedOrderNumber,
        ]);
    }

    public function submitOrder(Request $request)
    {
        try {
            DB::beginTransaction();

            // Get request data
            $data = $request->all();
            $customerData = $data['customer'] ?? [];
            $items = $data['items'] ?? [];
            $taxes = $data['taxes'] ?? [];
            $actions = $data['actions'] ?? [];
            $note = $data['note'] ?? '';
            $orderTypeDisplay = $data['order_type'] ?? 'Dine In';
            $orderNumber = $data['order_number'] ?? '';
            $pax = $data['pax'] ?? 1;
            $waiterId = $data['waiter_id'] ?? null;
            $tableId = $data['table_id'] ?? null;
            $discountType = $data['discount_type'] ?? null;
            $discountValue = $data['discount_value'] ?? 0;
            $discountAmount = $data['discount_amount'] ?? 0;
            $extraChargesData = $data['extra_charges'] ?? [];

            // Validate required fields (similar to Pos.php)
            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.orderItemRequired'),
                ], 422);
            }

            // Normalize order type for validation
            $normalizedOrderType = strtolower(str_replace(' ', '_', $orderTypeDisplay));
            if ($normalizedOrderType === 'dine in') {
                $normalizedOrderType = 'dine_in';
            }




            // Check if table is locked by another user (similar to Pos.php)
            $table = null;
            if ($tableId && $normalizedOrderType === 'dine_in') {
                $table = Table::find($tableId);
                if ($table && $table->tableSession && $table->tableSession->isLocked()) {
                    $lockedByUser = $table->tableSession->lockedByUser;
                    $lockedUserName = $lockedByUser ? $lockedByUser->name : 'Another user';

                    // Check if current user can access the table
                    $user = auth()->user();
                    if ($user && method_exists($table, 'canBeAccessedByUser') && !$table->canBeAccessedByUser($user->id)) {
                        return response()->json([
                            'success' => false,
                            'message' => __('messages.tableHandledByUser', [
                                'user' => $lockedUserName,
                                'table' => $table->table_code
                            ]),
                        ], 403);
                    }
                }
            }

            // Find or create customer (similar to Pos.php)
            $customerId = null;
            if (!empty($customerData['name']) || !empty($customerData['phone']) || !empty($customerData['email'])) {
                $customer = Customer::firstOrCreate(
                    [
                        'restaurant_id' => $this->restaurant()->id,
                        'phone' => $customerData['phone'] ?? null,
                    ],
                    [
                        'name' => $customerData['name'] ?? '',
                        'email' => $customerData['email'] ?? null,
                    ]
                );

                // Update customer data if provided
                if (!empty($customerData['name'])) {
                    $customer->name = $customerData['name'];
                }
                if (!empty($customerData['email'])) {
                    $customer->email = $customerData['email'];
                }
                if (!empty($customerData['phone'])) {
                    $customer->phone = $customerData['phone'];
                }
                $customer->save();
                $customerId = $customer->id;
            }

            // Find order type (similar to Pos.php)
            $orderTypeModel = null;
            $orderTypeId = null;
            $orderTypeSlug = null;
            $orderTypeName = null;

            $orderTypeModel = OrderType::where('branch_id', $this->branch()->id)
                ->where('is_active', true)
                ->where(function ($q) use ($normalizedOrderType, $orderTypeDisplay) {
                    $q->where('slug', $normalizedOrderType)
                        ->orWhere('type', $normalizedOrderType)
                        ->orWhere('order_type_name', $orderTypeDisplay);
                })
                ->first();

            if ($orderTypeModel) {
                $orderTypeId = $orderTypeModel->id;
                $orderTypeSlug = $orderTypeModel->slug;
                $orderTypeName = $orderTypeModel->order_type_name;
            } else {
                // Fallback to default order type
                $orderTypeModel = OrderType::where('branch_id', $this->branch()->id)
                    ->where('is_default', true)
                    ->where('is_active', true)
                    ->first();

                if ($orderTypeModel) {
                    $orderTypeId = $orderTypeModel->id;
                    $orderTypeSlug = $orderTypeModel->slug;
                    $orderTypeName = $orderTypeModel->order_type_name;
                } else {
                    $orderTypeSlug = $normalizedOrderType;
                    $orderTypeName = $orderTypeDisplay;
                }
            }

            // Calculate subtotal from items (similar to Pos.php calculateTotal)
            $subTotal = 0;
            foreach ($items as $item) {
                $itemPrice = $item['price'] ?? 0;
                $itemQuantity = $item['quantity'] ?? 1;
                $subTotal += $itemPrice * $itemQuantity;
            }

            // Apply discount (similar to Pos.php)
            $discountedTotal = $subTotal;
            if ($discountAmount > 0) {
                $discountedTotal -= $discountAmount;
            }

            // Calculate total starting from discounted total
            $total = $discountedTotal;

            // Add taxes (order level - similar to Pos.php)
            $totalTaxAmount = 0;
            if (!empty($taxes) && is_array($taxes)) {
                foreach ($taxes as $tax) {
                    // If tax has amount, use it directly
                    if (isset($tax['amount'])) {
                        $taxAmount = $tax['amount'];
                        $total += $taxAmount;
                        $totalTaxAmount += $taxAmount;
                    }
                }
            }

            // Add extra charges (similar to Pos.php)
            $extraCharges = [];
            if (!empty($extraChargesData) && is_array($extraChargesData)) {
                foreach ($extraChargesData as $charge) {
                    $chargeId = is_array($charge) ? ($charge['id'] ?? null) : $charge;
                    if ($chargeId) {
                        $chargeModel = RestaurantCharge::find($chargeId);
                        if ($chargeModel) {
                            $extraCharges[] = $chargeModel;
                            // Get charge amount using getAmount method
                            $chargeAmount = $chargeModel->getAmount($discountedTotal);
                            $total += $chargeAmount;
                        }
                    }
                }
            }

            // Ensure total is not negative
            $total = max(0, $total);

            // Generate order number (similar to Pos.php)
            $orderNumberData = Order::generateOrderNumber($this->branch());

            // Determine status based on actions (similar to Pos.php saveOrder)
            $status = 'draft';
            $orderStatus = 'placed';
            $tableStatus = 'available';

            $action = !empty($actions) ? $actions[0] : null;

            switch ($action) {
                case 'bill':
                case 'billed':
                    $status = 'billed';
                    $orderStatus = 'confirmed';
                    $tableStatus = 'running';
                    break;
                case 'kot':
                    $status = 'kot';
                    $orderStatus = 'confirmed';
                    $tableStatus = 'running';
                    break;
                case 'cancel':
                    $status = 'canceled';
                    $orderStatus = 'canceled';
                    $tableStatus = 'available';
                    break;
                default:
                    $status = 'draft';
                    $orderStatus = 'placed';
                    $tableStatus = 'available';
            }

            // Get order type name (similar to Pos.php)
            $orderTypeNameFinal = $orderTypeName ?? $orderTypeDisplay;

            // Create order (similar to Pos.php orderData structure)
            $order = Order::create([
                'order_number' => $orderNumberData['order_number'],
                'formatted_order_number' => $orderNumberData['formatted_order_number'],
                'branch_id' => $this->branch()->id,
                'table_id' => $tableId,
                'date_time' => now(),
                'number_of_pax' => $pax,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'waiter_id' => $waiterId,
                'sub_total' => $subTotal,
                'total' => $total,
                'order_type' => $orderTypeSlug ?? $normalizedOrderType,
                'order_type_id' => $orderTypeId,
                'custom_order_type_name' => $orderTypeNameFinal,
                'delivery_fee' => 0, // TODO: Add delivery fee if needed from request
                'delivery_executive_id' => null, // TODO: Add if needed
                'delivery_app_id' => null, // TODO: Add if needed
                'status' => $status,
                'order_status' => $orderStatus,
                'placed_via' => 'pos',
                'tax_mode' => 'order', // Default to order-level tax
                'customer_id' => $customerId,
            ]);

            // Save user ID when bill action is triggered (similar to Pos.php)
            $user = auth()->user();
            if ($status == 'billed' && $user) {
                $order->added_by = $user->id;
                $order->save();
            }

            // Create extra charges (similar to Pos.php)
            if (!empty($extraCharges)) {
                $chargesData = collect($extraCharges)
                    ->map(fn($charge) => [
                        'charge_id' => $charge->id,
                    ])->toArray();

                $order->charges()->createMany($chargesData);
            }

            // Handle canceled status (similar to Pos.php)
            if ($status == 'canceled') {
                if ($table) {
                    $table->available_status = $tableStatus;
                    $table->saveQuietly();
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => __('messages.orderCanceled'),
                    'order' => $order,
                ], 200);
            }

            // Handle KOT creation (similar to Pos.php)
            $kot = null;
            $kotIds = [];

            if ($status == 'kot') {
                // For now, create single KOT (can be extended for kitchen places later)
                $kot = Kot::create([
                    'branch_id' => $this->branch()->id,
                    'kot_number' => Kot::generateKotNumber($this->branch()),
                    'order_id' => $order->id,
                    'order_type_id' => $orderTypeId,
                    'token_number' => Kot::generateTokenNumber($this->branch()->id, $orderTypeId),
                    'note' => $note,
                ]);

                $kotIds[] = $kot->id;

                // Create KOT items (similar to Pos.php)
                foreach ($items as $item) {
                    $menuItemId = $item['id'] ?? null;
                    $variantId = $item['variant_id'] ?? 0;
                    $quantity = $item['quantity'] ?? 1;
                    $itemNote = $item['note'] ?? null;
                    $modifierIds = $item['modifier_ids'] ?? [];

                    $kotItem = KotItem::create([
                        'kot_id' => $kot->id,
                        'menu_item_id' => $menuItemId,
                        'menu_item_variation_id' => $variantId > 0 ? $variantId : null,
                        'quantity' => $quantity,
                        'note' => $itemNote,
                        'order_type_id' => $orderTypeId ?? null,
                        'order_type' => $orderTypeSlug ?? null,
                    ]);

                    // Sync modifiers if provided (similar to Pos.php)
                    if (!empty($modifierIds) && is_array($modifierIds)) {
                        $kotItem->modifierOptions()->sync($modifierIds);
                    }
                }
            }

            // Create order items (for 'billed' status only, similar to Pos.php)
            if ($status == 'billed') {
                foreach ($items as $item) {
                    $menuItemId = $item['id'] ?? null;
                    $variantId = $item['variant_id'] ?? 0;
                    $quantity = $item['quantity'] ?? 1;
                    $price = $item['price'] ?? 0;
                    $itemNote = $item['note'] ?? null;
                    $amount = $price * $quantity;
                    $modifierIds = $item['modifier_ids'] ?? [];

                    // Get menu item to set price context if needed (similar to Pos.php)
                    $menuItem = MenuItem::find($menuItemId);
                    if ($menuItem && $orderTypeId) {
                        // Set price context if orderTypeId is available
                        if (method_exists($menuItem, 'setPriceContext')) {
                            $menuItem->setPriceContext($orderTypeId, null);
                            $price = $menuItem->price ?? $price;
                        }
                    }

                    $orderItem = OrderItem::create([
                        'branch_id' => $this->branch()->id,
                        'order_id' => $order->id,
                        'menu_item_id' => $menuItemId,
                        'menu_item_variation_id' => $variantId > 0 ? $variantId : null,
                        'quantity' => $quantity,
                        'price' => $price,
                        'amount' => $amount,
                        'note' => $itemNote,
                        'order_type' => $orderTypeSlug ?? null,
                        'order_type_id' => $orderTypeId ?? null,
                    ]);

                    // Sync modifiers if provided (similar to Pos.php)
                    if (!empty($modifierIds) && is_array($modifierIds)) {
                        $orderItem->modifierOptions()->sync($modifierIds);
                    }
                }

                // Create order taxes (order level, similar to Pos.php)
                if (!empty($taxes) && is_array($taxes)) {
                    foreach ($taxes as $tax) {
                        if (isset($tax['id'])) {
                            OrderTax::create([
                                'order_id' => $order->id,
                                'tax_id' => $tax['id'],
                            ]);
                        }
                    }
                }

                // Recalculate totals based on actual items (similar to Pos.php)
                $recalculatedSubTotal = $order->items()->sum('amount');
                $recalculatedTotal = $recalculatedSubTotal;
                $recalculatedDiscountedTotal = $recalculatedSubTotal;

                // Apply discount
                if ($discountAmount > 0) {
                    $recalculatedTotal -= $discountAmount;
                    $recalculatedDiscountedTotal = $recalculatedTotal;
                }

                // Recalculate taxes using centralized method (similar to Pos.php)
                $orderTaxes = OrderTax::where('order_id', $order->id)->with('tax')->get();
                $recalculatedTaxAmount = 0;

                foreach ($orderTaxes as $orderTax) {
                    if ($orderTax->tax) {
                        $taxPercent = $orderTax->tax->tax_percent ?? 0;
                        $taxAmount = ($recalculatedSubTotal * $taxPercent) / 100;
                        $recalculatedTotal += $taxAmount;
                        $recalculatedTaxAmount += $taxAmount;
                    }
                }

                // Add extra charges (similar to Pos.php)
                $orderCharges = OrderCharge::where('order_id', $order->id)->with('charge')->get();
                foreach ($orderCharges as $orderCharge) {
                    if ($orderCharge->charge) {
                        $chargeAmount = $orderCharge->charge->getAmount($recalculatedDiscountedTotal);
                        $recalculatedTotal += $chargeAmount;
                    }
                }

                // Update order with recalculated totals (similar to Pos.php)
                $order->update([
                    'sub_total' => $recalculatedSubTotal,
                    'total' => max(0, $recalculatedTotal),
                    'discount_amount' => $discountAmount,
                    'total_tax_amount' => $recalculatedTaxAmount,
                    'tax_mode' => 'order',
                ]);
            }

            // Update table status (similar to Pos.php)
            if ($table) {
                $table->available_status = $tableStatus;
                $table->saveQuietly();
            }

            DB::commit();

            // Load relationships for response
            $order->load(['items', 'customer', 'table', 'waiter', 'kot']);

            // Return success message based on status (similar to Pos.php)
            $successMessage = 'Order created successfully';
            if ($status == 'kot') {
                $successMessage = __('messages.kotGenerated');
            } elseif ($status == 'billed') {
                $successMessage = __('messages.billedSuccess');
            }

            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'order' => $order,
                'kot' => $kot,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('POS Order Creation Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getOrder($id)
    {
        $order = Order::with('items', 'customer', 'table', 'waiter', 'kot', 'kot.items', 'kot.items.menuItem')->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order fetched successfully',
            'order' => $order,
        ], 200);
    }

    public function getOrders($status = null)
    {
        $orders = Order::where('branch_id', $this->branch()->id)
            ->with('items', 'customer', 'table', 'waiter', 'kot', 'kot.items', 'kot.items.menuItem');

        if ($status) {
            $orders->where('order_status', OrderStatus::from($status));
        }

        $orders = OrderResource::collection($orders->get());
        return response()->json($orders);
    }

    public function getTaxes()
    {
        $taxes = Tax::get();
        return response()->json($taxes);
    }

    public function getItemVariations($itemId)
    {
        $item = MenuItem::with(['variations.prices' => function($q) {
            $q->where('status', true);
        }])->find($itemId);

        if (!$item) {
            return response()->json([], 404);
        }

        $variations = $item->variations->map(function($variation) use ($item) {
            return [
                'id' => $variation->id,
                'variation_name' => $variation->variation_name,
                'price' => $variation->price ?? $item->price,
            ];
        });

        return response()->json($variations);
    }

    public function getRestaurants()
    {
        $restaurant = Restaurant::with('currency')->where('id', $this->restaurant()->id)->first();
        return response()->json($restaurant);
    }

    /**
     * Get delivery executives
     */
    public function getDeliveryExecutives()
    {
        $branch = $this->branch();
        $restaurant = $this->restaurant();

        $deliveryExecutives = \App\Models\DeliveryExecutive::where('restaurant_id', $restaurant->id)
            ->where('status', 'active')
            ->get()
            ->map(function ($exec) {
                return [
                    'id' => $exec->id,
                    'name' => $exec->name,
                    'phone' => $exec->phone,
                    'email' => $exec->email,
                ];
            });

        return response()->json($deliveryExecutives);
    }

    /**
     * Get modifiers for a menu item
     */
    public function getItemModifiers($itemId)
    {
        $item = MenuItem::with(['modifierGroups.modifierOptions'])->find($itemId);

        if (!$item) {
            return response()->json([], 404);
        }

        $modifiers = $item->modifierGroups->map(function ($group) {
            return [
                'id' => $group->id,
                'group_name' => $group->group_name,
                'min_selection' => $group->min_selection ?? 0,
                'max_selection' => $group->max_selection ?? 1,
                'required' => $group->required ?? false,
                'options' => $group->modifierOptions->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'option_name' => $option->option_name,
                        'price' => $option->price ?? 0,
                    ];
                }),
            ];
        });

        return response()->json($modifiers);
    }

    /**
     * Get item prices for order type
     */
    public function getItemPrices(Request $request, $itemId)
    {
        $orderTypeId = $request->query('order_type_id');
        $deliveryAppId = $request->query('delivery_app_id');

        $item = MenuItem::with(['prices' => function ($q) use ($orderTypeId) {
            if ($orderTypeId) {
                $q->where('order_type_id', $orderTypeId);
            }
        }])->find($itemId);

        if (!$item) {
            return response()->json(['price' => 0], 404);
        }

        // Set price context if available
        if (method_exists($item, 'setPriceContext') && $orderTypeId) {
            $item->setPriceContext($orderTypeId, $deliveryAppId);
        }

        return response()->json([
            'price' => $item->price,
            'original_price' => $item->original_price ?? $item->price,
        ]);
    }

    /**
     * Process payment for an order
     */
    public function processPayment(Request $request, $orderId)
    {
        try {
            DB::beginTransaction();

            $order = Order::find($orderId);
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $paymentMethod = $request->input('payment_method', 'cash');
            $amountPaid = $request->input('amount_paid', $order->total);
            $tipAmount = $request->input('tip_amount', 0);
            $change = $request->input('change', 0);

            // Create payment record
            $payment = \App\Models\Payment::create([
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'payment_method' => $paymentMethod,
                'amount' => $amountPaid,
                'tip_amount' => $tipAmount,
                'change_amount' => $change,
                'status' => 'completed',
                'payment_date' => now(),
            ]);

            // Update order status
            $order->update([
                'status' => 'paid',
                'order_status' => 'delivered',
                'tip_amount' => $tipAmount,
            ]);

            // Update table status if dine-in
            if ($order->table_id) {
                Table::where('id', $order->table_id)->update([
                    'available_status' => 'available'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('messages.paymentSuccess'),
                'payment' => $payment,
                'order' => $order->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payment processing error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing order
     */
    public function updateOrder(Request $request, $orderId)
    {
        try {
            DB::beginTransaction();

            $order = Order::find($orderId);
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $data = $request->all();

            // Update order fields
            $updateData = [];

            if (isset($data['discount_type'])) {
                $updateData['discount_type'] = $data['discount_type'];
            }
            if (isset($data['discount_value'])) {
                $updateData['discount_value'] = $data['discount_value'];
            }
            if (isset($data['discount_amount'])) {
                $updateData['discount_amount'] = $data['discount_amount'];
            }
            if (isset($data['tip_amount'])) {
                $updateData['tip_amount'] = $data['tip_amount'];
            }
            if (isset($data['delivery_fee'])) {
                $updateData['delivery_fee'] = $data['delivery_fee'];
            }
            if (isset($data['delivery_executive_id'])) {
                $updateData['delivery_executive_id'] = $data['delivery_executive_id'];
            }
            if (isset($data['customer_id'])) {
                $updateData['customer_id'] = $data['customer_id'];
            }
            if (isset($data['waiter_id'])) {
                $updateData['waiter_id'] = $data['waiter_id'];
            }
            if (isset($data['number_of_pax'])) {
                $updateData['number_of_pax'] = $data['number_of_pax'];
            }
            if (isset($data['table_id'])) {
                $updateData['table_id'] = $data['table_id'];
            }
            if (isset($data['note'])) {
                $updateData['note'] = $data['note'];
            }

            // Recalculate totals if items changed
            if (isset($data['items'])) {
                // Delete existing items and recreate
                $order->items()->delete();

                $subTotal = 0;
                foreach ($data['items'] as $item) {
                    $menuItemId = $item['menu_item_id'] ?? $item['id'] ?? null;
                    $variantId = $item['variation_id'] ?? $item['variant_id'] ?? null;
                    $quantity = $item['qty'] ?? $item['quantity'] ?? 1;
                    $price = $item['price'] ?? 0;
                    $itemNote = $item['note'] ?? null;
                    $modifierIds = $item['modifier_ids'] ?? [];
                    $amount = $price * $quantity;

                    $orderItem = OrderItem::create([
                        'branch_id' => $order->branch_id,
                        'order_id' => $order->id,
                        'menu_item_id' => $menuItemId,
                        'menu_item_variation_id' => $variantId > 0 ? $variantId : null,
                        'quantity' => $quantity,
                        'price' => $price,
                        'amount' => $amount,
                        'note' => $itemNote,
                        'order_type' => $order->order_type,
                        'order_type_id' => $order->order_type_id,
                    ]);

                    if (!empty($modifierIds) && is_array($modifierIds)) {
                        $orderItem->modifierOptions()->sync($modifierIds);
                    }

                    $subTotal += $amount;
                }

                $updateData['sub_total'] = $subTotal;

                // Apply discount
                $discountAmount = 0;
                $discountType = $data['discount_type'] ?? $order->discount_type;
                $discountValue = $data['discount_value'] ?? $order->discount_value;

                if ($discountType === 'percent') {
                    $discountAmount = round(($subTotal * $discountValue) / 100, 2);
                } elseif ($discountType === 'fixed') {
                    $discountAmount = min($discountValue, $subTotal);
                }

                $updateData['discount_amount'] = $discountAmount;

                // Calculate total
                $total = $subTotal - $discountAmount;

                // Add taxes
                $orderTaxes = OrderTax::where('order_id', $order->id)->with('tax')->get();
                $totalTaxAmount = 0;
                foreach ($orderTaxes as $orderTax) {
                    if ($orderTax->tax) {
                        $taxAmount = ($subTotal * $orderTax->tax->tax_percent) / 100;
                        $total += $taxAmount;
                        $totalTaxAmount += $taxAmount;
                    }
                }

                // Add delivery fee
                $deliveryFee = $data['delivery_fee'] ?? $order->delivery_fee ?? 0;
                $total += $deliveryFee;

                // Add tip
                $tipAmount = $data['tip_amount'] ?? $order->tip_amount ?? 0;
                $total += $tipAmount;

                $updateData['total'] = max(0, $total);
                $updateData['total_tax_amount'] = $totalTaxAmount;
            }

            $order->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('messages.orderUpdated'),
                'order' => $order->fresh(['items', 'customer', 'table', 'waiter']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Request $request, $orderId)
    {
        try {
            DB::beginTransaction();

            $order = Order::find($orderId);
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $reason = $request->input('reason', '');

            // Update order status
            $order->update([
                'status' => 'cancelled',
                'order_status' => 'cancelled',
                'cancel_reason' => $reason,
            ]);

            // Update table status if dine-in
            if ($order->table_id) {
                Table::where('id', $order->table_id)->update([
                    'available_status' => 'available'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('messages.orderCanceled'),
                'order' => $order->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order cancellation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get areas for tables
     */
    public function getAreas()
    {
        $areas = Area::where('branch_id', $this->branch()->id)
            ->where('status', 'active')
            ->withCount('tables')
            ->get()
            ->map(function ($area) {
                return [
                    'id' => $area->id,
                    'area_name' => $area->area_name,
                    'tables_count' => $area->tables_count,
                ];
            });

        return response()->json($areas);
    }

    /**
     * Get running orders (for display/dashboard)
     */
    public function getRunningOrders()
    {
        $orders = Order::where('branch_id', $this->branch()->id)
            ->whereIn('status', ['kot', 'billed'])
            ->whereIn('order_status', ['placed', 'confirmed', 'preparing', 'food_ready'])
            ->with(['items.menuItem', 'customer', 'table', 'waiter'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json($orders);
    }

    /**
     * Get payment methods
     */
    public function getPaymentMethods()
    {
        $methods = [
            ['id' => 'cash', 'name' => __('modules.payment.cash')],
            ['id' => 'card', 'name' => __('modules.payment.card')],
            ['id' => 'upi', 'name' => __('modules.payment.upi')],
            ['id' => 'other', 'name' => __('modules.payment.other')],
        ];

        return response()->json($methods);
    }

    /**
     * Print order
     */
    public function printOrder($orderId)
    {
        $order = Order::with(['items.menuItem', 'items.menuItemVariation', 'customer', 'table', 'waiter', 'taxes.tax'])->find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        // Return print URL
        return response()->json([
            'success' => true,
            'print_url' => route('orders.print', $order->id),
            'order' => $order,
        ]);
    }

    /**
     * Print KOT
     */
    public function printKot($kotId)
    {
        $kot = Kot::with(['items.menuItem', 'items.menuItemVariation', 'order'])->find($kotId);

        if (!$kot) {
            return response()->json([
                'success' => false,
                'message' => 'KOT not found',
            ], 404);
        }

        // Return print URL
        return response()->json([
            'success' => true,
            'print_url' => route('kot.print', $kot->id),
            'kot' => $kot,
        ]);
    }
}
