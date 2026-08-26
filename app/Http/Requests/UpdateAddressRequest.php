<?php

namespace App\Http\Requests;

class UpdateAddressRequest extends StoreAddressRequest
{
    /**
     * আপডেটে একই নিয়ম — ownership AddressService-এ যাচাই হয়
     */
    public function rules(): array
    {
        return parent::rules();
    }
}
