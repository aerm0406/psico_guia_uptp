<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Publicacion
{
    protected static $table = 'publicaciones';

    public static function byPsicologo($psicologoId)
    {
        return DB::table(self::$table)
            ->where('psicologo_id', $psicologoId)
            ->where('estatus', 1)
            ->orderBy('created_at', 'desc')
            ->limit(14)
            ->get();
    }

    public static function findById($id)
    {
        return DB::table(self::$table)
            ->where('id', $id)
            ->where('estatus', 1)
            ->first();
    }

    public static function forPacientes()
    {
        return DB::table(self::$table)
            ->join('users', 'publicaciones.psicologo_id', '=', 'users.id')
            ->select('publicaciones.*', 'users.nombres', 'users.apellidos', 'users.profile_photo_path')
            ->where('publicaciones.estatus', 1)
            ->orderBy('publicaciones.created_at', 'desc')
            ->limit(14)
            ->get();
    }

    public static function create(array $data)
    {
        return DB::table(self::$table)->insertGetId([
            'psicologo_id' => $data['psicologo_id'],
            'titulo' => $data['titulo'],
            'contenido' => $data['contenido'] ?? '',
            'alcance' => $data['alcance'] ?? 'todos',
            'tipo' => $data['tipo'] ?? 'texto',
            'color_fondo' => $data['color_fondo'] ?? null,
            'media_path' => $data['media_path'] ?? null,
            'estatus' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }

    public static function update($id, array $data)
    {
        $data['updated_at'] = Carbon::now();
        return DB::table(self::$table)->where('id', $id)->update($data);
    }

    public static function delete($id)
    {
        return DB::table(self::$table)->where('id', $id)->update(['estatus' => 0, 'updated_at' => Carbon::now()]);
    }
}
