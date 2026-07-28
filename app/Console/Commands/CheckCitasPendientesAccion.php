<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckCitasPendientesAccion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-citas-pendientes-accion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Buscar todas las citas confirmadas cuya fecha sea de hace exactamente 7 días
        $fechaHace7Dias = now()->subDays(7)->toDateString();

        $citas = \Illuminate\Support\Facades\DB::table('citas')
            ->where('estado', 'confirmada')
            ->where('fecha', '<=', $fechaHace7Dias)
            ->get();

        foreach ($citas as $cita) {
            // Instanciar para notificación (desencripta y obtiene modelo básico)
            $citaModel = \App\Models\Cita::instanciarParaNotificacion($cita->id);
            
            if ($citaModel && $citaModel->psicologo_id) {
                // Notificar al psicólogo
                \App\Models\Cita::notificarUsuario($citaModel->psicologo_id, new \App\Notifications\CitaSinProcesarNotification($citaModel));
            }
        }
        
        $this->info('Revisión de citas sin procesar completada. Se encontraron ' . $citas->count() . ' citas pendientes de acción de hace 7 días.');
    }
}
