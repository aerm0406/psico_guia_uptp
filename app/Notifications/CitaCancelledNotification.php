<?php

namespace App\Notifications;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CitaCancelledNotification extends Notification
{
    use Queueable;

    public $cita;
    public $cancelledBy; // 'paciente' or 'psicologo'

    public function __construct(Cita $cita, $cancelledBy)
    {
        $this->cita = $cita;
        $this->cancelledBy = $cancelledBy;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $senderName = $this->cancelledBy === 'paciente' ? $this->cita->paciente->name : $this->cita->psicologo->name;
        $url = $this->cancelledBy === 'paciente' ? route('agenda.index') : route('citas.index');
        
        return [
            'type_id' => 'cita_cancelled',
            'cita_id' => $this->cita->id,
            'sender_name' => $senderName,
            'body' => $senderName . ' ha cancelado una cita.',
            'url' => $url,
        ];
    }
}
