<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\RedirectResponse;

class AddressController extends Controller
{
    public function __construct(
        private AddressService $addressService,
    ) {}

    /**
     * চেকআউট/ঠিকানা ব্যবস্থাপনা থেকেই তৈরি — সবসময় checkout-এ ফেরত
     */
    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $this->addressService->create($request->user(), $request->validated());

        return redirect()
            ->route('checkout.index')
            ->with('success', __('checkout.messages.address_added'));
    }

    public function update(UpdateAddressRequest $request, Address $address): RedirectResponse
    {
        $this->addressService->update($request->user(), $address, $request->validated());

        return redirect()
            ->route('checkout.index')
            ->with('success', __('checkout.messages.address_updated'));
    }

    public function destroy(Address $address): RedirectResponse
    {
        $this->addressService->delete(auth()->user(), $address);

        return redirect()
            ->route('checkout.index')
            ->with('success', __('checkout.messages.address_deleted'));
    }

    public function setDefault(Address $address): RedirectResponse
    {
        $this->addressService->setDefault(auth()->user(), $address);

        return redirect()
            ->route('checkout.index')
            ->with('success', __('checkout.messages.default_set'));
    }
}
