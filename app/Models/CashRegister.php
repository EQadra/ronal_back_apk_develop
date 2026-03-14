<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Company;

class CashRegister extends Model
{
    use HasFactory;

    /* =========================
       Constantes de estado
    ========================= */

    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';

    /* =========================
       Fillable
    ========================= */

    protected $fillable = [
        'company_id',
        'name',

        // apertura
        'opening_cash_pen',
        'opening_cash_bob',
        'opening_cash_usd',
        'opening_gold',

        // cierre
        'closing_cash_pen',
        'closing_cash_bob',
        'closing_cash_usd',
        'closing_gold',

        // usuarios
        'opened_by',
        'closed_by',

        // estado
        'status',

        // fecha
        'date',

        // balances
        'balance_pen',
        'balance_usd',
        'balance_bob',
    ];

    /* =========================
       Casts
    ========================= */

    protected $casts = [
        'opening_cash_pen' => 'float',
        'opening_cash_bob' => 'float',
        'opening_cash_usd' => 'float',
        'opening_gold' => 'float',

        'closing_cash_pen' => 'float',
        'closing_cash_bob' => 'float',
        'closing_cash_usd' => 'float',
        'closing_gold' => 'float',

        'balance_pen' => 'float',
        'balance_usd' => 'float',
        'balance_bob' => 'float',

        'date' => 'date',
    ];

    /* =========================
       Relaciones
    ========================= */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /* =========================
       Métodos de estado
    ========================= */

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /* =========================
       Obtener caja actual
    ========================= */

    public static function current(int $companyId): ?self
    {
        return self::where('company_id', $companyId)
            ->where('status', self::STATUS_OPEN)
            ->first();
    }

    /* =========================
       Aplicar transacción
    ========================= */

    public function applyTransaction(Transaction $transaction): void
    {
        $this->balance_pen = ($this->balance_pen ?? 0) + ($transaction->total_pen ?? 0);
        $this->balance_usd = ($this->balance_usd ?? 0) + ($transaction->total_usd ?? 0);
        $this->balance_bob = ($this->balance_bob ?? 0) + ($transaction->total_bob ?? 0);

        $this->save();
    }

    /* =========================
       Obtener total por moneda
    ========================= */

    public function getTotal(string $currency): float
    {
        return match ($currency) {
            'PEN' => $this->balance_pen ?? 0,
            'USD' => $this->balance_usd ?? 0,
            'BOB' => $this->balance_bob ?? 0,
            default => 0,
        };
    }

    /* =========================
       Cerrar caja
    ========================= */

    public function close(User $user, array $data): void
    {
        $this->update([
            'closing_cash_pen' => $data['closing_cash_pen'] ?? 0,
            'closing_cash_usd' => $data['closing_cash_usd'] ?? 0,
            'closing_cash_bob' => $data['closing_cash_bob'] ?? 0,
            'closing_gold'     => $data['closing_gold'] ?? 0,
            'closed_by'        => $user->id,
            'status'           => self::STATUS_CLOSED,
        ]);
    }
}