<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

use App\Models\User;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        // TODO: hash password 
        // if ($user && $user->password === $credentials['password']) {
        //     $token = $user->createToken('authToken')->plainTextToken;
        //     return response()->json(['token' => $token], 200);
        // }

        if ($user && Hash::check($credentials['password'], $user->password)) {
            $token = $user->createToken('authToken')->plainTextToken;
            return response()->json(['token' => $token], 200);
        }


        return response()->json(['error' => 'Unauthorized'], 401);
    }
 
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Correo de recuperación enviado'], 200)
            : response()->json(['error' => 'Error al enviar el correo'], 400);
    }

    /**
     * Restablecer contraseña
     */
    public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'token' => 'required',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();
            
            $user->tokens()->delete(); // Invalida los tokens existentes
        }
    );

    return $status === Password::PASSWORD_RESET
        ? response()->json(['message' => 'Contraseña restablecida correctamente'], 200)
        : response()->json(['error' => 'Error al restablecer la contraseña'], 400);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }


}