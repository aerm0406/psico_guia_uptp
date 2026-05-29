<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Enfermedad
{
    const TIPO_MENTAL = 'mental';
    const TIPO_FISICA = 'fisica';

    public static function obtenerEnfermedades($cantidad = 8, $search = null, $categoria = null)
    {
        $query = DB::table('enfermedades')
            ->where('estatus', 1);

        if ($search) {
            $query->where('nombre', 'LIKE', '%' . $search . '%');
        }

        if ($categoria) {
            $query->where('categoria', $categoria);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($cantidad);
    }

    public static function obtenerPorId($id)
    {
        return DB::table('enfermedades')
            ->where('id', $id)
            ->where('estatus', 1)
            ->first();
    }

    public static function existeEnfermedad($nombre, $tipo, $categoria, $excludeId = null)
    {
        $query = DB::table('enfermedades')
            ->where('nombre', $nombre)
            ->where('tipo', $tipo)
            ->where('categoria', $categoria);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public static function crearEnfermedad($data)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('enfermedades')->insert([
                'nombre' => $data['nombre'],
                'tipo' => $data['tipo'] ?? null,
                'categoria' => $data['categoria'],
                'descripcion' => $data['descripcion'] ?? null,
                'estatus' => 1,
                'created_at' => Carbon::now(),
            ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function actualizarEnfermedad($id, $data)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('enfermedades')->where('id', $id)->update([
                'nombre' => $data['nombre'],
                'tipo' => $data['tipo'] ?? null,
                'categoria' => $data['categoria'],
                'descripcion' => $data['descripcion'] ?? null,
                'updated_at' => Carbon::now(),
            ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function eliminarEnfermedad($id)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('enfermedades')->where('id', $id)->update([
                'estatus' => 0,
                'updated_at' => Carbon::now(),
            ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function mental()
    {
        return DB::table('enfermedades')->where('tipo', self::TIPO_MENTAL);
    }

    public static function fisica()
    {
        return DB::table('enfermedades')->where('tipo', self::TIPO_FISICA);
    }
}
