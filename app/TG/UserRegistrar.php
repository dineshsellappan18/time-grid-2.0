<?php

namespace App\TG;

use App\Events\NewUserWasRegistered;
use App\Exceptions\RegistrationFailedException;
use App\Models\User;
use App\TG\Contracts\UserRegistrarInterface;
use Illuminate\Support\Facades\Log;

class UserRegistrar implements UserRegistrarInterface
{
    public function register(array $data): User
    {
        $existingUser = User::where('email', $data['email'])->first();

        if ($existingUser !== null) {
            throw RegistrationFailedException::emailTaken($data['email']);
        }

        $user = User::create([
            'username' => $data['username'] ?? md5("{$data['name']}/{$data['email']}"),
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'] ?? null,
        ]);

        Log::info('UserRegistrar: user registered', [
            'user_id' => $user->id,
        ]);

        event(new NewUserWasRegistered($user));

        return $user;
    }

    public function findOrCreateFromOAuth(object $providerUser): User
    {
        $user = User::where('email', $providerUser->email)
            ->orWhere('username', $providerUser->nickname)
            ->first();

        if ($user !== null) {
            Log::info('UserRegistrar: existing user found via OAuth', [
                'user_id' => $user->id,
            ]);
            return $user;
        }

        $user = User::create([
            'username' => $providerUser->nickname,
            'name'     => $providerUser->nickname,
            'email'    => $providerUser->email,
        ]);

        Log::info('UserRegistrar: new user created via OAuth', [
            'user_id' => $user->id,
        ]);

        event(new NewUserWasRegistered($user));

        return $user;
    }

    public function linkExisting(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
