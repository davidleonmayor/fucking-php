<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    //
    public function forgotPassword(Request $request)
    {
        // Validar que el correo sea válido y exista en la base de datos
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Enviar el enlace de restablecimiento
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Responder según el resultado
        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Correo de recuperación enviado'], 200)
            : response()->json(['error' => 'Error al enviar el correo'], 400);
    }

    public function resetPassword(Request $request)
    {
        // Validar los datos enviados
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Intentar restablecer la contraseña
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        // Responder según el resultado
        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Contraseña restablecida correctamente'], 200)
            : response()->json(['error' => 'Error al restablecer la contraseña'], 400);
    }
}
