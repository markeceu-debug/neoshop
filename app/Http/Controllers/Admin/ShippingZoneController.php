<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\ShippingZonesDataTable;
use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\Request;

class ShippingZoneController extends Controller
{
    public function index(ShippingZonesDataTable $dataTable)
    {
        $stats = [
            'total'    => ShippingZone::count(),
            'active'   => ShippingZone::where('active', true)->count(),
            'inactive' => ShippingZone::where('active', false)->count(),
        ];

        return $dataTable->render('admin.shipping-zones.index', compact('stats'));
    }

    public function create()
    {
        return view('admin.shipping-zones.create', [
            'provinces' => $this->provinces(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        ShippingZone::create($data);

        return redirect()
            ->route('admin.shipping-zones.index')
            ->with('success', 'Zona de frete criada!');
    }

    public function edit(ShippingZone $shippingZone)
    {
        return view('admin.shipping-zones.edit', [
            'zone'      => $shippingZone,
            'provinces' => $this->provinces(),
        ]);
    }

    public function update(Request $request, ShippingZone $shippingZone)
    {
        $data = $this->validated($request, $shippingZone->id);

        $shippingZone->update($data);

        return redirect()
            ->route('admin.shipping-zones.index')
            ->with('success', 'Zona de frete atualizada!');
    }

    public function destroy(ShippingZone $shippingZone)
    {
        $shippingZone->delete();

        return redirect()
            ->route('admin.shipping-zones.index')
            ->with('success', 'Zona excluída!');
    }

    /**
     * Ativar/desativar em massa.
     */
    public function toggleActive(Request $request, ShippingZone $shippingZone)
    {
        $shippingZone->update(['active' => ! $shippingZone->active]);

        return response()->json([
            'status' => 'success',
            'active' => $shippingZone->fresh()->active,
            'message' => $shippingZone->active ? 'Zona ativada!' : 'Zona desativada!',
        ]);
    }

    /* ============================================================
     * HELPERS
     * ============================================================ */

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'province'        => ['required', 'string', 'max:100'],
            'municipality'    => ['required', 'string', 'max:100', 'unique:shipping_zones,municipality,' . $ignoreId . ',id,province,' . $request->input('province')],
            'standard_fee'    => ['required', 'numeric', 'min:0'],
            'standard_hours'  => ['required', 'integer', 'min:1', 'max:72'],
            'express_fee'     => ['nullable', 'numeric', 'min:0'],
            'express_hours'   => ['nullable', 'integer', 'min:1', 'max:72'],
            'pickup_enabled'  => ['nullable', 'boolean'],
            'pickup_address'  => ['nullable', 'string', 'max:200'],
            'free_above'      => ['nullable', 'numeric', 'min:0'],
            'active'          => ['nullable', 'boolean'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ];

        $data = $request->validate($rules);

        $data['pickup_enabled'] = $request->boolean('pickup_enabled');
        $data['active']         = $request->boolean('active');
        $data['express_fee']    = $data['express_fee'] ?? 0;
        $data['express_hours']  = $data['express_hours'] ?? 2;
        $data['free_above']     = $data['free_above'] ?: null;

        return $data;
    }

    /**
     * Lista de províncias + municípios de Angola.
     * Usado nos selects do formulário.
     */
    private function provinces(): array
    {
        return [
            'Bengo' => ['Dande', 'Ambriz', 'Bula Atumba', 'Dembos', 'Nambuangongo', 'Pango Aluquém'],
            'Benguela' => ['Benguela', 'Lobito', 'Catumbela', 'Baía Farta', 'Balombo', 'Bocoio', 'Caimbambo', 'Chongoroi', 'Cubal', 'Ganda'],
            'Bié' => ['Kuito', 'Andulo', 'Camacupa', 'Catabola', 'Chinguar', 'Chitembo', 'Cuemba', 'Cunhinga', 'Nharea'],
            'Cabinda' => ['Cabinda', 'Belize', 'Buco-Zau', 'Cacongo'],
            'Cuando Cubango' => ['Menongue', 'Calai', 'Cuangar', 'Cuchi', 'Cuito Cuanavale', 'Dirico', 'Longa', 'Mavinga', 'Nancova', 'Rivungo'],
            'Cuanza Norte' => ['N\'dalatando', 'Ambaca', 'Banga', 'Bolongongo', 'Cambambe', 'Cazengo', 'Golungo Alto', 'Gonguembo', 'Lucala', 'Quiculungo', 'Samba Caju'],
            'Cuanza Sul' => ['Sumbe', 'Cassongue', 'Cela', 'Conda', 'Ebo', 'Gabela (Amboim)', 'Mussende', 'Porto Amboim', 'Quibala', 'Quilenda', 'Seles'],
            'Cunene' => ['Ondjiva', 'Cahama', 'Cuanhama', 'Curoca', 'Cuvelai', 'Namacunde', 'Ombadja'],
            'Huambo' => ['Huambo', 'Caála', 'Bailundo', 'Ecunha', 'Londuimbali', 'Longonjo', 'Mungo', 'Tchicala-Tcholoanga', 'Tchindjenje', 'Ucuma'],
            'Huíla' => ['Lubango', 'Matala', 'Caconda', 'Cacula', 'Caluquembe', 'Chibia', 'Chicomba', 'Chipindo', 'Cuvango', 'Gambos', 'Humpata', 'Jamba', 'Quilengues', 'Quipungo'],
            'Luanda' => ['Luanda', 'Belas', 'Cacuaco', 'Cazenga', 'Icolo e Bengo', 'Quiçama', 'Talatona', 'Viana'],
            'Lunda Norte' => ['Dundo', 'Cambulo', 'Capenda-Camulemba', 'Caungula', 'Chitato', 'Cuango', 'Cuílo', 'Lóvua', 'Lubalo', 'Xá-Muteba'],
            'Lunda Sul' => ['Saurimo', 'Cacolo', 'Dala', 'Muconda'],
            'Malanje' => ['Malanje', 'Cacuso', 'Calandula', 'Cambundi-Catembo', 'Cangandala', 'Caombo', 'Cuaba Nzogo', 'Cunda-Dia-Baze', 'Luquembo', 'Marimba', 'Massango', 'Mucari', 'Quela', 'Quirima'],
            'Moxico' => ['Luena', 'Camanongue', 'Léua', 'Luau', 'Luchazes', 'Cameia'],
            'Namibe' => ['Moçâmedes', 'Bibala', 'Camucuio', 'Tômbwa', 'Virei'],
            'Uíge' => ['Uíge', 'Alto Cauale', 'Ambuíla', 'Bembe', 'Buengas', 'Bungo', 'Damba', 'Milunga', 'Mucaba', 'Negage', 'Puri', 'Quimbele', 'Quitexe', 'Sanza Pombo', 'Songo', 'Zombo'],
            'Zaire' => ['Mabanza Congo', 'Cuimba', 'Noqui', 'Nzeto', 'Soyo', 'Tomboco'],
        ];
    }
}