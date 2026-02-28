<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\CashRegister;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use NumberFormatter;

class TransactionController extends Controller
{
    /**
     * 📋 Listar todas las transacciones
     */
    public function index()
    {
        try {
            $transactions = Transaction::with(['cashRegister', 'user'])
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($tx) => $this->formatTransaction($tx));

            return response()->json([
                'success' => true,
                'message' => 'Lista de transacciones obtenida correctamente.',
                'data'    => $transactions,
            ], 200);

        } catch (\Throwable $th) {
            Log::error('❌ Error al obtener transacciones', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las transacciones.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * 💰 Crear nueva transacción con logs y formateo
     */
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                $validated = $request->validate([
                    'grams'                 => 'required|numeric|min:0.01',
                    'purity'                => 'required|numeric|min:0|max:1',
                    'discount_percentage'   => 'nullable|numeric|min:0|max:100',
                    'price_per_oz'          => 'required|numeric|min:0',
                    'exchange_rate_pen_usd' => 'required|numeric|min:0.01',
                    'moneda'                => 'required|string|in:PEN,BOB,USD',
                    'tipo_venta'            => 'nullable|string|in:0,1',
                    'client_name'           => 'nullable|string|max:255',
                    'hora'                  => 'nullable|string',
                ]);

                $validated['metal_type']       = 'oro';
                $validated['created_by']       = Auth::id();
                $validated['cash_register_id'] = Auth::user()->cash_register_id ?? 1;
                $validated['hora']             = $validated['hora'] ?? now('America/Lima')->format('H:i:s');

                if (empty($validated['client_name'])) {
                    $validated['client_name'] = 'Cliente-' . now('America/Lima')->format('Ymd-His');
                }

                $grams       = $validated['grams'];
                $priceOz     = $validated['price_per_oz'];
                $exchange    = $validated['exchange_rate_pen_usd'];
                $purity      = $validated['purity'];
                $discountPct = $validated['discount_percentage'] ?? 0;

                $pricePerGramUSD = ($priceOz / 31.1035) * $purity;
                $pricePerGramPEN = $pricePerGramUSD * $exchange * (1 - $discountPct / 100);
                $pricePerGramBOB = $pricePerGramPEN;

                $totalPEN = $pricePerGramPEN * $grams;
                $totalUSD = $totalPEN / $exchange;
                $totalBOB = $totalPEN;

                // Asignamos al array validado
                $validated['price_per_gram_pen'] = $pricePerGramPEN;
                $validated['price_per_gram_usd'] = $pricePerGramUSD;
                $validated['price_per_gram_bob'] = $pricePerGramBOB;
                $validated['total_pen']          = $totalPEN;
                $validated['total_usd']          = $totalUSD;
                $validated['total_bob']          = $totalBOB;

                // 🔹 Log detallado antes de guardar
                Log::info('📌 Creando transacción', ['validated' => $validated]);

                $transaction = Transaction::create($validated);

                // Actualizar balance de caja
                $cashRegister = CashRegister::find($validated['cash_register_id']);
                if ($cashRegister && method_exists($cashRegister, 'isOpen') && $cashRegister->isOpen()) {
                    match ($validated['moneda']) {
                        'PEN' => $cashRegister->increment('balance_pen', $totalPEN),
                        'BOB' => $cashRegister->increment('balance_bob', $totalBOB),
                        'USD' => $cashRegister->increment('balance_usd', $totalUSD),
                        default => null,
                    };
                }

                // 🔹 Retornar con formato de precios
                return response()->json([
                    'success' => true,
                    'message' => 'Transacción registrada correctamente.',
                    'data'    => $this->formatTransaction($transaction->load(['cashRegister', 'user'])),
                ], 201);

            } catch (\Throwable $th) {
                Log::error('❌ Error al registrar transacción', [
                    'error' => $th->getMessage(),
                    'input' => $request->all()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar la transacción.',
                    'error'   => $th->getMessage(),
                ], 500);
            }
        });
    }

    /**
     * 📅 Obtener transacciones del día
     */
    public function day()
    {
        try {
            $startOfDay = Carbon::now('America/Lima')->startOfDay();
            $endOfDay   = Carbon::now('America/Lima')->endOfDay();

            $transactions = Transaction::with(['cashRegister', 'user'])
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($tx) => $this->formatTransaction($tx));

            return response()->json([
                'success' => true,
                'message' => 'Transacciones del día obtenidas correctamente.',
                'data'    => $transactions,
            ], 200);

        } catch (\Throwable $th) {
            Log::error('❌ Error al obtener transacciones del día', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las transacciones del día.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Formatear precios para que sean legibles
     */
    private function formatTransaction(Transaction $tx)
    {
        $formatter = new NumberFormatter('es_PE', NumberFormatter::DECIMAL);

        return [
            'id'                 => $tx->id,
            'client_name'        => $tx->client_name,
            'grams'              => $formatter->format($tx->grams),
            'purity'             => $formatter->format($tx->purity),
            'discount_percentage'=> $formatter->format($tx->discount_percentage),
            'price_per_gram_pen' => $formatter->format($tx->price_per_gram_pen),
            'price_per_gram_usd' => $formatter->format($tx->price_per_gram_usd),
            'price_per_gram_bob' => $formatter->format($tx->price_per_gram_bob),
            'price_per_oz'       => $formatter->format($tx->price_per_oz),
            'total_pen'          => $formatter->format($tx->total_pen),
            'total_usd'          => $formatter->format($tx->total_usd),
            'total_bob'          => $formatter->format($tx->total_bob),
            'exchange_rate_pen_usd' => $formatter->format($tx->exchange_rate_pen_usd),
            'moneda'             => $tx->moneda,
            'tipo_venta'         => $tx->tipo_venta,
            'metal_type'         => $tx->metal_type,
            'hora'               => $tx->hora,
            'created_by'         => $tx->created_by,
            'cash_register_id'   => $tx->cash_register_id,
            'created_at'         => $tx->created_at,
            'updated_at'         => $tx->updated_at,
            'cash_register'      => $tx->cashRegister,
            'user'               => $tx->user,
        ];
    }
}