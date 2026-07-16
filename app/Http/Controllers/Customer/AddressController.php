<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CustomerAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(): View
    {
        $addresses = Auth::guard('customer')->user()->addresses()->with('city')->get();
        $cities = City::where('is_active', true)->orderBy('name')->get();

        return view('customer.addresses.index', compact('addresses', 'cities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAddress($request);

        $customer = Auth::guard('customer')->user();

        if ($request->boolean('is_default')) {
            $customer->addresses()->update(['is_default' => false]);
        }

        $customer->addresses()->create($data + ['is_default' => $request->boolean('is_default')]);

        return back()->with('status', 'Address added successfully.');
    }

    public function update(Request $request, CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        $data = $this->validateAddress($request);

        if ($request->boolean('is_default')) {
            $address->customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data + ['is_default' => $request->boolean('is_default')]);

        return back()->with('status', 'Address updated successfully.');
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        $address->delete();

        return back()->with('status', 'Address removed.');
    }

    public function setDefault(CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        $address->customer->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('status', 'Default address updated.');
    }

    private function authorizeAddress(CustomerAddress $address): void
    {
        abort_unless($address->customer_id === Auth::guard('customer')->id(), 404);
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'address_line' => ['required', 'string', 'max:500'],
            'city_id' => ['nullable', 'exists:cities,id'],
        ]);
    }
}
