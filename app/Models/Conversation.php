<?php

namespace App\Models;

class Conversation
{
    public static function obtenerUsuarioUno($userOneId)
    {
        return \Illuminate\Support\Facades\DB::table('users')->where('id', $userOneId)->first();
    }

    public static function obtenerUsuarioDos($userTwoId)
    {
        return \Illuminate\Support\Facades\DB::table('users')->where('id', $userTwoId)->first();
    }

    public static function obtenerMensajes($conversationId)
    {
        return \Illuminate\Support\Facades\DB::table('messages')
            ->where('conversation_id', $conversationId)
            ->get();
    }

    public static function obtenerConversacion($userId, $targetUserId)
    {
        return \Illuminate\Support\Facades\DB::table('conversations')
            ->where(function($query) use ($userId, $targetUserId) {
                $query->where('user_one_id', $userId)->where('user_two_id', $targetUserId);
            })->orWhere(function($query) use ($userId, $targetUserId) {
                $query->where('user_one_id', $targetUserId)->where('user_two_id', $userId);
            })->first();
    }

    public static function obtenerOUCrearConversacion($userId, $targetUserId)
    {
        $conv = self::obtenerConversacion($userId, $targetUserId);
        if (!$conv) {
            try {
                \Illuminate\Support\Facades\DB::beginTransaction();
                $id = \Illuminate\Support\Facades\DB::table('conversations')->insertGetId([
                    'user_one_id' => $userId,
                    'user_two_id' => $targetUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                \Illuminate\Support\Facades\DB::commit();
                $conv = self::obtenerConversacion($userId, $targetUserId);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                throw $e;
            }
        }
        return $conv;
    }
}
