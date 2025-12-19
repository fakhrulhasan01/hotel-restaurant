<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\OrderType;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class CustomOrderTypes extends Component
{
    use LivewireAlert;

    public $settings;
    public $allowCustomOrderTypeOptions = false;
    public $disableOrderTypePopup = false;
    public $defaultOrderTypeId = null;
    public $orderTypeFields = [];
    public $confirmDeleteOrderTypeModal = false;
    public $fieldId = null;
    public $fieldIndex = null;
    public $orderTypeOptions = [];

    protected function rules()
    {
        $rules = [
            'allowCustomOrderTypeOptions' => 'boolean',
            'disableOrderTypePopup' => 'boolean',
            'orderTypeFields.*.orderTypeName' => 'required',
            'orderTypeFields.*.type' => 'required|in:dine_in,delivery,pickup',
        ];

        if ($this->disableOrderTypePopup) {
            $branchId = branch()->id;
            $rules['defaultOrderTypeId'] = [
                'required',
                function ($attribute, $value, $fail) use ($branchId) {
                    $orderType = OrderType::where('id', $value)
                        ->where('branch_id', $branchId)
                        ->where('is_active', true)
                        ->first();

                    if (!$orderType) {
                        $fail(__('validation.invalidOrderType'));
                    }
                },
            ];
        }

        return $rules;
    }

    public function mount()
    {
        $this->allowCustomOrderTypeOptions = (bool)($this->settings->show_order_type_options ?? false);
        $this->disableOrderTypePopup = (bool)($this->settings->disable_order_type_popup ?? false);
        $this->defaultOrderTypeId = $this->settings->default_order_type_id ?? null;

        // Initialize order type options with proper translations
        $this->orderTypeOptions = [
            'dine_in' => __('modules.settings.dineIn'),
            'delivery' => __('modules.settings.delivery'),
            'pickup' => __('modules.settings.pickup')
        ];

        $this->fetchData();

        if (empty($this->orderTypeFields)) {
            $this->addMoreOrderTypeFields();
        }
    }

    public function fetchData()
    {
        $orderTypes = OrderType::where('branch_id', branch()->id)->get();

        $this->orderTypeFields = $orderTypes->map(function ($orderType) {
            return [
                'id' => $orderType->id,
                'orderTypeName' => $orderType->order_type_name,
                'enabled' => (bool)$orderType->is_active,
                'type' => $orderType->type ?? '',
                'isDefault' => $orderType->is_default,
            ];
        })->toArray();
    }

    public function addMoreOrderTypeFields()
    {
        $this->orderTypeFields[] = [
            'id' => null,
            'orderTypeName' => '',
            'enabled' => true,
            'type' => '',
            'isDefault' => false,
        ];
    }


    public function showConfirmationOrderTypeField($index, $id = null)
    {
        // Don't delete default order types
        if (isset($this->orderTypeFields[$index]['isDefault']) && $this->orderTypeFields[$index]['isDefault']) {
            $this->alert('error', __('modules.settings.cannotDeleteDefaultOrderType'), [
                'toast' => true,
                'position' => 'top-end',
                'showCancelButton' => false,
            ]);
            return;
        }

        if (is_null($id)) {
            $this->removeOrderTypeField($index);
        } else {
            $this->confirmDeleteOrderTypeModal = true;
            $this->fieldId = $id;
            $this->fieldIndex = $index;
        }
    }

    public function deleteAndRemove($id, $index)
    {
        if ($id) {
            OrderType::destroy($id);
        }

        $this->removeOrderTypeField($index);
        $this->reset(['fieldId', 'fieldIndex', 'confirmDeleteOrderTypeModal']);
        $this->alert('success', __('messages.orderTypeDeleted'), [
            'toast' => true,
            'position' => 'top-end',
            'showCancelButton' => false,
        ]);
    }

    public function removeOrderTypeField($index)
    {
        if (isset($this->orderTypeFields[$index])) {
            unset($this->orderTypeFields[$index]);
            $this->orderTypeFields = array_values($this->orderTypeFields);
        }
    }

    public function saveOrderTypes()
    {
        // Filter out empty fields
        $this->orderTypeFields = array_values(array_filter($this->orderTypeFields, function ($field) {
            return !empty($field['orderTypeName']);
        }));

        $messages = [
            'orderTypeFields.*.orderTypeName.required' => __('validation.orderTypeNameRequired'),
            'orderTypeFields.*.type.required' => __('validation.orderTypeRequired'),
            'orderTypeFields.*.type.in' => __('validation.invalidOrderType'),
            'defaultOrderTypeId.required' => __('validation.defaultOrderTypeRequired'),
            'defaultOrderTypeId.exists' => __('validation.invalidOrderType'),
        ];

        $this->validate($this->rules(), $messages);

        $branchId = branch()->id;

        foreach ($this->orderTypeFields as $field) {
            OrderType::updateOrCreate(
                ['id' => $field['id']],
                [
                    'branch_id' => $branchId,
                    'order_type_name' => $field['orderTypeName'],
                    'is_active' => $field['enabled'],
                    'type' => $field['type'],
                    'slug' => Str::slug($field['orderTypeName'], '_'),
                ]
            );
        }

        // Save popup disable and default order type settings
        $this->settings->disable_order_type_popup = $this->disableOrderTypePopup;
        $this->settings->default_order_type_id = $this->disableOrderTypePopup ? $this->defaultOrderTypeId : null;
        $this->settings->save();

        $this->fetchData();
        $this->alert('success', __('messages.settingsUpdated'), [
            'toast' => true,
            'position' => 'top-end',
            'showCancelButton' => false,
        ]);
    }

    public function updatedAllowCustomOrderTypeOptions($value)
    {
        if (!$value) {
            $this->orderTypeFields = [];
        } elseif (empty($this->orderTypeFields)) {
            $this->fetchData();
        }

        // Update the settings
        $this->settings->show_order_type_options = $value;
        $this->settings->save();

        session()->forget('restaurant');

        $this->alert('success', __('messages.settingsUpdated'), [
            'toast' => true,
            'position' => 'top-end',
            'showCancelButton' => false,
        ]);
    }

    public function updatedDisableOrderTypePopup($value)
    {
        if (!$value) {
            // Clear default order type when popup is re-enabled
            $this->defaultOrderTypeId = null;
        }

        // Update the settings immediately
        $this->settings->disable_order_type_popup = $value;
        $this->settings->default_order_type_id = $value ? $this->defaultOrderTypeId : null;
        $this->settings->save();

        session()->forget('restaurant');
    }

    public function updatedDefaultOrderTypeId($value)
    {
        // Save the default order type immediately if popup is disabled
        if ($this->disableOrderTypePopup) {
            $this->settings->default_order_type_id = $value;
            $this->settings->save();
            session()->forget('restaurant');
        }
    }

    public function getEnabledOrderTypesProperty()
    {
        return OrderType::where('branch_id', branch()->id)
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(function ($orderType) {
                return [$orderType->id => $orderType->order_type_name];
            });
    }

    public function render()
    {
        return view('livewire.settings.custom-order-types');
    }
}
