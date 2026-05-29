<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function obtenerUsuario($id)
    {
        return \App\Models\User::obtenerUsuarioPorId($id);
    }

    protected function obtenerPsicologosDisponibles()
    {
        return \App\Models\User::obtenerPsicologosDisponibles();
    }

    protected function instanciarUsuarioParaNotificacion($id)
    {
        return \App\Models\User::instanciarParaNotificacion($id);
    }

    protected function actualizarPerfilUsuario($userId, $updateData)
    {
        return \App\Models\User::actualizarPerfil($userId, $updateData);
    }

    protected function eliminarUsuarioPermanentemente($userId)
    {
        return \App\Models\User::eliminarUsuario($userId); // Borrado lógico
    }

    protected function obtenerTodosPacientes($buscar)
    {
        return \App\Models\User::obtenerTodosPacientes($buscar);
    }

    protected function obtenerPacientesConCitas($userId, $buscar)
    {
        return \App\Models\User::obtenerPacientesConCitas($userId, $buscar);
    }

    protected function obtenerContactosParaChat($userId, $isPsicologo)
    {
        return \App\Models\User::obtenerContactosParaChat($userId, $isPsicologo);
    }

    protected function buscarUsuarios($buscar, $role, $cantidad = 8)
    {
        return \App\Models\User::buscarUsuarios($buscar, $role, $cantidad);
    }

    protected function obtenerEstadisticasUsuarios()
    {
        return \App\Models\User::obtenerEstadisticas();
    }

    protected function generarPasswordSegura()
    {
        return \App\Models\User::generarPasswordSegura();
    }

    protected function crearUsuarioAdmin($data)
    {
        return \App\Models\User::crearUsuario($data);
    }

    protected function actualizarUsuarioAdmin($id, $data)
    {
        return \App\Models\User::actualizarUsuario($id, $data);
    }

    protected function actualizarContrasenaUsuario($id, $password)
    {
        return \App\Models\User::actualizarContrasena($id, $password);
    }

    protected function eliminarUsuarioLogico($id)
    {
        return \App\Models\User::eliminarUsuario($id);
    }

    protected function registrarNuevoUsuario($data)
    {
        return \App\Models\User::registrarUsuario($data);
    }
}
