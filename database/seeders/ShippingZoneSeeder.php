<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    /**
     * Municípios principais de Angola com tarifas padrão.
     * O admin pode ajustar depois no painel.
     */
    public function run(): void
    {
        // [província, município, padrão, expresso, grátis acima de]
        $zones = [
            // ---------- LUANDA ----------
            ['Luanda', 'Luanda',        800.00, 1500.00, 30000.00],
            ['Luanda', 'Belas',         900.00, 1800.00, 35000.00],
            ['Luanda', 'Cacuaco',      1000.00, 2000.00, 40000.00],
            ['Luanda', 'Cazenga',       900.00, 1800.00, 35000.00],
            ['Luanda', 'Icolo e Bengo',1200.00, 2400.00, 50000.00],
            ['Luanda', 'Quiçama',      1400.00, 2800.00, 60000.00],
            ['Luanda', 'Talatona',     1000.00, 2000.00, 40000.00],
            ['Luanda', 'Viana',        1100.00, 2200.00, 45000.00],

            // ---------- BENGO ----------
            ['Bengo', 'Dande',         1200.00, 2400.00, 50000.00],
            ['Bengo', 'Ambriz',        1500.00, 3000.00, 70000.00],
            ['Bengo', 'Bula Atumba',   1300.00, 2600.00, 60000.00],
            ['Bengo', 'Dembos',        1300.00, 2600.00, 60000.00],
            ['Bengo', 'Nambuangongo',  1400.00, 2800.00, 70000.00],
            ['Bengo', 'Pango Aluquém', 1300.00, 2600.00, 60000.00],

            // ---------- BENGUELA ----------
            ['Benguela', 'Benguela',   1000.00, 2000.00, 40000.00],
            ['Benguela', 'Lobito',     1000.00, 2000.00, 40000.00],
            ['Benguela', 'Catumbela',  1100.00, 2200.00, 45000.00],
            ['Benguela', 'Baía Farta', 1300.00, 2600.00, 60000.00],
            ['Benguela', 'Balombo',    1400.00, 2800.00, 70000.00],
            ['Benguela', 'Bocoio',     1400.00, 2800.00, 70000.00],
            ['Benguela', 'Caimbambo',  1400.00, 2800.00, 70000.00],
            ['Benguela', 'Chongoroi',  1500.00, 3000.00, 75000.00],
            ['Benguela', 'Cubal',      1500.00, 3000.00, 75000.00],
            ['Benguela', 'Ganda',      1500.00, 3000.00, 75000.00],

            // ---------- HUAMBO ----------
            ['Huambo', 'Huambo',       1000.00, 2000.00, 40000.00],
            ['Huambo', 'Caála',        1100.00, 2200.00, 45000.00],
            ['Huambo', 'Bailundo',     1200.00, 2400.00, 50000.00],
            ['Huambo', 'Ecunha',       1300.00, 2600.00, 60000.00],
            ['Huambo', 'Londuimbali',  1300.00, 2600.00, 60000.00],
            ['Huambo', 'Longonjo',     1300.00, 2600.00, 60000.00],
            ['Huambo', 'Mungo',        1300.00, 2600.00, 60000.00],
            ['Huambo', 'Tchicala-Tcholoanga', 1400.00, 2800.00, 65000.00],
            ['Huambo', 'Tchindjenje',  1400.00, 2800.00, 65000.00],
            ['Huambo', 'Ucuma',        1300.00, 2600.00, 60000.00],

            // ---------- HUÍLA ----------
            ['Huíla', 'Lubango',       1100.00, 2200.00, 45000.00],
            ['Huíla', 'Matala',        1300.00, 2600.00, 60000.00],
            ['Huíla', 'Caconda',       1400.00, 2800.00, 70000.00],
            ['Huíla', 'Cacula',        1400.00, 2800.00, 70000.00],
            ['Huíla', 'Caluquembe',    1400.00, 2800.00, 70000.00],
            ['Huíla', 'Chibia',        1300.00, 2600.00, 60000.00],
            ['Huíla', 'Chicomba',      1400.00, 2800.00, 70000.00],
            ['Huíla', 'Chipindo',      1500.00, 3000.00, 75000.00],
            ['Huíla', 'Cuvango',       1500.00, 3000.00, 75000.00],
            ['Huíla', 'Gambos',        1600.00, 3200.00, 80000.00],
            ['Huíla', 'Humpata',       1200.00, 2400.00, 50000.00],
            ['Huíla', 'Jamba',         1500.00, 3000.00, 75000.00],
            ['Huíla', 'Quilengues',    1400.00, 2800.00, 70000.00],
            ['Huíla', 'Quipungo',      1400.00, 2800.00, 70000.00],

            // ---------- CABINDA ----------
            ['Cabinda', 'Cabinda',     1200.00, 2400.00, 50000.00],
            ['Cabinda', 'Belize',      1400.00, 2800.00, 70000.00],
            ['Cabinda', 'Buco-Zau',    1400.00, 2800.00, 70000.00],
            ['Cabinda', 'Cacongo',     1400.00, 2800.00, 70000.00],

            // ---------- MALANJE ----------
            ['Malanje', 'Malanje',     1100.00, 2200.00, 45000.00],
            ['Malanje', 'Cacuso',      1300.00, 2600.00, 60000.00],
            ['Malanje', 'Calandula',   1400.00, 2800.00, 70000.00],
            ['Malanje', 'Cambundi-Catembo', 1400.00, 2800.00, 70000.00],
            ['Malanje', 'Cangandala',  1300.00, 2600.00, 60000.00],
            ['Malanje', 'Caombo',      1400.00, 2800.00, 70000.00],
            ['Malanje', 'Cuaba Nzogo', 1400.00, 2800.00, 70000.00],
            ['Malanje', 'Cunda-Dia-Baze', 1400.00, 2800.00, 70000.00],
            ['Malanje', 'Luquembo',    1400.00, 2800.00, 70000.00],
            ['Malanje', 'Marimba',     1400.00, 2800.00, 70000.00],
            ['Malanje', 'Massango',    1400.00, 2800.00, 70000.00],
            ['Malanje', 'Mucari',      1400.00, 2800.00, 70000.00],
            ['Malanje', 'Quela',       1400.00, 2800.00, 70000.00],
            ['Malanje', 'Quirima',     1400.00, 2800.00, 70000.00],

            // ---------- NAMIBE ----------
            ['Namibe', 'Moçâmedes',    1200.00, 2400.00, 50000.00],
            ['Namibe', 'Bibala',       1400.00, 2800.00, 70000.00],
            ['Namibe', 'Camucuio',     1400.00, 2800.00, 70000.00],
            ['Namibe', 'Tômbwa',       1300.00, 2600.00, 60000.00],
            ['Namibe', 'Virei',        1600.00, 3200.00, 80000.00],

            // ---------- UÍGE ----------
            ['Uíge', 'Uíge',           1200.00, 2400.00, 50000.00],
            ['Uíge', 'Alto Cauale',    1400.00, 2800.00, 70000.00],
            ['Uíge', 'Ambuíla',        1400.00, 2800.00, 70000.00],
            ['Uíge', 'Bembe',          1400.00, 2800.00, 70000.00],
            ['Uíge', 'Buengas',        1400.00, 2800.00, 70000.00],
            ['Uíge', 'Bungo',          1400.00, 2800.00, 70000.00],
            ['Uíge', 'Damba',          1400.00, 2800.00, 70000.00],
            ['Uíge', 'Milunga',        1400.00, 2800.00, 70000.00],
            ['Uíge', 'Mucaba',         1400.00, 2800.00, 70000.00],
            ['Uíge', 'Negage',         1300.00, 2600.00, 60000.00],
            ['Uíge', 'Puri',           1400.00, 2800.00, 70000.00],
            ['Uíge', 'Quimbele',       1500.00, 3000.00, 75000.00],
            ['Uíge', 'Quitexe',        1400.00, 2800.00, 70000.00],
            ['Uíge', 'Sanza Pombo',    1500.00, 3000.00, 75000.00],
            ['Uíge', 'Songo',          1400.00, 2800.00, 70000.00],
            ['Uíge', 'Zombo',          1400.00, 2800.00, 70000.00],

            // ---------- ZAIRE ----------
            ['Zaire', 'Mabanza Congo', 1300.00, 2600.00, 60000.00],
            ['Zaire', 'Cuimba',        1400.00, 2800.00, 70000.00],
            ['Zaire', 'Noqui',         1500.00, 3000.00, 75000.00],
            ['Zaire', 'Nzeto',         1400.00, 2800.00, 70000.00],
            ['Zaire', 'Soyo',          1400.00, 2800.00, 70000.00],
            ['Zaire', 'Tomboco',       1400.00, 2800.00, 70000.00],

            // ---------- MOXICO ----------
            ['Moxico', 'Luena',        1300.00, 2600.00, 60000.00],
            ['Moxico', 'Camanongue',   1500.00, 3000.00, 75000.00],
            ['Moxico', 'Léua',         1500.00, 3000.00, 75000.00],
            ['Moxico', 'Luau',         1600.00, 3200.00, 80000.00],
            ['Moxico', 'Luchazes',     1600.00, 3200.00, 80000.00],
            ['Moxico', 'Cameia',       1500.00, 3000.00, 75000.00],

            // ---------- LUNDA NORTE ----------
            ['Lunda Norte', 'Dundo',   1400.00, 2800.00, 70000.00],
            ['Lunda Norte', 'Cambulo', 1600.00, 3200.00, 80000.00],
            ['Lunda Norte', 'Capenda-Camulemba', 1600.00, 3200.00, 80000.00],
            ['Lunda Norte', 'Caungula',1600.00, 3200.00, 80000.00],
            ['Lunda Norte', 'Chitato', 1600.00, 3200.00, 80000.00],
            ['Lunda Norte', 'Cuango',  1600.00, 3200.00, 80000.00],
            ['Lunda Norte', 'Cuílo',   1700.00, 3400.00, 85000.00],
            ['Lunda Norte', 'Lóvua',   1600.00, 3200.00, 80000.00],
            ['Lunda Norte', 'Lubalo',  1700.00, 3400.00, 85000.00],
            ['Lunda Norte', 'Xá-Muteba', 1600.00, 3200.00, 80000.00],

            // ---------- LUNDA SUL ----------
            ['Lunda Sul', 'Saurimo',   1400.00, 2800.00, 70000.00],
            ['Lunda Sul', 'Cacolo',    1600.00, 3200.00, 80000.00],
            ['Lunda Sul', 'Dala',      1600.00, 3200.00, 80000.00],
            ['Lunda Sul', 'Muconda',   1600.00, 3200.00, 80000.00],

            // ---------- BIÉ ----------
            ['Bié', 'Kuito',           1300.00, 2600.00, 60000.00],
            ['Bié', 'Andulo',          1500.00, 3000.00, 75000.00],
            ['Bié', 'Camacupa',        1500.00, 3000.00, 75000.00],
            ['Bié', 'Catabola',        1500.00, 3000.00, 75000.00],
            ['Bié', 'Chinguar',        1500.00, 3000.00, 75000.00],
            ['Bié', 'Chitembo',        1500.00, 3000.00, 75000.00],
            ['Bié', 'Cuemba',          1600.00, 3200.00, 80000.00],
            ['Bié', 'Cunhinga',        1500.00, 3000.00, 75000.00],
            ['Bié', 'Nharea',          1500.00, 3000.00, 75000.00],

            // ---------- CUNENE ----------
            ['Cunene', 'Ondjiva',      1400.00, 2800.00, 70000.00],
            ['Cunene', 'Cahama',       1600.00, 3200.00, 80000.00],
            ['Cunene', 'Cuanhama',     1600.00, 3200.00, 80000.00],
            ['Cunene', 'Curoca',       1700.00, 3400.00, 85000.00],
            ['Cunene', 'Cuvelai',      1600.00, 3200.00, 80000.00],
            ['Cunene', 'Namacunde',    1600.00, 3200.00, 80000.00],
            ['Cunene', 'Ombadja',      1600.00, 3200.00, 80000.00],

            // ---------- CUANDO CUBANGO ----------
            ['Cuando Cubango', 'Menongue', 1400.00, 2800.00, 70000.00],
            ['Cuando Cubango', 'Calai',    1600.00, 3200.00, 80000.00],
            ['Cuando Cubango', 'Cuangar',  1700.00, 3400.00, 85000.00],
            ['Cuando Cubango', 'Cuchi',    1600.00, 3200.00, 80000.00],
            ['Cuando Cubango', 'Cuito Cuanavale', 1700.00, 3400.00, 85000.00],
            ['Cuando Cubango', 'Dirico',   1700.00, 3400.00, 85000.00],
            ['Cuando Cubango', 'Longa',    1700.00, 3400.00, 85000.00],
            ['Cuando Cubango', 'Mavinga',  1700.00, 3400.00, 85000.00],
            ['Cuando Cubango', 'Nancova',  1700.00, 3400.00, 85000.00],
            ['Cuando Cubango', 'Rivungo',  1700.00, 3400.00, 85000.00],

            // ---------- CUANZA NORTE ----------
            ['Cuanza Norte', 'N\'dalatando', 1200.00, 2400.00, 50000.00],
            ['Cuanza Norte', 'Ambaca',    1400.00, 2800.00, 70000.00],
            ['Cuanza Norte', 'Banga',     1400.00, 2800.00, 70000.00],
            ['Cuanza Norte', 'Bolongongo',1400.00, 2800.00, 70000.00],
            ['Cuanza Norte', 'Cambambe',  1300.00, 2600.00, 60000.00],
            ['Cuanza Norte', 'Cazengo',   1200.00, 2400.00, 50000.00],
            ['Cuanza Norte', 'Golungo Alto',1400.00, 2800.00, 70000.00],
            ['Cuanza Norte', 'Gonguembo', 1400.00, 2800.00, 70000.00],
            ['Cuanza Norte', 'Lucala',    1300.00, 2600.00, 60000.00],
            ['Cuanza Norte', 'Quiculungo',1400.00, 2800.00, 70000.00],
            ['Cuanza Norte', 'Samba Caju',1400.00, 2800.00, 70000.00],

            // ---------- CUANZA SUL ----------
            ['Cuanza Sul', 'Sumbe',       1200.00, 2400.00, 50000.00],
            ['Cuanza Sul', 'Cassongue',   1400.00, 2800.00, 70000.00],
            ['Cuanza Sul', 'Cela',        1400.00, 2800.00, 70000.00],
            ['Cuanza Sul', 'Conda',       1400.00, 2800.00, 70000.00],
            ['Cuanza Sul', 'Ebo',         1400.00, 2800.00, 70000.00],
            ['Cuanza Sul', 'Gabela (Amboim)', 1300.00, 2600.00, 60000.00],
            ['Cuanza Sul', 'Mussende',    1400.00, 2800.00, 70000.00],
            ['Cuanza Sul', 'Porto Amboim',1300.00, 2600.00, 60000.00],
            ['Cuanza Sul', 'Quibala',     1300.00, 2600.00, 60000.00],
            ['Cuanza Sul', 'Quilenda',    1400.00, 2800.00, 70000.00],
            ['Cuanza Sul', 'Seles',       1300.00, 2600.00, 60000.00],

            // ---------- LITORAL/OUTRAS (fallback) ----------
            // Adicionar conforme necessidade
        ];

        foreach ($zones as [$province, $municipality, $standard, $express, $freeAbove]) {
            ShippingZone::updateOrCreate(
                [
                    'province'     => $province,
                    'municipality' => $municipality,
                ],
                [
                    'standard_fee'   => $standard,
                    'standard_hours' => 6,
                    'express_fee'    => $express,
                    'express_hours'  => 2,
                    'pickup_enabled' => true,
                    'free_above'     => $freeAbove,
                    'active'         => true,
                ]
            );
        }
    }
}