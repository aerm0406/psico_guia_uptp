<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CitaConfirmada extends Mailable
{
    use Queueable, SerializesModels;

    public Cita $cita;

    /**
     * Create a new message instance.
     */
    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Confirmación de cita')
            ->view('emails.cita_confirmada')
            ->with([
                'paciente' => optional($this->cita->paciente)->name ?: 'Paciente',
                'psicologo' => optional($this->cita->psicologo)->name ?: 'Tu psicólogo',
                'fecha' => $this->cita->fecha ? $this->cita->fecha->translatedFormat('d \d\e F, Y') : now()->translatedFormat('d \d\e F, Y'),
                'hora' => $this->cita->hora ?: 'Pendiente',
                'bloque' => $this->cita->bloque_propuesto ?: ($this->cita->bloques_sugeridos ?: 'No definido'),
            ]);
    }
}
