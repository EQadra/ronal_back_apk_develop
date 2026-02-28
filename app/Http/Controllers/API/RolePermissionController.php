<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function __construct()
    {
        // 🔒 Protege todas las rutas con Sanctum
        $this->middleware(['auth:sanctum']);

        // ✅ (Opcional) Si quieres limitar a ciertos permisos Spatie:
        // $this->middleware(['permission:gestionar roles'])->only(['update', 'assignPermission', 'revokePermission']);
    }

    /** ✅ Listar todos los roles con sus permisos */
    public function index()
    {
        $roles = Role::with('permissions')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de roles con permisos',
            'data' => $roles,
        ]);
    }

    /** ✅ Mostrar permisos de un rol específico (solo nombres) */
    public function permissions($roleId)
    {
        $role = Role::with('permissions')->find($roleId);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permisos del rol',
            'data' => $role->permissions->pluck('name'),
        ]);
    }

    /** ✅ Sincronizar permisos (reemplaza los existentes) */
    public function update(Request $request, $roleId)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::find($roleId);
        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado',
            ], 404);
        }

        $role->syncPermissions($validated['permissions']);

        return response()->json([
            'success' => true,
            'message' => 'Permisos actualizados correctamente',
            'data' => $role->permissions->pluck('name'),
        ]);
    }

    /** ✅ Asignar un permiso individual al rol */
    public function assignPermission(Request $request, $roleId)
    {
        $validated = $request->validate([
            'permission' => 'required|string|exists:permissions,name',
        ]);

        $role = Role::find($roleId);
        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Rol no encontrado'], 404);
        }

        if ($role->hasPermissionTo($validated['permission'])) {
            return response()->json([
                'success' => false,
                'message' => 'El rol ya tiene este permiso',
            ], 400);
        }

        $role->givePermissionTo($validated['permission']);

        return response()->json([
            'success' => true,
            'message' => 'Permiso asignado correctamente al rol',
            'data' => $role->permissions->pluck('name'),
        ]);
    }

    /** 🚫 Revocar un permiso individual del rol */
    public function revokePermission(Request $request, $roleId)
    {
        $validated = $request->validate([
            'permission' => 'required|string|exists:permissions,name',
        ]);

        $role = Role::find($roleId);
        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Rol no encontrado'], 404);
        }

        if (!$role->hasPermissionTo($validated['permission'])) {
            return response()->json([
                'success' => false,
                'message' => 'El rol no tiene este permiso',
            ], 400);
        }

        $role->revokePermissionTo($validated['permission']);

        return response()->json([
            'success' => true,
            'message' => 'Permiso revocado correctamente del rol',
            'data' => $role->permissions->pluck('name'),
        ]);
    }
}
