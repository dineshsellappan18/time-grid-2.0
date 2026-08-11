<?php

namespace App\TG\Repositories;

use App\Models\User;

class UserRepository
{
    public function findOrCreate(object $userData): User
    {
        $user = User::where('email', '=', $userData->email)->orWhere('username', '=', $userData->nickname)->first();
        if ($user !== null) {
            return $user;
        }

        return User::create([
            'username' => $userData->nickname,
            'name'     => $userData->nickname,
            'email'    => $userData->email,
        ]);
    }
}
