<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class WelcomeController extends Controller
{
    public function index(): View
    {
        logger()->info(__METHOD__);

        return view('welcome');
    }
}
