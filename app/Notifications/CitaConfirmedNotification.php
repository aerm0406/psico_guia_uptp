<?php

namespace App\Notifications;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CitaConfirmedNotification extends Notification
{
    use Queueable;

    public $cita;

    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type_id' => 'cita_confirmed',
            'cita_id' => $this->cita->id,
            'psicologo_name' => $this->cita->psicologo->name,
            'body' => 'Tienes una nueva cita confirmada con ' . $this->cita->psicologo->name . '.',
            'url' => route('citas.index'), // Paciente views confirmed in 'citas.index'
        ];
    }
}
