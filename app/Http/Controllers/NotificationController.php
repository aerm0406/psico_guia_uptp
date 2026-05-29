<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAsRead($id)
    {
        $userId = Auth::id();
        $notification = Notification::obtenerPorIdYUsuario($id, $userId);
            
        abort_if(!$notification, 404);
        
        Notification::marcarComoLeida($id);

        // Redirect to the URL attached to the notification, or dashboard if none
        $data = json_decode($notification->data, true);
        $url = $data['url'] ?? route('dashboard');
        
        return redirect($url);
    }

    public function markAllAsRead(Request $request)
    {
        $userId = Auth::id();
        Notification::marcarTodasComoLeidas($userId);

        return response()->json(['success' => true]);
    }
}
