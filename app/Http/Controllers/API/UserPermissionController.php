<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserPermissionController extends Controller
{
    public function __construct()
    {
        // 🔒 Protege todas las rutas usando token Sanctum o JWT
        $this->middleware(['auth:sanctum']);
    }

    /** ✅ Asignar permiso a un usuario */
    public function givePermission(Request $request, User $user)
    {
        $validated = $request->validate([
            'permission' => 'required|exists:permissions,name',
        ]);

        $user->givePermissionTo($validated['permission']);

        return response()->json([
            'success' => true,
            'message' => 'Permiso asignado correctamente',
            'data'    => $user->getAllPermissions()->pluck('name'),
        ], 200);
    }

    /** ✅ Revocar permiso a un usuario */
    public function revokePermission(Request $request, User $user)
    {
        $validated = $request->validate([
            'permission' => 'required|exists:permissions,name',
        ]);

        $user->revokePermissionTo($validated['permission']);

        return response()->json([
            'success' => true,
            'message' => 'Permiso revocado correctamente',
            'data'    => $user->getAllPermissions()->pluck('name'),
        ], 200);
    }

    /** ✅ Listar permisos del usuario */
    public function permissions(User $user)
    {
        return response()->json([
            'success' => true,
            'message' => 'Permisos del usuario',
            'data'    => $user->getAllPermissions()->pluck('name'),
        ], 200);
    }
}
