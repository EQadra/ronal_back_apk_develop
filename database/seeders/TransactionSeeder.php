<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\User;
use App\Models\CashRegister;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@demo.com')->first();
        $cash = CashRegister::where('company_id', $user->company_id)->first();

        for ($i = 1; $i <= 10; $i++) {
            Transaction::create([
                'company_id' => $user->company_id,
                'client_name' => "Cliente $i",
                'grams' => rand(5, 50),
                'purity' => 0.95,
                'discount_percentage' => rand(0, 5),
                'price_per_gram_pen' => 250,
                'price_per_gram_usd' => 68,
                'price_per_gram_bob' => 250,
                'price_per_oz' => 2100,
                'total_pen' => rand(500, 5000),
                'total_usd' => rand(150, 1500),
                'total_bob' => rand(500, 5000),
                'exchange_rate_pen_usd' => 3.7,
                'moneda' => ['PEN', 'USD', 'BOB'][rand(0, 2)],
                'tipo_venta' => 'regular',
                'type' => 'ingreso',
                'metal_type' => 'oro',
                'hora' => Carbon::now()->format('H:i:s'),
                'created_by' => $user->id,
                'cash_register_id' => $cash->id,
                'created_at' => Carbon::now()->subHours(rand(1, 48)),
            ]);
        }
    }
}
