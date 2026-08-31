<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotificationSetting;
use App\Services\PushNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admin Panel → Settings → Notifications → Browser Push Notifications —
 * the master/role/event-type control center. See PushNotificationSetting
 * for the column list and PushNotificationService for how every one of
 * these booleans is actually enforced.
 */
class PushNotificationSettingController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $columns = array_merge(
            ['push_notifications_enabled', 'push_admin_enabled', 'push_customer_enabled', 'push_driver_enabled'],
            array_keys(PushNotificationSetting::ADMIN_EVENTS),
            array_keys(PushNotificationSetting::DRIVER_EVENTS),
            array_keys(PushNotificationSetting::CUSTOMER_EVENTS),
        );

        $data = [];

        foreach ($columns as $column) {
            $data[$column] = $request->boolean($column);
        }

        PushNotificationSetting::current()->update($data);

        return redirect()
            ->route('admin.notification-settings.edit')
            ->with('status', __('Browser push notification settings saved.'));
    }

    public function sendTest(PushNotificationService $pushNotifications): RedirectResponse
    {
        $result = $pushNotifications->sendTest(Auth::guard('admin')->user());

        return redirect()
            ->route('admin.notification-settings.edit')
            ->with($result['sent'] ? 'status' : 'error', $result['message']);
    }
}
