<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingController extends Controller
{
    /**
     * Configuração de frete do vendedor.
     */
    public function edit()
    {
        $user = Auth::user();
        $zone = null;

        if ($user->province && $user->municipality) {
            $zone = ShippingZone::forMunicipality($user->province, $user->municipality);
        }

        return view('vendedor.shipping.edit', compact('user', 'zone'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $zone = ShippingZone::forMunicipality($user->province, $user->municipality);

        if (! $zone) {
            return back()->with('error', 'Ainda não entregamos no seu município.');
        }

        $data = $request->validate([
            'custom_shipping_enabled' => ['nullable', 'boolean'],
            'custom_shipping_fee'     => ['nullable', 'numeric', 'min:0', 'max:' . $zone->max_fee],
            'custom_free_above'       => ['nullable', 'numeric', 'min:0'],
            'custom_shipping_hours'   => ['nullable', 'integer', 'min:1', 'max:72'],
        ], [
            'custom_shipping_fee.max' => "O frete não pode ser maior que R$ {$zone->max_fee} (teto do município).",
        ]);

        $data['custom_shipping_enabled'] = $request->boolean('custom_shipping_enabled');
        $data['custom_free_above'] = $data['custom_free_above'] ?: null;
        $data['custom_shipping_hours'] = $data['custom_shipping_hours'] ?? $zone->standard_hours;

        $user->update($data);

        return redirect()
            ->route('vendor.shipping.edit')
            ->with('success', 'Configurações de frete salvas!');
    }
}