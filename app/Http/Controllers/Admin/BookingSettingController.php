<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingSettingController extends Controller
{
    public function edit(): View
    {
        $settings = BookingSetting::current();
        $whatsappTokens = \App\Services\WhatsAppBookingLinkGenerator::availableTokens();

        return view('admin.booking-settings.edit', compact('settings', 'whatsappTokens'));
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = BookingSetting::current();

        $data = $request->validate([
            'whatsapp_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]*$/'],
            'whatsapp_message_template' => ['nullable', 'string', 'max:2000'],
        ]);

        $settings->update([
            'manual_booking_enabled' => $request->boolean('manual_booking_enabled'),
            'website_booking_enabled' => $request->boolean('website_booking_enabled'),
            'guest_booking_enabled' => $request->boolean('guest_booking_enabled'),
            'voice_search_enabled' => $request->boolean('voice_search_enabled'),
            'auto_confirmation_enabled' => $request->boolean('auto_confirmation_enabled'),
            'driver_auto_assignment_enabled' => $request->boolean('driver_auto_assignment_enabled'),
            'whatsapp_number' => $data['whatsapp_number'] ?? null,
            'whatsapp_message_template' => ($data['whatsapp_message_template'] ?? null) ?: BookingSetting::DEFAULT_WHATSAPP_TEMPLATE,
        ]);

        return back()->with('status', 'Booking settings updated successfully.');
    }
}
