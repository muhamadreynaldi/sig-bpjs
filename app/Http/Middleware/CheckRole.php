<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role  Role yang diizinkan (misal: 'admin')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check() || Auth::user()->role !== $role) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman tersebut.');
        }
        return $next($request);
    }
}