<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CashRegisterController extends Controller
{
    /* =========================
       Abrir caja
    ========================= */
public function open(Request $request)
{
    $user = Auth::guard('api')->user();

    $cashRegister = CashRegister::create([
        'company_id' => $user->company_id,
        'name' => 'Caja ' . now()->format('Y-m-d'),
        'date' => now()->toDateString(),

        'opening_cash_pen' => $request->opening_cash_pen,
        'opening_cash_bob' => $request->opening_cash_bob,
        'opening_cash_usd' => $request->opening_cash_usd,
        'opening_gold' => $request->opening_gold,

        'balance_pen' => $request->opening_cash_pen,
        'balance_bob' => $request->opening_cash_bob,
        'balance_usd' => $request->opening_cash_usd,

        'opened_by' => $user->id,
        'status' => 'open'
    ]);

    return response()->json([
        'message' => 'Caja abierta correctamente',
        'data' => $cashRegister
    ]);
}
    /* =========================
       Cerrar caja
    ========================= */

    public function close(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'closing_cash_pen' => 'required|numeric|min:0',
            'closing_cash_bob' => 'nullable|numeric|min:0',
            'closing_cash_usd' => 'nullable|numeric|min:0',
            'closing_gold'     => 'required|numeric|min:0',
        ]);

        $cashRegister = CashRegister::current($user->company_id);

        if (!$cashRegister) {
            return response()->json([
                'success' => false,
                'message' => 'No hay caja abierta.'
            ], 404);
        }

        $cashRegister->close($user, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Caja cerrada correctamente',
            'data' => $cashRegister
        ]);
    }

    /* =========================
       Caja actual
    ========================= */

    public function current(Request $request)
    {
        $cashRegister = CashRegister::current($request->user()->company_id);

        return response()->json([
            'success' => true,
            'data' => $cashRegister
        ]);
    }

    /* =========================
       Historial de cajas
    ========================= */

    public function history(Request $request)
    {
        $cashRegisters = CashRegister::where('company_id', $request->user()->company_id)
            ->orderBy('date', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $cashRegisters
        ]);
    }

    /* =========================
       Ver detalle de caja
    ========================= */

    public function show(Request $request, $id)
    {
        $cashRegister = CashRegister::where('company_id', $request->user()->company_id)
            ->with(['transactions', 'openedBy', 'closedBy'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $cashRegister
        ]);
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

}