<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();

        if ($user->profile_completed) {
            return [
                'profile_photo' => ['nullable', 'image', 'max:2048'],
                'horario_file' => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:4096'],
                'semestre' => [Rule::requiredIf($user->role === 'paciente' && $user->perfil_academico === 'Estudiante'), 'integer', 'min:1', 'max:12', 'nullable'],
            ];
        }

        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'cedula' => ['required', 'string', 'max:20', Rule::unique('users', 'cedula')->ignore($user->id)],
            'genero' => ['required', 'string', 'in:Masculino,Femenino'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'telefono' => ['required', 'string', 'max:50'],
            'ubicacion' => ['required', 'string', 'max:255'],
            'estado_civil' => ['required', 'string', 'in:Soltero(a),Casado(a),Divorciado(a),Viudo(a)'],
            'discapacidad' => ['required', 'string', 'in:Si,No'],
            'tipo_discapacidad' => ['nullable', 'string', 'max:255', 'required_if:discapacidad,Si'],
            'tiene_hijos' => ['required', 'string', 'in:Si,No'],
            'numero_hijos' => ['nullable', 'integer', 'min:0', 'max:50', 'required_if:tiene_hijos,Si'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'perfil_academico' => [Rule::requiredIf($user->role === 'paciente'), 'string', 'in:Estudiante,Profesor,Obrero,Administrativo,Pre-escolar,Otros', 'nullable'],
            'pnf' => [Rule::requiredIf($user->role === 'paciente' && $this->input('perfil_academico') === 'Estudiante'), 'string', 'max:255', 'nullable'],
            'semestre' => [Rule::requiredIf($user->role === 'paciente' && $this->input('perfil_academico') === 'Estudiante'), 'integer', 'min:1', 'max:12', 'nullable'],
            'horario_file' => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:4096'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombres.required' => 'El campo nombres es obligatorio.',
            'apellidos.required' => 'El campo apellidos es obligatorio.',
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.unique' => 'Esta cédula ya está registrada.',
            'genero.required' => 'El género es obligatorio.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'ubicacion.required' => 'La ubicación es obligatoria.',
            'estado_civil.required' => 'El estado civil es obligatorio.',
            'discapacidad.required' => 'Debe indicar si tiene o no discapacidad.',
            'tipo_discapacidad.required_if' => 'Debe especificar el tipo de discapacidad.',
            'tiene_hijos.required' => 'Debe indicar si tiene o no hijos.',
            'numero_hijos.required_if' => 'Debe especificar el número de hijos.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya está registrado.',
            'profile_photo.image' => 'El archivo debe ser una imagen.',
            'profile_photo.max' => 'La imagen de perfil no debe superar los 2MB.',
            'perfil_academico.required_if' => 'El perfil académico es obligatorio para pacientes.',
            'pnf.required_if' => 'El PNF es obligatorio para estudiantes.',
            'semestre.required_if' => 'El semestre es obligatorio para estudiantes.',
            'horario_file.mimes' => 'El horario debe ser un archivo PDF, JPG o PNG.',
            'horario_file.max' => 'El archivo de horario no debe superar los 4MB.',
        ];
    }
}
