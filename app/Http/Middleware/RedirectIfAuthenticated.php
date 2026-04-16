<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                if ($user instanceof User && $user->hasRole('admin')) {
                    return redirect('/home');
                }

                if ($user instanceof User && $user->hasRole('sisicha')) {
                    return redirect('/Admini-Sisichakunay');
                }

                if ($user instanceof User && $user->hasRole('biblioteca')) {
                    return redirect('/Bibliotecario');
                }

                if ($user instanceof User && $user->hasRole('videos')) {
                    return redirect('/Administrador-Videos');
                }

                if ($user instanceof User && $user->hasRole('audios')) {
                    return redirect('/Administrador-Canciones');
                }

                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
