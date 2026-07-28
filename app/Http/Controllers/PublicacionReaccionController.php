<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PublicacionReaccionController extends Controller
{
    public function toggle(Request $request, $publicacionId)
    {
        $userId = auth()->id();
        
        // Verificar si la publicación existe
        $publicacion = DB::table('publicaciones')->where('id', $publicacionId)->first();
        if (!$publicacion) {
            return response()->json(['error' => 'Publicación no encontrada'], 404);
        }

        // Verificar si ya reaccionó
        $reaccion = DB::table('publicacion_reacciones')
            ->where('publicacion_id', $publicacionId)
            ->where('paciente_id', $userId)
            ->first();

        if ($reaccion) {
            // Si ya reaccionó, quitar el me gusta
            DB::table('publicacion_reacciones')->where('id', $reaccion->id)->delete();
            $status = 'removed';
        } else {
            // Si no ha reaccionado, agregar el me gusta
            DB::table('publicacion_reacciones')->insert([
                'publicacion_id' => $publicacionId,
                'paciente_id' => $userId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            $status = 'added';
        }

        // --- Manejo de Notificación al Psicólogo ---
        
        // Obtener el conteo total de likes
        $totalLikes = DB::table('publicacion_reacciones')
            ->where('publicacion_id', $publicacionId)
            ->count();

        // Borrar notificación anterior para esta publicación (si existe)
        // Buscamos notificaciones del tipo 'reaccion_publicacion' que pertenezcan a esta publicacion
        $notificacionesPrevias = DB::table('notifications')
            ->where('notifiable_id', $publicacion->psicologo_id)
            ->where('type', 'App\\Notifications\\ReaccionPublicacionNotification')
            ->get();
            
        foreach ($notificacionesPrevias as $notif) {
            $data = json_decode($notif->data, true);
            if (isset($data['publicacion_id']) && $data['publicacion_id'] == $publicacionId) {
                DB::table('notifications')->where('id', $notif->id)->delete();
            }
        }

        // Si aún hay likes, crear la nueva notificación actualizada
        if ($totalLikes > 0) {
            // Obtener el último en dar like
            $ultimaReaccion = DB::table('publicacion_reacciones')
                ->where('publicacion_id', $publicacionId)
                ->orderBy('created_at', 'desc')
                ->first();
                
            $ultimoUsuario = DB::table('users')->where('id', $ultimaReaccion->paciente_id)->first();
            $nombreUltimo = $ultimoUsuario->nombres;

            if ($totalLikes == 1) {
                $mensaje = "{$nombreUltimo} le dio me gusta a tu aviso.";
            } else {
                $otros = $totalLikes - 1;
                $mensaje = "{$nombreUltimo} y {$otros} personas más le dieron me gusta a tu aviso.";
            }

            DB::table('notifications')->insert([
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\ReaccionPublicacionNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $publicacion->psicologo_id,
                'data' => json_encode([
                    'type_id' => 'reaccion_aviso',
                    'body' => $mensaje,
                    'url' => route('publicaciones.index'),
                    'publicacion_id' => $publicacionId
                ]),
                'read_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        return response()->json([
            'status' => $status,
            'total_likes' => $totalLikes
        ]);
    }
}
