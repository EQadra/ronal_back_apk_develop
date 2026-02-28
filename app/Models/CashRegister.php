<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;
use App\Models\User;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',

        // 💰 Montos de apertura
        'opening_cash_pen',
        'opening_cash_bob',
        'opening_cash_usd',
        'opening_gold',

        // 🔒 Montos de cierre
        'closing_cash_pen',
        'closing_cash_bob',
        'closing_cash_usd',
        'closing_gold',

        // 👤 Usuario que abrió / cerró caja
        'opened_by',
        'closed_by',

        // 📌 Estado
        'status', // open | closed

        // 📅 Fecha de operación
        'date',

        // 📊 Saldos calculados
        'balance_pen',
        'balance_usd',
        'balance_bob',
    ];

    /* =========================
       Relaciones
    ========================= */

    // Usuario que abrió la caja
    public function userOpened()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    // Usuario que cerró la caja
    public function userClosed()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    // Alias opcionales
    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    // Transacciones relacionadas a esta caja
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /* =========================
       Métodos de utilidad
    ========================= */

    /**
     * Retorna true si la caja está abierta
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Aplica la transacción al saldo de la caja
     */
    public function applyTransaction(Transaction $transaction): void
    {
        // Suma los totales de la transacción a los balances
        $this->balance_pen += $transaction->total_pen;
        $this->balance_usd += $transaction->total_usd;
        $this->balance_bob += $transaction->total_bob;

        $this->save();
    }

    /**
     * Retorna el total de la caja por moneda
     */
    public function getTotal(string $moneda): float
    {
        return match($moneda) {
            'PEN' => $this->balance_pen,
            'USD' => $this->balance_usd,
            'BOB' => $this->balance_bob,
            default => 0,
        };
    }
}