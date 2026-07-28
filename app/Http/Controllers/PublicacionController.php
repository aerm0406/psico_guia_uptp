<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Publicacion;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PublicacionController extends Controller
{
    public function index()
    {
        $publicaciones = Publicacion::byPsicologo(Auth::id());
        return view('publicaciones.index', compact('publicaciones'));
    }

    public function mural()
    {
        $publicaciones = Publicacion::forPacientes();
        return view('publicaciones.mural', compact('publicaciones'));
    }

    public function create()
    {
        return view('publicaciones.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'nullable|string',
            'alcance' => 'required|in:todos,mis_pacientes',
            'tipo' => 'required|in:texto,color,imagen',
            'color_fondo' => 'nullable|string',
            'imagen' => 'nullable|image|max:2048' // max 2MB
        ]);

        $mediaPath = null;
        if ($request->tipo === 'imagen' && $request->hasFile('imagen')) {
            $mediaPath = $request->file('imagen')->store('publicaciones', 'public');
        }

        $publicacionId = Publicacion::create([
            'psicologo_id' => Auth::id(),
            'titulo' => $request->titulo,
            'contenido' => $request->contenido,
            'alcance' => $request->alcance,
            'tipo' => $request->tipo,
            'color_fondo' => $request->tipo === 'color' ? $request->color_fondo : null,
            'media_path' => $mediaPath
        ]);

        // --- Manejo de Notificaciones a los pacientes ---
        $psicologo = Auth::user();
        $mensaje = "El psicólogo {$psicologo->nombres} ha publicado un nuevo aviso: {$request->titulo}";
        
        $pacientes = DB::table('users')->where('role', 'paciente');
        // Si fuera 'mis_pacientes', aquí podríamos filtrar por citas. 
        // Para asegurar que llegue a sus pacientes, por ahora notificamos a todos los pacientes (mural público).
        $pacientes = $pacientes->get();

        $notificaciones = [];
        foreach ($pacientes as $paciente) {
            $notificaciones[] = [
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\NuevoAvisoNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $paciente->id,
                'data' => json_encode([
                    'type_id' => 'nuevo_aviso',
                    'body' => $mensaje,
                    'url' => route('mural.index')
                ]),
                'read_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        if (count($notificaciones) > 0) {
            DB::table('notifications')->insert($notificaciones);
        }

        return redirect()->route('publicaciones.index')->with('success', 'Publicación creada exitosamente.');
    }

    public function edit($id)
    {
        $publicacion = Publicacion::findById($id);
        if (!$publicacion || $publicacion->psicologo_id != Auth::id()) {
            return redirect()->route('publicaciones.index')->with('error', 'Acceso denegado.');
        }

        return view('publicaciones.edit', compact('publicacion'));
    }

    public function update(Request $request, $id)
    {
        $publicacion = Publicacion::findById($id);
        if (!$publicacion || $publicacion->psicologo_id != Auth::id()) {
            return redirect()->route('publicaciones.index')->with('error', 'Acceso denegado.');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'nullable|string',
            'alcance' => 'required|in:todos,mis_pacientes',
        ]);

        Publicacion::update($id, [
            'titulo' => $request->titulo,
            'contenido' => $request->contenido ?? '',
            'alcance' => $request->alcance,
        ]);

        return redirect()->route('publicaciones.index')->with('success', 'Publicación actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $publicacion = Publicacion::findById($id);
        if (!$publicacion || $publicacion->psicologo_id != Auth::id()) {
            return redirect()->route('publicaciones.index')->with('error', 'Acceso denegado.');
        }

        Publicacion::delete($id);

        return redirect()->route('publicaciones.index')->with('success', 'Publicación eliminada.');
    }
}
