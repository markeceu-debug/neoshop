<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Formulário de configurações.
     */
    public function edit()
    {
        $settings = [
            // Comissão
            'commission_rate'   => Setting::get('commission_rate', 10),
            'commission_min'    => Setting::get('commission_min', 200),
            'guarantee_days'    => Setting::get('guarantee_days', 7),
            'min_withdrawal'    => Setting::get('min_withdrawal', 5000),

            // Banco
            'bank_holder'       => Setting::get('bank_holder', ''),
            'bank_name'         => Setting::get('bank_name', ''),
            'bank_iban'         => Setting::get('bank_iban', ''),
            'bank_account'      => Setting::get('bank_account', ''),
            'multicaixa_express' => Setting::get('multicaixa_express', ''),

            // Loja
            'store_name'        => Setting::get('store_name', 'NeoShop'),
            'store_email'       => Setting::get('store_email', ''),
            'store_phone'       => Setting::get('store_phone', ''),
        ];

        return view('admin.settings.edit', compact('settings'));
    }

    /**
     * Salva as configurações.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            // Comissão
            'commission_rate'    => ['required', 'numeric', 'min:0', 'max:100'],
            'commission_min'     => ['required', 'numeric', 'min:0'],
            'guarantee_days'     => ['required', 'integer', 'min:1', 'max:60'],
            'min_withdrawal'     => ['required', 'numeric', 'min:0'],

            // Banco
            'bank_holder'        => ['required', 'string', 'max:150'],
            'bank_name'          => ['required', 'string', 'max:100'],
            'bank_iban'          => ['required', 'string', 'max:34'],
            'bank_account'       => ['required', 'string', 'max:30'],
            'multicaixa_express' => ['nullable', 'string', 'max:20'],

            // Loja
            'store_name'         => ['required', 'string', 'max:100'],
            'store_email'        => ['nullable', 'email', 'max:150'],
            'store_phone'        => ['nullable', 'string', 'max:30'],
        ]);

        $types = [
            'commission_rate'    => 'decimal',
            'commission_min'     => 'decimal',
            'guarantee_days'     => 'int',
            'min_withdrawal'     => 'decimal',
            'bank_holder'        => 'string',
            'bank_name'          => 'string',
            'bank_iban'          => 'string',
            'bank_account'       => 'string',
            'multicaixa_express' => 'string',
            'store_name'         => 'string',
            'store_email'        => 'string',
            'store_phone'        => 'string',
        ];

        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '', $types[$key] ?? 'string');
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Configurações salvas!');
    }
}