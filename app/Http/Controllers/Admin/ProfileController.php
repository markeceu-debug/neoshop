<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Página de visualização do perfil.
     */
    public function show()
    {
        $user = Auth::user();
        return view('admin.profile.show', compact('user'));
    }

    /**
     * Formulário de edição.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Atualiza os dados básicos (nome, e-mail, telefone, avatar).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'telefone' => ['nullable', 'string', 'max:20'],
            'avatar'   => ['nullable', 'image', 'max:2048'], // 2MB
        ]);

        if ($request->hasFile('avatar')) {
            // Remove antigo
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Perfil atualizado com sucesso!');
    }

    /**
     * Atualiza a senha.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'A senha atual está incorreta.',
            'password.confirmed'                => 'A confirmação de senha não confere.',
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Senha alterada com sucesso!');
    }

    /**
     * Remove o avatar atual.
     */
    public function destroyAvatar()
    {
        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Avatar removido com sucesso!');
    }
}