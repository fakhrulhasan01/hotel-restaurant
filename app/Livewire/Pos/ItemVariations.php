<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use Livewire\Attributes\Computed;

class ItemVariations extends Component
{

    public $menuItem;
    public $menuItemId;
    public $itemVariation;
    public $variationName;
    public $variationPrice;
    public $showEditVariationsModal = false;
    public $showDeleteVariationsModal = false;
    public $orderTypeId;
    public $deliveryAppId;

    public function mount($menuItemId, $orderTypeId = null, $deliveryAppId = null)
    {
        $this->menuItemId = $menuItemId;
        $this->orderTypeId = $orderTypeId;
        $this->deliveryAppId = $deliveryAppId;

        // Load menuItem with eager loading only once
        $this->loadMenuItem();
    }

    protected function loadMenuItem()
    {
        // Build constraints for eager loading prices based on current context
        $orderTypeId = $this->orderTypeId;
        $deliveryAppId = $this->deliveryAppId;

        // Load menuItem with contextual eager loading to avoid N+1 queries
        $this->menuItem = \App\Models\MenuItem::with([
            'prices' => function ($q) use ($orderTypeId, $deliveryAppId) {
                $q->where('status', true)
                    ->whereNull('menu_item_variation_id'); // Only item-level prices, not variation prices
                
                // Filter by order type if provided
                if ($orderTypeId) {
                    $q->where(function($query) use ($orderTypeId) {
                        $query->where('order_type_id', $orderTypeId);
                    });
                }
                
                // Filter by delivery app if provided, or include null
                if ($deliveryAppId) {
                    $q->where(function($query) use ($deliveryAppId) {
                        $query->where('delivery_app_id', $deliveryAppId)
                              ->orWhereNull('delivery_app_id');
                    });
                } else {
                    $q->whereNull('delivery_app_id');
                }
            },
            'variations.prices' => function ($q) use ($orderTypeId, $deliveryAppId) {
                $q->where('status', true);
                
                // Filter by order type if provided
                if ($orderTypeId) {
                    $q->where(function($query) use ($orderTypeId) {
                        $query->where('order_type_id', $orderTypeId);
                    });
                }
                
                // Filter by delivery app if provided, or include null
                if ($deliveryAppId) {
                    $q->where(function($query) use ($deliveryAppId) {
                        $query->where('delivery_app_id', $deliveryAppId)
                              ->orWhereNull('delivery_app_id');
                    });
                } else {
                    $q->whereNull('delivery_app_id');
                }
            },
            'translations'
        ])->find($this->menuItemId);

        // Only apply price context if menuItem was found
        if ($this->menuItem) {
            $this->applyPriceContext();
        }
    }

    public function applyPriceContext()
    {
        if (!$this->orderTypeId || !$this->menuItem) {
            return;
        }

        $this->menuItem->setPriceContext($this->orderTypeId, $this->deliveryAppId);

        foreach ($this->menuItem->variations as $variation) {
            $variation->setPriceContext($this->orderTypeId, $this->deliveryAppId);
        }
    }

    public function hydrate()
    {
        $this->applyPriceContext();
    }

    public function setItemVariation($id)
    {
        $this->dispatch('setPosVariation', variationId: $id);
    }

    public function render()
    {
        return view('livewire.pos.item-variations');
    }

}
