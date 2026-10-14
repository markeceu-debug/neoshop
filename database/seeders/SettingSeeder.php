<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key'   => 'commission_rate',
                'value' => '10',
                'type'  => 'decimal',
                'description' => 'Comissão da plataforma em % sobre o valor do produto',
            ],
            [
                'key'   => 'guarantee_days',
                'value' => '7',
                'type'  => 'int',
                'description' => 'Dias de garantia após entrega antes de liberar o dinheiro',
            ],
            [
                'key'   => 'min_withdrawal',
                'value' => '5000',
                'type'  => 'decimal',
                'description' => 'Valor mínimo para saque (em Kz)',
            ],
            [
                'key'   => 'bank_holder',
                'value' => 'NeoShop Angola, Lda',
                'type'  => 'string',
                'description' => 'Titular da conta que recebe pagamentos',
            ],
            [
                'key'   => 'bank_name',
                'value' => 'BAI',
                'type'  => 'string',
                'description' => 'Banco que recebe pagamentos',
            ],
            [
                'key'   => 'bank_iban',
                'value' => 'AO06 0000 0000 0000 0000 0000 0',
                'type'  => 'string',
                'description' => 'IBAN para transferências',
            ],
            [
                'key'   => 'multicaixa_express',
                'value' => '923 000 000',
                'type'  => 'string',
                'description' => 'Número Multicaixa Express',
            ],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}