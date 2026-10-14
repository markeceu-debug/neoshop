<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class UpdateSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Comissão
            [
                'key'         => 'commission_rate',
                'value'       => '10',
                'type'        => 'decimal',
                'description' => 'Comissão da plataforma (%)',
            ],
            [
                'key'         => 'commission_min',
                'value'       => '200',
                'type'        => 'decimal',
                'description' => 'Comissão mínima por pedido (R$)',
            ],
            [
                'key'         => 'guarantee_days',
                'value'       => '7',
                'type'        => 'int',
                'description' => 'Dias de garantia após entrega',
            ],

            // Dados bancários (cliente paga aqui)
            [
                'key'         => 'bank_holder',
                'value'       => 'NeoShop Angola, Lda',
                'type'        => 'string',
                'description' => 'Titular da conta',
            ],
            [
                'key'         => 'bank_name',
                'value'       => 'BAI',
                'type'        => 'string',
                'description' => 'Banco',
            ],
            [
                'key'         => 'bank_iban',
                'value'       => 'AO06 0000 0000 0000 0000 0000 0',
                'type'        => 'string',
                'description' => 'IBAN para transferências',
            ],
            [
                'key'         => 'bank_account',
                'value'       => '0000 0000 0000 0000 0',
                'type'        => 'string',
                'description' => 'Número da conta',
            ],
            [
                'key'         => 'multicaixa_express',
                'value'       => '923 000 000',
                'type'        => 'string',
                'description' => 'Multicaixa Express',
            ],

            // Saque
            [
                'key'         => 'min_withdrawal',
                'value'       => '5000',
                'type'        => 'decimal',
                'description' => 'Valor mínimo para saque (R$)',
            ],

            // Loja
            [
                'key'         => 'store_name',
                'value'       => 'NeoShop',
                'type'        => 'string',
                'description' => 'Nome da loja',
            ],
            [
                'key'         => 'store_email',
                'value'       => 'contato@neoshop.com.br',
                'type'        => 'string',
                'description' => 'E-mail de contato',
            ],
            [
                'key'         => 'store_phone',
                'value'       => '0800 123 4567',
                'type'        => 'string',
                'description' => 'Telefone de contato',
            ],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}