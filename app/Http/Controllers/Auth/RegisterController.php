<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\TG\Contracts\UserRegistrarInterface;
use Illuminate\Foundation\Auth\RegistersUsers;
use Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';

    public function __construct(
        private readonly UserRegistrarInterface $registrar,
    ) {
        $this->middleware('guest');
        parent::__construct();
    }

    protected function validator(array $data)
    {
        $rules = [
            'email'                => 'required|email|max:255|unique:users',
            'password'             => 'required|confirmed|min:6',
            'g-recaptcha-response' => 'required|captcha',
            'allow_register'       => 'required|accepted',
        ];

        if (app()->environment('local') || app()->environment('testing')) {
            unset($rules['g-recaptcha-response']);
        }

        $data['allow_register'] = config('root.app.allow_register', true);

        $messages = [
            'allow_register.accepted' => trans('app.allow_register'),
        ];

        return Validator::make($data, $rules, $messages);
    }

    protected function create(array $data)
    {
        return $this->registrar->register([
            'username' => md5("{$data['name']}/{$data['email']}"),
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
}
