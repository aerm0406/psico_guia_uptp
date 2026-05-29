<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(\Illuminate\Http\Request $request): RedirectResponse
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
        $user = \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->first();

        if (!$user) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        if ($user->email_verified_at !== null) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $userId)
            ->update([
                'email_verified_at' => now(),
                'updated_at' => now(),
            ]);

        $notifiable = $this->instanciarUsuarioParaNotificacion($userId);
        if ($notifiable) {
            event(new Verified($notifiable));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
