<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller

{
    /**
     * GET /api/profile
     * Retorna usuario + roles + permisos
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user'      => $user,
            'roles'     => $user->getRoleNames(),
            'permisos'  => $user->getAllPermissions()->pluck('name'),
            'token'     => $request->bearerToken(),
        ]);
    }

    /**
     * PUT /api/profile
     * Actualiza datos básicos
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => ['sometimes', 'string', 'max:100'],
            'email' => [
                'sometimes', 'email', 'max:150',
                Rule::unique('users')->ignore($user->id)
            ],
            'company' => ['sometimes', 'string', 'max:120'],
        ]);

        $user->update($validated);

        return response()->json([
            'user'     => $user->fresh(),
            'roles'    => $user->getRoleNames(),
            'permisos' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    /**
     * POST /api/profile/avatar
     * Sube imagen con multipart
     */
    public function updateAvatar(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'image' => 'required|image|max:2048', // 2 MB
        ]);

        // borrar imagen previa si existe
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // guardar nueva
        $path = $request->file('image')->store('avatars', 'public');

        $user->avatar = $path;
        $user->save();

        return response()->json([
            'user'     => $user->fresh(),
            'roles'    => $user->getRoleNames(),
            'permisos' => $user->getAllPermissions()->pluck('name'),
        ]);
    }
}
