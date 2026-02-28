<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        // 🔒 Protege todos los endpoints con Sanctum
        $this->middleware(['auth:sanctum']);
        // Opcional: $this->middleware(['permission:gestionar permisos'])->only(['store', 'update', 'destroy']);
    }

    /** ✅ Listar permisos */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Lista de permisos',
            'data'    => Permission::all()
        ]);
    }

    /** ✅ Crear permiso */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'sanctum', // 👈 cambia a sanctum
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permiso creado exitosamente',
            'data'    => $permission,
        ], 201);
    }

    /** ✅ Mostrar un permiso */
    public function show(Permission $permission)
    {
        return response()->json([
            'success' => true,
            'message' => 'Permiso encontrado',
            'data'    => $permission,
        ]);
    }

    /** ✅ Actualizar permiso */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $validated['name']]);

        return response()->json([
            'success' => true,
            'message' => 'Permiso actualizado correctamente',
            'data'    => $permission,
        ]);
    }

    /** ✅ Eliminar permiso */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permiso eliminado correctamente',
        ]);
    }
}
