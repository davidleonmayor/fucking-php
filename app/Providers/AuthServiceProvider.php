<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Personalizar el enlace de restablecimiento
        // ResetPassword::createUrlUsing(function ($user, string $token) {
        //     // return 'http://frontend.com/reset-password?token=' . $token . '&email=' . $user->email;
        //     return 'http://frontend.com/reset-password?token=' . $token . '&email=' . $user->email;
        // });
         // Personalizar el enlace de restablecimiento
         
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return "http://127.0.0.1:5501/inicio-de-sesion/Modulo-iniciar-sesion/mensajes/recuperarContraseña2.html?token={$token}&email=" . urlencode($user->email);
        });
    }
}
