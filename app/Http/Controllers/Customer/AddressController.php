<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        $addresses = $request->user()->addresses()->latest()->get();

        return view('customer.addresses.index', [
            'addresses' => $addresses,
        ]);
    }

    public function create(): View
    {
        return view('customer.addresses.create');
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $address = $request->user()->addresses()->create($request->validated());

        if ($request->boolean('is_default')) {
            $address->setAsDefault();
        }

        return redirect()->route('customer.addresses.index')
            ->with('status', 'ঠিকানা যোগ করা হয়েছে।');
    }

    public function edit(Address $address): View
    {
        abort_unless(
            auth()->id() === $address->user_id,
            403
        );

        return view('customer.addresses.edit', [
            'address' => $address,
        ]);
    }

    public function update(AddressRequest $request, Address $address): RedirectResponse
    {
        abort_unless(
            auth()->id() === $address->user_id,
            403
        );

        $address->update($request->validated());

        if ($request->boolean('is_default')) {
            $address->setAsDefault();
        }

        return redirect()->route('customer.addresses.index')
            ->with('status', 'ঠিকানা আপডেট করা হয়েছে।');
    }

    public function destroy(Address $address): RedirectResponse
    {
        abort_unless(
            auth()->id() === $address->user_id,
            403
        );

        $address->delete();

        return redirect()->route('customer.addresses.index')
            ->with('status', 'ঠিকানা মুছে ফেলা হয়েছে।');
    }
}
