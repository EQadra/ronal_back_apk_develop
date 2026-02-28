<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashRegister;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class CashRegisterController extends Controller
{
    /**
     * Open today's cash register
     */
    public function open(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'opening_cash_pen' => ['required', 'numeric', 'min:0'],
                'opening_cash_bob' => ['nullable', 'numeric', 'min:0'],
                'opening_cash_usd' => ['nullable', 'numeric', 'min:0'],
                'opening_gold'     => ['required', 'numeric', 'min:0'],
            ]);

            $today = Carbon::today()->toDateString();

            // Prevent duplicate cash register
            if (CashRegister::where('date', $today)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cash register is already opened today.',
                    'data'    => null,
                ], 409);
            }

            $cashRegister = CashRegister::create([
                'date'             => $today,
                'opening_cash_pen' => $validated['opening_cash_pen'],
                'opening_cash_bob' => $validated['opening_cash_bob'] ?? 0,
                'opening_cash_usd' => $validated['opening_cash_usd'] ?? 0,
                'opening_gold'     => $validated['opening_gold'],
                'balance_pen'      => $validated['opening_cash_pen'],
                'balance_bob'      => $validated['opening_cash_bob'] ?? 0,
                'balance_usd'      => $validated['opening_cash_usd'] ?? 0,
                'opened_by'        => $user->id,
                'status'           => 'open',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cash register opened successfully.',
                'data'    => $cashRegister,
            ], 201);

        } catch (\Throwable $th) {
            \Log::error('Error opening cash register: '.$th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error opening cash register.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Close today's cash register
     */
    public function close(Request $request)
    {
        try {
            $validated = $request->validate([
                'closing_cash_pen' => ['required', 'numeric', 'min:0'],
                'closing_cash_bob' => ['nullable', 'numeric', 'min:0'],
                'closing_cash_usd' => ['nullable', 'numeric', 'min:0'],
                'closing_gold'     => ['required', 'numeric', 'min:0'],
            ]);

            $cashRegister = CashRegister::where('date', Carbon::today()->toDateString())->first();

            if (!$cashRegister) {
                return response()->json([
                    'success' => false,
                    'message' => 'No cash register opened today.',
                    'data'    => null,
                ], 404);
            }

            if ($cashRegister->status === 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cash register is already closed.',
                    'data'    => $cashRegister,
                ], 409);
            }

            $cashRegister->update([
                'closing_cash_pen' => $validated['closing_cash_pen'],
                'closing_cash_bob' => $validated['closing_cash_bob'] ?? 0,
                'closing_cash_usd' => $validated['closing_cash_usd'] ?? 0,
                'closing_gold'     => $validated['closing_gold'],
                'closed_by'        => $request->user()->id,
                'status'           => 'closed',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cash register closed successfully.',
                'data'    => $cashRegister,
            ], 200);

        } catch (\Throwable $th) {
            \Log::error('Error closing cash register: '.$th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error closing cash register.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Get today's cash register
     */
    public function today()
    {
        try {
            $cashRegister = CashRegister::where('date', Carbon::today()->toDateString())->first();

            if (!$cashRegister) {
                return response()->json([
                    'success' => false,
                    'message' => 'No cash register opened today.',
                    'data'    => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Today\'s cash register found.',
                'data'    => $cashRegister,
            ], 200);

        } catch (\Throwable $th) {
            \Log::error('Error fetching today\'s cash register: '.$th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching today\'s cash register.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

        /**
     * Listar cierres de caja
     */
    public function closures()
    {
        try {
            $closures = CashRegister::with(['openedBy','closedBy'])
                ->orderByDesc('date')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Lista de cierres obtenida correctamente.',
                'data'    => $closures,
            ]);

        } catch (\Throwable $th) {
            Log::error("Error al listar cierres: ".$th->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cierres.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }


    /**
     * Resumen completo del día
     */
   public function summaryDetail($date)
{
    try {
        $cashRegister = CashRegister::with(['openedBy','closedBy'])
            ->where('date', $date)
            ->first();

        if (!$cashRegister) {
            return response()->json([
                'success' => false,
                'message' => 'No existe caja para esa fecha.',
            ], 404);
        }

        $transactions = Transaction::with('user')
            ->where('cash_register_id', $cashRegister->id)
            ->orderBy('created_at')
            ->get();

        $totals = [
            'total_pen'   => $transactions->sum('total_pen'),
            'total_usd'   => $transactions->sum('total_usd'),
            'total_bob'   => $transactions->sum('total_bob'),
            'total_grams' => $transactions->sum('grams'),
            'count'       => $transactions->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detalle del cierre obtenido.',
            'data' => [
                'cash_register' => $cashRegister,
                'transactions'  => $transactions,
                'totals'        => $totals,
            ]
        ]);

    } catch (\Throwable $th) {
        Log::error("Error en summaryDetail: " . $th->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error al obtener detalle del cierre.',
            'error'   => $th->getMessage(),
        ], 500);
    }
}

  /**
     * Resumen completo del día
     */

 
}
