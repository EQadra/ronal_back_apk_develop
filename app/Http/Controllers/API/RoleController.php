<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        // 🔒 Protege todo con Sanctum + Spatie
        $this->middleware(['auth:sanctum']);
        // Si deseas: $this->middleware(['permission:gestionar roles'])->only(['store', 'update', 'destroy']);
    }

    /** ✅ Listar roles con permisos */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Lista de roles',
            'data'    => Role::with('permissions')->get()
        ]);
    }

    /** ✅ Crear rol */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'sanctum', // 👈 cambia 'web' a 'sanctum'
        ]);

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Rol creado exitosamente',
            'data'    => $role,
        ], 201);
    }

    /** ✅ Mostrar rol con permisos */
    public function show(Role $role)
    {
        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Rol encontrado',
            'data'    => $role,
        ]);
    }

    /** ✅ Actualizar rol */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $validated['name']]);
        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Rol actualizado correctamente',
            'data'    => $role,
        ]);
    }

    /** ✅ Eliminar rol */
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rol eliminado correctamente',
        ]);
    }

    /** ✅ Obtener permisos de un rol */
    public function permisos($id)
    {
        try {
            $role = Role::findOrFail($id);
            $permissions = $role->permissions()->pluck('name');

            return response()->json([
                'success' => true,
                'message' => 'Permisos del rol cargados correctamente',
                'data'    => $permissions,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener permisos del rol',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /** ✅ Asignar permiso a un rol */
    public function assignPermission(Request $request, $id)
    {
        $validated = $request->validate([
            'permission' => 'required|string|exists:permissions,name',
        ]);

        try {
            $role = Role::findOrFail($id);
            $role->givePermissionTo($validated['permission']);

            return response()->json([
                'success' => true,
                'message' => "Permiso '{$validated['permission']}' asignado correctamente.",
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar permiso',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /** ✅ Revocar permiso de un rol */
    public function revokePermission(Request $request, $id)
    {
        $validated = $request->validate([
            'permission' => 'required|string|exists:permissions,name',
        ]);

        try {
            $role = Role::findOrFail($id);
            $role->revokePermissionTo($validated['permission']);

            return response()->json([
                'success' => true,
                'message' => "Permiso '{$validated['permission']}' revocado correctamente.",
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al revocar permiso',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }
}
