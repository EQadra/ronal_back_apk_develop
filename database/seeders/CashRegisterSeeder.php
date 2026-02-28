<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CashRegister;
use App\Models\User;
use Carbon\Carbon;

class CashRegisterSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@demo.com')->firstOrFail();

        CashRegister::firstOrCreate(
            [
                'company_id' => $admin->company_id,
                'date' => Carbon::today(),
            ],
            [
                'name' => 'Caja Principal',
                'status' => 'open',
                'opening_cash_pen' => 0,
                'opening_cash_usd' => 0,
                'opening_cash_bob' => 0,
                'opening_gold' => 0,
                'balance_pen' => 0,
                'balance_usd' => 0,
                'balance_bob' => 0,
                'opened_by' => $admin->id,
            ]
        );
    }
}
