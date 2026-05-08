<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    use AuthenticatesUsers;

    protected function redirectTo()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return '/home';
        }

        if ($user->hasRole('sisicha')) {
            return '/sisipedia/categories';
        }

        if ($user->hasRole('biblioteca')) {
            return '/Bibliotecario';
        }

        if ($user->hasRole('videos')) {
            return '/Administrador-Videos';
        }

        if ($user->hasRole('audios')) {
            return '/Administrador-Canciones';
        }

        if ($user->hasRole('alumno')) {
            return '/';
        }

        return '/';
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
