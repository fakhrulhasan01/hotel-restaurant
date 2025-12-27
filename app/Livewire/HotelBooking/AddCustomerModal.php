<?php

namespace App\Livewire\HotelBooking;

use App\Models\Country;
use App\Models\Customer;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class AddCustomerModal extends Component
{
    use LivewireAlert;

    public $customerName;
    public $customerPhone;
    public $customerPhoneCode;
    public $customerEmail;
    public $customerAddress;

    public $phoneCodeSearch = '';
    public $phoneCodeIsOpen = false;
    public $allPhoneCodes;
    public $filteredPhoneCodes;

    public function mount()
    {
        // Initialize phone codes
        $this->allPhoneCodes = collect(Country::pluck('phonecode')->unique()->filter()->values());
        $this->filteredPhoneCodes = $this->allPhoneCodes;

        // Set default phone code
        $this->customerPhoneCode = $this->allPhoneCodes->first();
    }

    public function updatedPhoneCodeIsOpen($value)
    {
        if (!$value) {
            $this->reset(['phoneCodeSearch']);
            $this->updatedPhoneCodeSearch();
        }
    }

    public function updatedPhoneCodeSearch()
    {
        $this->filteredPhoneCodes = $this->allPhoneCodes->filter(function ($phonecode) {
            return str_contains($phonecode, $this->phoneCodeSearch);
        })->values();
    }

    public function selectPhoneCode($phonecode)
    {
        $this->customerPhoneCode = $phonecode;
        $this->phoneCodeIsOpen = false;
        $this->phoneCodeSearch = '';
        $this->updatedPhoneCodeSearch();
    }

    public function submitForm()
    {
        $this->validate([
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'required|string|max:20',
            'customerPhoneCode' => 'required',
            'customerEmail' => 'nullable|email|max:255',
            'customerAddress' => 'nullable|string',
        ]);

        $customer = Customer::create([
            'name' => $this->customerName,
            'phone' => $this->customerPhone,
            'phone_code' => $this->customerPhoneCode,
            'email' => $this->customerEmail,
            'delivery_address' => $this->customerAddress,
            'restaurant_id' => restaurant()->id,
        ]);

        $this->dispatch('customerAdded', customerId: $customer->id);

        // Reset form
        $this->reset(['customerName', 'customerPhone', 'customerEmail', 'customerAddress']);
        $this->customerPhoneCode = $this->allPhoneCodes->first();
    }

    public function render()
    {
        return view('livewire.hotel-booking.add-customer-modal', [
            'phonecodes' => $this->filteredPhoneCodes,
        ]);
    }
}
