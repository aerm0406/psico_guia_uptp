<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'cedula' => ['required', 'string', 'max:20', Rule::unique(User::class)->ignore($this->user()->id)],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'genero' => ['required', 'string', 'in:Masculino,Femenino'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'telefono' => ['required', 'string', 'max:50'],
            'ubicacion' => ['required', 'string', 'max:255'],
            'discapacidad' => ['required', 'string', 'in:Si,No'],
            'tipo_discapacidad' => ['nullable', 'string', 'max:100', 'required_if:discapacidad,Si'],
            'tiene_hijos' => ['required', 'string', 'in:Si,No'],
            'numero_hijos' => ['nullable', 'integer', 'min:1', 'max:50', 'required_if:tiene_hijos,Si'],
            'estado_civil' => ['required', 'string', 'in:Soltero(a),Casado(a),Divorciado(a),Viudo(a)'],
            'perfil_academico' => ['required', 'string', 'in:Estudiante,Profesor,Obrero,Administrativo'],
            'pnf' => ['nullable', 'string', 'in:Informatica,Agroalimentaria,Mecanica,Administracion,Electrica', 'required_if:perfil_academico,Estudiante'],
            'semestre' => ['nullable', 'integer', 'min:1', 'max:12', 'required_if:perfil_academico,Estudiante'],
            'horario_file' => ['nullable', 'file', 'mimes:pdf,jpg,png,jpeg', 'max:4096'],
        ];
    }
}
