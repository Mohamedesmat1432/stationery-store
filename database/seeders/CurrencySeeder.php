<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'rate' => 1,
                'is_default' => true,
                'decimal_places' => 2,
            ],
            [
                'code' => 'EGP',
                'name' => 'Egyptian Pound',
                'symbol' => 'EGP',
                'rate' => 50,
                'is_default' => false,
                'decimal_places' => 2,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(['code' => $currency['code']], $currency);
        }
    }
}
