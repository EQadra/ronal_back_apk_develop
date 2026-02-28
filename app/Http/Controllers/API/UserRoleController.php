<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserRoleController extends Controller
{
    public function __construct()
    {
        // 🔒 Protege todas las rutas con Sanctum
        $this->middleware(['auth:sanctum']);
        // Opcional: puedes restringir ciertas acciones con permisos Spatie:
        // $this->middleware(['permission:gestionar roles'])->only(['assignRole', 'revokeRole']);
    }

    /** ✅ Asignar rol a un usuario */
    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user->assignRole($validated['role']);

        return response()->json([
            'success' => true,
            'message' => 'Rol asignado correctamente al usuario.',
            'roles'   => $user->getRoleNames(),
        ], 200);
    }

    /** ✅ Revocar rol de un usuario */
    public function revokeRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user->removeRole($validated['role']);

        return response()->json([
            'success' => true,
            'message' => 'Rol revocado correctamente del usuario.',
            'roles'   => $user->getRoleNames(),
        ], 200);
    }

    /** ✅ Sincronizar roles (reemplaza los existentes) */
    public function syncRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->syncRoles($validated['roles']);

        return response()->json([
            'success' => true,
            'message' => 'Roles sincronizados correctamente.',
            'roles'   => $user->getRoleNames(),
        ], 200);
    }

    /** ✅ Obtener roles del usuario */
    public function roles(User $user)
    {
        return response()->json([
            'success' => true,
            'message' => 'Roles del usuario.',
            'data'    => $user->getRoleNames(),
        ], 200);
    }
}
