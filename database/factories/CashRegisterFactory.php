<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\CashRegister;

class CashRegisterFactory extends Factory
{
    protected $model = CashRegister::class;

    public function definition(): array
    {
        // Usuario que abre
        $openedBy = User::inRandomOrder()->first()?->id ?? User::factory()->create()->id;

        // Estado aleatorio
        $status = $this->faker->randomElement(['open', 'closed']);

        // Si está cerrada, debe tener usuario que cierra
        $closedBy = $status === 'closed'
            ? (User::inRandomOrder()->first()?->id ?? User::factory()->create()->id)
            : null;

        // Apertura
        $opening_pen = $this->faker->randomFloat(2, 50, 500);
        $opening_gold = $this->faker->randomFloat(2, 5, 50);

        // Cierre solo si la caja está cerrada
        $closing_pen = $status === 'closed'
            ? $opening_pen + $this->faker->randomFloat(2, -50, 200)
            : null;

        return [
            'name' => 'Principal',

            'opening_cash_pen'  => $opening_pen,
            'opening_cash_bob'  => 0,
            'opening_cash_usd'  => 0,
            'opening_gold'      => $opening_gold,

            'closing_cash_pen'  => $closing_pen,
            'closing_cash_bob'  => $closing_pen ? 0 : null,
            'closing_cash_usd'  => $closing_pen ? 0 : null,
            'closing_gold'      => $closing_pen ? $opening_gold : null,

            'balance_pen' => $closing_pen ?? $opening_pen,
            'balance_bob' => 0,
            'balance_usd' => 0,

            'status'    => $status,
            'opened_by' => $openedBy,
            'closed_by' => $closedBy,

            'date' => $this->faker->date(),
        ];
    }
}
