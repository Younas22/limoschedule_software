<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    public function toggle(): RedirectResponse
    {
        $driver = Auth::guard('driver')->user();

        $driver->update(['is_online' => ! $driver->is_online]);

        return back()->with('status', $driver->is_online ? __('You are now online.') : __('You are now offline.'));
    }
}
