<?php

namespace App\Models;

class Message
{
    public static function obtenerUltimoMensaje($conversationId)
    {
        return \Illuminate\Support\Facades\DB::table('messages')
            ->where('conversation_id', $conversationId)
            ->latest('created_at')
            ->first();
    }

    public static function marcarLeidos($conversationId, $senderId)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            $res = \Illuminate\Support\Facades\DB::table('messages')
                ->where('conversation_id', $conversationId)
                ->where('sender_id', $senderId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            \Illuminate\Support\Facades\DB::commit();
            return $res;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            throw $e;
        }
    }

    public static function crearMensaje($conversationId, $senderId, $body)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            $id = \Illuminate\Support\Facades\DB::table('messages')->insertGetId([
                'conversation_id' => $conversationId,
                'sender_id' => $senderId,
                'body' => $body,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $msg = \Illuminate\Support\Facades\DB::table('messages')->where('id', $id)->first();
            \Illuminate\Support\Facades\DB::commit();
            return $msg;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            throw $e;
        }
    }

    public static function obtenerConversacion($conversationId)
    {
        return \Illuminate\Support\Facades\DB::table('conversations')->where('id', $conversationId)->first();
    }

    public static function obtenerRemitente($senderId)
    {
        return \Illuminate\Support\Facades\DB::table('users')->where('id', $senderId)->first();
    }

    public static function registrarActividadChat($userId, $chatActivoId)
    {
        return \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->update([
            'ultima_actividad_chat' => now(),
            'chat_activo_user_id' => $chatActivoId,
        ]);
    }

    public static function usuarioEstaActivoEnChat($userId, $senderId)
    {
        $user = \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->first();
        if (!$user || !$user->ultima_actividad_chat || !$user->chat_activo_user_id) {
            return false;
        }

        // Si su última actividad fue hace menos de 1 minuto y está en el chat del remitente
        if ($user->chat_activo_user_id == $senderId) {
            $actividad = \Carbon\Carbon::parse($user->ultima_actividad_chat);
            if (now()->diffInSeconds($actividad) <= 60) {
                return true;
            }
        }
        return false;
    }

    public static function programarNotificacionCorreo($senderId, $receiverId)
    {
        $pendiente = \Illuminate\Support\Facades\DB::table('chat_notificaciones_pendientes')
            ->where('sender_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->where('estado', 'pendiente')
            ->first();

        if ($pendiente) {
            \Illuminate\Support\Facades\DB::table('chat_notificaciones_pendientes')
                ->where('id', $pendiente->id)
                ->update([
                    'cantidad_mensajes' => $pendiente->cantidad_mensajes + 1,
                    'updated_at' => now(),
                ]);
        } else {
            \Illuminate\Support\Facades\DB::table('chat_notificaciones_pendientes')->insert([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'cantidad_mensajes' => 1,
                'programado_para' => now()->addMinutes(2), // 2 minutos de espera
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public static function cancelarNotificacionesPendientes($receiverId, $senderId)
    {
        return \Illuminate\Support\Facades\DB::table('chat_notificaciones_pendientes')
            ->where('receiver_id', $receiverId)
            ->where('sender_id', $senderId)
            ->where('estado', 'pendiente')
            ->update([
                'estado' => 'cancelada',
                'updated_at' => now(),
            ]);
    }

    public static function obtenerNotificacionesParaEnviar()
    {
        return \Illuminate\Support\Facades\DB::table('chat_notificaciones_pendientes')
            ->whereIn('estado', ['pendiente', 'error'])
            ->where('programado_para', '<=', now())
            ->get();
    }

    public static function actualizarEstadoNotificacion($id, $estado)
    {
        return \Illuminate\Support\Facades\DB::table('chat_notificaciones_pendientes')
            ->where('id', $id)
            ->update([
                'estado' => $estado,
                'updated_at' => now(),
            ]);
    }
}
