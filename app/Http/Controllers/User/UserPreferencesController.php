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

        $parameters = config()->get('preferences.App\Models\User');
        $preferences = auth()->user()->preferences;

        return view('user.preferences.edit', compact('preferences', 'parameters'));
    }

    public function postPreferences(Request $request)
    {
        logger()->info(__METHOD__);

        $this->setUserPreferences($request->all());

        flash()->success(trans('user.msg.preferences.success'));

        return redirect()->back();
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
