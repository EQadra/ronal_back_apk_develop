<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {

            $user = $request->user();
            $companyId = $user->company_id;

            $hoy = Carbon::today();

            // ==============================
            // 🔹 TODAS LAS VENTAS DEL DÍA
            // 🔹 SOLO DE SU EMPRESA
            // ==============================

            $ventasHoy = [
                'pen' => Transaction::where('company_id', $companyId)
                    ->whereDate('created_at', $hoy)
                    ->sum('total_pen'),

                'usd' => Transaction::where('company_id', $companyId)
                    ->whereDate('created_at', $hoy)
                    ->sum('total_usd'),

                'bob' => Transaction::where('company_id', $companyId)
                    ->whereDate('created_at', $hoy)
                    ->sum('total_bob'),
            ];

            $totalTransacciones = Transaction::where('company_id', $companyId)
                ->whereDate('created_at', $hoy)
                ->count();

            // ==============================
            // 📊 STATS
            // ==============================

            $stats = [
                [
                    "label" => "Ventas Hoy (PEN)",
                    "value" => $ventasHoy["pen"] ?? 0,
                    "icon"  => "cash"
                ],
                [
                    "label" => "Ventas Hoy (USD)",
                    "value" => $ventasHoy["usd"] ?? 0,
                    "icon"  => "cash-multiple"
                ],
                [
                    "label" => "Ventas Hoy (BOB)",
                    "value" => $ventasHoy["bob"] ?? 0,
                    "icon"  => "currency-usd"
                ],
                [
                    "label" => "Transacciones Hoy",
                    "value" => $totalTransacciones,
                    "icon"  => "history"
                ],
            ];

            $charts = [
                "gold" => [
                    "labels" => ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"],
                    "data"   => [220, 221, 219, 222, 223, 224, 225]
                ],
                "users" => [
                    "labels" => ["Ventas"],
                    "data"   => [$ventasHoy["pen"] ?? 0]
                ]
            ];

            $caja = [
                "apertura" => "08:00",
                "cierre" => "22:00",
                "saldo_inicial" => 0,
                "saldo_actual"  => $ventasHoy["pen"] ?? 0
            ];

            return response()->json([
                "success" => true,
                "message" => "Dashboard cargado correctamente.",
                "data" => [
                    "stats"  => $stats,
                    "caja"   => $caja,
                    "charts" => $charts,
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "message" => "Error al cargar dashboard.",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}