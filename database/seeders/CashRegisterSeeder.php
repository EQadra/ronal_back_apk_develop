<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CashRegister;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;

class CashRegisterSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar admin
        $admin = User::where('email', 'admin@demo.com')->first();

        if (!$admin) {
            $this->command->error('No existe el usuario admin@demo.com');
            return;
        }

        // Si el usuario no tiene empresa, asignar o crear una
        if (!$admin->company_id) {
            $company = Company::first() ?? Company::create([
                'name' => 'Empresa Demo'
            ]);

            $admin->company_id = $company->id;
            $admin->save();
        }

        CashRegister::firstOrCreate(
            [
                'company_id' => $admin->company_id,
                'date' => Carbon::today()->toDateString(),
            ],
            [
                'name' => 'Caja Principal',
                'status' => CashRegister::STATUS_OPEN,

                'opening_cash_pen' => 0,
                'opening_cash_usd' => 0,
                'opening_cash_bob' => 0,
                'opening_gold' => 0,

                'balance_pen' => 0,
                'balance_usd' => 0,
                'balance_bob' => 0,

                'opened_by' => $admin->id
            ]
        );
    }
}