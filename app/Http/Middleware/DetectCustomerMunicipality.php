<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DetectCustomerMunicipality
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Se estiver logado, usa o município do perfil
        if (Auth::check() && Auth::user()->municipality) {
            Session::put('customer_province', Auth::user()->province);
            Session::put('customer_municipality', Auth::user()->municipality);
        }

        // 2. Se cliente enviou município no checkout, atualiza na sessão
        if ($request->filled('shipping_address.municipality')) {
            Session::put('customer_province', $request->input('shipping_address.province'));
            Session::put('customer_municipality', $request->input('shipping_address.municipality'));
        }

        // 3. Se veio via query string (?municipality=Belas), também atualiza
        if ($request->filled('municipality')) {
            Session::put('customer_province', $request->input('province'));
            Session::put('customer_municipality', $request->input('municipality'));
        }

        return $next($request);
    }
}