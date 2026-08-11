<?php

namespace App;

use App\TG\Repositories\UserRepository;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\Factory as Socialite;

class AuthenticateUser
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly Socialite $socialite,
        private readonly Guard $auth,
    ) {
    }

    public function execute(string $provider, bool $hasCode, AuthenticateUserListener $listener)
    {
        if (!$hasCode) {
            return $this->getAuthorizationFirst($provider);
        }

        $providerUser = $this->getUser($provider);

        Log::info('oauth.callback', [
            'provider'         => $provider,
            'provider_id_hash' => hash('sha256', (string) $providerUser->getId()),
            'email_domain'     => $this->extractDomain($providerUser->getEmail()),
        ]);

        $user = $this->users->findOrCreate($providerUser);

        if ($user === null) {
            return $this->getAuthorizationFirst($provider);
        }

        $this->auth->login($user, true);

        return $listener->userHasLoggedIn($user);
    }

    private function getAuthorizationFirst(string $provider)
    {
        return $this->socialite->driver($provider)->redirect();
    }

    private function getUser(string $provider)
    {
        return $this->socialite->driver($provider)->user();
    }

    private function extractDomain(?string $email): ?string
    {
        if ($email === null || $email === '') {
            return null;
        }

        $parts = explode('@', $email);

        return count($parts) === 2 ? $parts[1] : null;
    }
}
