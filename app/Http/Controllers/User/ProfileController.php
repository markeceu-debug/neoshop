<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'province'          => ['required', 'string', 'max:100'],
            'municipality'      => ['required', 'string', 'max:100'],
            'neighborhood'      => ['nullable', 'string', 'max:100'],
            'address_reference' => ['nullable', 'string', 'max:200'],
        ]);

        $user->update($data);

        return redirect()
            ->route('user.profile.edit')
            ->with('success', 'Perfil atualizado!');
    }
}