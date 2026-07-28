<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EstadoAnimoDiario;
use Carbon\Carbon;

class EstadoAnimoDiarioController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'valor' => 'required|integer|min:1|max:10',
        ]);

        $yaRegistrado = EstadoAnimoDiario::getTodayForUser(Auth::id());

        if ($yaRegistrado) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Ya has registrado tu estado de ánimo hoy.'], 400);
            }
            return redirect()->back()->with('error', 'Ya has registrado tu estado de ánimo hoy.');
        }

        \Illuminate\Support\Facades\DB::table('estado_animo_diarios')->insert([
            'paciente_id' => Auth::id(),
            'valor' => $request->valor,
            'fecha' => Carbon::today(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => '¡Gracias por compartir cómo te sientes hoy!']);
        }

        return redirect()->back()->with('success', '¡Gracias por compartir cómo te sientes hoy!');
    }
}
