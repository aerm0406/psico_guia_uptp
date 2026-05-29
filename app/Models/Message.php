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
}
