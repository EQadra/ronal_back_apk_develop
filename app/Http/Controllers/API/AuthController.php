<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Registro (User + Company)
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email|unique:users,email',
            'password'     => 'required|string|min:6|confirmed',
            'company_name' => 'required|string|max:255',
        ]);

        // Crear empresa
        $company = Company::create([
            'name' => $validated['company_name'],
            'slug' => Str::slug($validated['company_name']) . '-' . uniqid(),
            'plan' => 'free',
        ]);

        // Crear usuario (owner)
        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'company_id' => $company->id,
            'genero'     => 'masculino',
            'avatar'     => 'https://picsum.photos/seed/male/200',
        ]);

        // Opcional: asignar rol owner
        $user->assignRole('owner');

        $token = Auth::guard('api')->login($user);

        return response()->json([
            'message' => 'Usuario y empresa creados correctamente.',
            'user'    => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'avatar'   => $user->avatar,
                'genero'   => $user->genero,
                'telefono' => $user->telefono,
                'company'  => $company,
            ],
            'roles'    => $user->getRoleNames(),
            'permisos' => $user->getAllPermissions()->pluck('name'),
            'token'    => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ], 201);
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no coinciden con nuestros registros.'],
            ]);
        }

        $user = Auth::guard('api')->user()->load('company');

        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'user'    => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'avatar'   => $user->avatar,
                'genero'   => $user->genero,
                'telefono' => $user->telefono,
                'company'  => $user->company,
            ],
            'roles'    => $user->getRoleNames(),
            'permisos' => $user->getAllPermissions()->pluck('name'),
            'token'    => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ]);
    }

    /**
     * Perfil autenticado
     */
    public function profile()
    {
        $user = Auth::guard('api')->user()->load('company');

        return response()->json([
            'user' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'avatar'   => $user->avatar,
                'genero'   => $user->genero,
                'telefono' => $user->telefono,
                'company'  => $user->company,
            ],
            'roles'    => $user->getRoleNames(),
            'permisos' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    /**
     * Logout
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh()
    {
        return response()->json([
            'access_token' => Auth::guard('api')->refresh(),
            'token_type'   => 'bearer',
            'expires_in'   => Auth::guard('api')->factory()->getTTL() * 60,
        ]);
    }

    /**
     * Enviar link de reseteo
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => __($status),
        ], $status === Password::RESET_LINK_SENT ? 200 : 400);
    }

    /**
     * Resetear contraseña
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return response()->json([
            'message' => __($status),
        ], $status === Password::PASSWORD_RESET ? 200 : 400);
    }
}
