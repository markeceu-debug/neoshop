<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Formulário de perfil do vendedor.
     */
    public function edit()
    {
        $user = Auth::user();

        return view('vendedor.profile.edit', [
            'user'      => $user,
            'provinces' => $this->provinces(),
        ]);
    }

    /**
     * Salva os dados do perfil.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'province'            => ['required', 'string', 'max:100'],
            'municipality'        => ['required', 'string', 'max:100'],
            'neighborhood'        => ['nullable', 'string', 'max:100'],
            'address_reference'   => ['nullable', 'string', 'max:200'],
        ]);

        $user->update($data);

        return redirect()
            ->route('vendor.profile.edit')
            ->with('success', 'Perfil atualizado!');
    }

    /**
     * Lista de províncias e municípios.
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