<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code'         => 'SHOP10',
                'name'         => 'R$ 10 OFF',
                'type'         => 'fixed',
                'value'        => 10,
                'min_purchase' => 50,
                'active'       => true,
            ],
            [
                'code'         => 'BLACK50',
                'name'         => '50% OFF (máx R$ 100)',
                'type'         => 'percent',
                'value'        => 50,
                'min_purchase' => 100,
                'max_discount' => 100,
                'usage_limit'  => 500,
                'active'       => true,
            ],
            [
                'code'         => 'BEMVINDO',
                'name'         => '10% OFF primeira compra',
                'type'         => 'percent',
                'value'        => 10,
                'min_purchase' => 0,
                'per_user_limit' => 1,
                'active'       => true,
            ],
            [
                'code'         => 'FRETEGRATIS',
                'name'         => 'Frete grátis',
                'type'         => 'free_shipping',
                'value'        => 0,
                'min_purchase' => 30,
                'active'       => true,
            ],
        ];

        foreach ($coupons as $data) {
            Coupon::updateOrCreate(['code' => $data['code']], $data);
        }
    }
}