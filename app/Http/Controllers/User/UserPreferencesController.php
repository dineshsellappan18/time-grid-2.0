<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserPreferencesController extends Controller
{
    public function getProfile(): View
    {
        Log::info('user.profile', [
            'actor'     => auth()->id(),
            'resource'  => 'profile',
            'operation' => 'view',
        ]);

        $user = auth()->user();

        return view('user.profile', compact('user'));
    }

    public function postProfile(Request $request): RedirectResponse
    {
        Log::info('user.profile.update', [
            'actor'     => auth()->id(),
            'resource'  => 'profile',
            'operation' => 'update',
        ]);

        $user = auth()->user();

        $validated = $request->validate([
            'name'     => ['required', 'string', 'min:3', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['nullable', 'string', 'min:3', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['username'])) {
            $user->username = $validated['username'];
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        flash()->success(trans('user.msg.profile.success', ['default' => 'Profile updated successfully.']));

        return redirect()->route('user.profile');
    }

    public function getPreferences()
    {
        logger()->info(__METHOD__);

        $user = auth()->user();
        $parameters = config()->get('preferences.App\Models\User');
        $preferences = $user->preferences;

        $notificationPrefs = [
            ['key' => 'notify_appointment_reserved', 'label' => 'Appointment Reserved', 'help' => 'Receive a notification when a new appointment is booked.', 'value' => $user->pref('notify_appointment_reserved', true)],
            ['key' => 'notify_appointment_confirmed', 'label' => 'Appointment Confirmed', 'help' => 'Receive a notification when an appointment is confirmed.', 'value' => $user->pref('notify_appointment_confirmed', true)],
            ['key' => 'notify_appointment_canceled', 'label' => 'Appointment Canceled', 'help' => 'Receive a notification when an appointment is canceled.', 'value' => $user->pref('notify_appointment_canceled', true)],
            ['key' => 'notify_appointment_reminder', 'label' => 'Appointment Reminders', 'help' => 'Receive reminders before upcoming appointments.', 'value' => $user->pref('notify_appointment_reminder', true)],
            ['key' => 'notify_marketing_emails', 'label' => 'Marketing Emails', 'help' => 'Receive product updates and promotional offers.', 'value' => $user->pref('notify_marketing_emails', false)],
        ];

        return view('user.preferences.edit', compact('user', 'preferences', 'parameters', 'notificationPrefs'));
    }

    public function postPreferences(Request $request)
    {
        logger()->info(__METHOD__);

        $this->setUserPreferences($request->all());

        flash()->success(trans('user.msg.preferences.success'));

        return redirect()->back();
    }

    public function destroyAccount(Request $request): RedirectResponse
    {
        Log::info('user.account.delete', [
            'actor'     => auth()->id(),
            'resource'  => 'account',
            'operation' => 'delete',
        ]);

        $request->validate([
            'confirm_email' => ['required', 'email', function ($attribute, $value, $fail) {
                if ($value !== auth()->user()->email) {
                    $fail('The email address does not match your account.');
                }
            }],
        ]);

        $user = auth()->user();

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        flash()->success(trans('user.msg.account.deleted', ['default' => 'Your account has been permanently deleted.']));

        return redirect('/');
    }

    protected function setUserPreferences($preferences)
    {
        $parameters = config()->get('preferences.App\Models\User');

        $parametersKeys = array_flip(array_keys($parameters));

        $mergedPreferences = array_intersect_key($preferences, $parametersKeys);

        foreach ($mergedPreferences as $key => $value) {
            logger()->info(sprintf(
                "set preference: UserId:%s key:%s='%s' type:%s",
                auth()->user()->id,
                $key,
                $value,
                $parameters[$key]['type']
            ));

            auth()->user()->pref($key, $value, $parameters[$key]['type']);
        }
    }
}
