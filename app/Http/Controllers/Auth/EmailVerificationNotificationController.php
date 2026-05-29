<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
        $user = $this->instanciarUsuarioParaNotificacion($userId);

        if (!$user) {
            abort(403);
        }

        if ($user->email_verified_at !== null) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $user->notify(new \Illuminate\Auth\Notifications\VerifyEmail());

        return back()->with('status', 'verification-link-sent');
    }
}
