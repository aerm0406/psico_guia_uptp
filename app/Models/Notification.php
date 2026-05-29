<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Notification
{
    /**
     * Obtiene una notificación específica por su ID y el ID del usuario.
     */
    public static function obtenerPorIdYUsuario($id, $userId)
    {
        return DB::table('notifications')
            ->where('id', $id)
            ->where('notifiable_id', $userId)
            ->first();
    }

    /**
     * Marca una notificación específica como leída.
     */
    public static function marcarComoLeida($id)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('notifications')
                ->where('id', $id)
                ->update(['read_at' => now(), 'updated_at' => now()]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Marca todas las notificaciones no leídas de un usuario como leídas.
     */
    public static function marcarTodasComoLeidas($userId)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('notifications')
                ->where('notifiable_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now(), 'updated_at' => now()]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Limpia las notificaciones de mensajes nuevos flotantes enviadas por un usuario específico.
     */
    public static function limpiarNotificacionesMensajes($userId, $targetUserId)
    {
        try {
            DB::beginTransaction();
            $notifications = DB::table('notifications')
                ->where('notifiable_id', $userId)
                ->where('type', 'App\Notifications\NewMessageNotification')
                ->whereNull('read_at')
                ->get();

            foreach ($notifications as $notification) {
                $data = json_decode($notification->data, true);
                if (($data['sender_id'] ?? null) == $targetUserId) {
                    DB::table('notifications')
                        ->where('id', $notification->id)
                        ->update(['read_at' => now(), 'updated_at' => now()]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
