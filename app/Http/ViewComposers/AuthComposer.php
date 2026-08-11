<?php

namespace App\Http\ViewComposers;

use Creativeorange\Gravatar\Facades\Gravatar;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AuthComposer
{
    public function compose(): void
    {
        view()->share('isGuest', auth()->guest());
        view()->share('signedIn', auth()->check());
        view()->share('user', auth()->user());
        view()->share('timezone', session()->get('timezone'));

        if (auth()->user()) {
            view()->share('gravatarURL', Gravatar::get(auth()->user()->email, ['size' => 80, 'secure' => true]));
            view()->share('appointments', $this->getActiveAppointments());
        } else {
            view()->share('gravatarURL', 'http://placehold.it/150x150');
            view()->share('appointments', collect([]));
        }
    }

    protected function getActiveAppointments(): Collection
    {
        return Cache::get('user-{auth()->id()}-active-appointments', fn () => auth()->user()->appointments()->active()->get());
    }
}
