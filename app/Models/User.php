<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class User
{
    const ROLE_PSICOLOGO = 'psicologo';
    const ROLE_PACIENTE = 'paciente';
    const ROLE_ADMIN = 'admin';

    // Propiedades principales de la tabla
    public $id;
    public $email;
    public $role;
    public $cedula;
    public $nombres;
    public $apellidos;
    public $profile_photo_path;
    public $status;

    // Propiedades calculadas dinámicamente
    public $name;
    public $profile_photo_url;
    public $avatar;
    public $primera_cita;

    /**
     * Constructor para mapear datos cuando se instancia un objeto User individual.
     */
    public function __construct($properties = null)
    {
        if ($properties) {
            foreach ((array) $properties as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Genera una contraseña segura.
     */
    public static function generarPasswordSegura()
    {
        $length = rand(10, 14);
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '@$!%*?&';

        $password = $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $special[rand(0, strlen($special) - 1)];

        $all = $uppercase . $lowercase . $numbers . $special;
        for ($i = 0; $i < $length - 4; $i++) {
            $password .= $all[rand(0, strlen($all) - 1)];
        }

        return str_shuffle($password);
    }

    public function getNameAttribute()
    {
        return trim(($this->nombres ?? '') . ' ' . ($this->apellidos ?? ''));
    }

    public function getShortNameAttribute()
    {
        $firstName = explode(' ', trim($this->nombres ?? ''))[0];
        $firstLastName = explode(' ', trim($this->apellidos ?? ''))[0];
        return trim($firstName . ' ' . $firstLastName);
    }

    public function getProfilePhotoUrlAttribute()
    {
        return !empty($this->profile_photo_path) ? Storage::disk('public')->url($this->profile_photo_path) : null;
    }

    public function getAvatarAttribute()
    {
        if (!empty($this->profile_photo_path)) {
            return Storage::disk('public')->url($this->profile_photo_path);
        }
        $initials = strtoupper(substr($this->nombres ?? '', 0, 1) . substr($this->apellidos ?? '', 0, 1));
        return $initials ?: 'PR';
    }

    public function isPsicologo(): bool
    {
        return $this->role === self::ROLE_PSICOLOGO;
    }

    public function isPaciente(): bool
    {
        return $this->role === self::ROLE_PACIENTE;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public static function buscarUsuarios($buscar = null, $role = null, $cantidad = 8)
    {
        // Construir nombre seguro evitando NULLs y espacio sobrante
        $query = DB::table('users')
            ->select('users.*', DB::raw("TRIM(CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, ''))) as name"))
            ->where('status', 1);

        // Normalizar y validar el parámetro role para hacerlo tolerante a mayúsculas/espacios
        $allowedRoles = [self::ROLE_ADMIN, self::ROLE_PACIENTE, self::ROLE_PSICOLOGO];
        $roleNormalized = null;
        if (!is_null($role)) {
            if (is_array($role)) {
                $roleNormalized = strtolower(trim($role[0] ?? ''));
            } else {
                $roleNormalized = strtolower(trim((string) $role));
            }
        }

        if ($roleNormalized && in_array($roleNormalized, $allowedRoles, true)) {
            $query->whereRaw("LOWER(TRIM(COALESCE(role, ''))) = ?", [$roleNormalized]);
        }

        // Búsqueda insensible a mayúsculas/minúsculas y segura frente a NULLs
        if ($buscar) {
            $buscarNormalized = mb_strtolower($buscar, 'UTF-8');
            $query->where(function ($q) use ($buscarNormalized) {
                $q->whereRaw("LOWER(COALESCE(nombres, '')) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(COALESCE(apellidos, '')) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(TRIM(CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, '')))) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(COALESCE(email, '')) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(COALESCE(cedula, '')) LIKE ?", ["{$buscarNormalized}%"]);
            });
        }

        return $query->orderBy('id', 'desc')->paginate($cantidad);
    }

    public static function obtenerPacientesSinCita($busqueda = '')
    {
        $citasActivasIds = DB::table('citas')
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->pluck('user_id');

        $query = DB::table('users')
            ->select('users.*', DB::raw("TRIM(CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, ''))) as name"))
            ->where('role', self::ROLE_PACIENTE)
            ->where('status', 1)
            ->whereNotIn('id', $citasActivasIds);

        if ($busqueda) {
            $buscarNormalized = mb_strtolower($busqueda, 'UTF-8');
            $query->where(function ($q) use ($buscarNormalized) {
                $q->whereRaw("LOWER(COALESCE(nombres, '')) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(COALESCE(apellidos, '')) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(TRIM(CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, '')))) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(COALESCE(email, '')) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(COALESCE(cedula, '')) LIKE ?", ["{$buscarNormalized}%"]);
            });
        }

        $usuariosRaw = $query->orderBy('nombres', 'asc')->limit(20)->get();

        return $usuariosRaw->map(function ($u) {
            return new self($u);
        });
    }

    public static function obtenerUsuarioPorId($id)
    {
        $userRaw = DB::table('users')
            ->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
            ->where('id', $id)
            ->where('status', 1)
            ->first();

        if ($userRaw) {
            $user = new self($userRaw);

            if (!isset($user->profile_photo_path)) {
                $user->profile_photo_path = null;
            }

            $user->profile_photo_url = !empty($user->profile_photo_path) ? Storage::disk('public')->url($user->profile_photo_path) : null;
            $initials = strtoupper(substr($user->nombres ?? '', 0, 1) . substr($user->apellidos ?? '', 0, 1));
            $user->avatar = $user->profile_photo_url ?: ($initials ?: 'PR');

            $user->primera_cita = DB::table('citas')
                ->where('user_id', $id)
                ->whereNotNull('fecha')
                ->orderBy('fecha', 'asc')
                ->value('fecha');

            return $user;
        }

        return null;
    }

    public static function instanciarParaNotificacion($id)
    {
        $data = DB::table('users')->where('id', $id)->first();
        if (!$data) return null;
        $notifiable = new \App\Models\NotifiableUser();

        foreach ((array) $data as $key => $value) {
            $notifiable->{$key} = $value;
        }
        $notifiable->name = trim(($data->nombres ?? '') . ' ' . ($data->apellidos ?? ''));

        return $notifiable;
    }

    public static function crearUsuario($data)
    {
        try {
            DB::beginTransaction();
            $id = DB::table('users')->insertGetId([
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'cedula' => $data['cedula'] ?? null,
                'nombres' => $data['nombres'] ?? null,
                'apellidos' => $data['apellidos'] ?? null,
                'profile_completed' => false,
                'must_change_password' => true,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => null,
            ]);
            DB::commit();
            return $id;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function registrarUsuario($data)
    {
        try {
            DB::beginTransaction();
            $id = DB::table('users')->insertGetId([
                'nombres' => $data['nombres'] ?? null,
                'apellidos' => $data['apellidos'] ?? null,
                'cedula' => $data['cedula'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'aprobado' => $data['role'] === self::ROLE_PSICOLOGO ? 0 : 1,
                'email' => $data['email'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::commit();
            return $id;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function actualizarUsuario($id, $data)
    {
        try {
            DB::beginTransaction();
            $updateData = [
                'email' => $data['email'] ?? null,
                'role' => $data['role'],
                'cedula' => $data['cedula'] ?? null,
                'nombres' => $data['nombres'] ?? null,
                'apellidos' => $data['apellidos'] ?? null,
                'genero' => $data['genero'] ?? null,
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'estado_civil' => $data['estado_civil'] ?? null,
                'ubicacion' => $data['ubicacion'] ?? null,
                'discapacidad' => $data['discapacidad'] ?? 'No',
                'tipo_discapacidad' => $data['tipo_discapacidad'] ?? null,
                'tiene_hijos' => $data['tiene_hijos'] ?? 'No',
                'numero_hijos' => $data['numero_hijos'] ?? null,
                'perfil_academico' => $data['perfil_academico'] ?? null,
                'pnf' => $data['pnf'] ?? null,
                'semestre' => $data['semestre'] ?? null,
                'updated_at' => now(),
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $res = DB::table('users')->where('id', $id)->update($updateData);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function actualizarContrasena($id, $newPassword)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('users')->where('id', $id)->update([
                'password' => Hash::make($newPassword),
                'updated_at' => now(),
            ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function eliminarUsuario($id)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('users')->where('id', $id)->update([
                'status' => 0,
                'updated_at' => now(),
            ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function actualizarPerfil($id, $data)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('users')->where('id', $id)->update($data);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function obtenerPsicologos()
    {
        return DB::table('users')
            ->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
            ->where('role', self::ROLE_PSICOLOGO)
            ->where('status', 1)
            ->get();
    }

    public static function obtenerContactosParaChat($userId, $isPsicologo)
    {
        if ($isPsicologo) {
            $pacientesIds = DB::table('citas')->where('psicologo_id', $userId)->pluck('user_id')->unique();
            return DB::table('users')->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
                ->whereIn('id', $pacientesIds)->get();
        } else {
            $psicologosIds = DB::table('citas')->where('user_id', $userId)->pluck('psicologo_id')->unique();
            return DB::table('users')->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
                ->whereIn('id', $psicologosIds)->get()
                ->map(function ($psicologo) {
                    $firstName = explode(' ', trim($psicologo->nombres ?? ''))[0] ?? '';
                    $firstLastName = explode(' ', trim($psicologo->apellidos ?? ''))[0] ?? '';
                    $shortName = trim($firstName . ' ' . $firstLastName);
                    $psicologo->name = $shortName ?: $psicologo->name;
                    return $psicologo;
                });
        }
    }

    public static function obtenerEstadisticas()
    {
        return [
            'total' => DB::table('users')->where('status', 1)->count(),
            'pacientes' => DB::table('users')->where('status', 1)->where('role', self::ROLE_PACIENTE)->count(),
            'psicologos' => DB::table('users')->where('status', 1)->where('role', self::ROLE_PSICOLOGO)->count(),
            'admins' => DB::table('users')->where('status', 1)->where('role', self::ROLE_ADMIN)->count(),
        ];
    }

    public static function obtenerPacientesConCitas($psicologoId, $buscar = null, $cantidad = 5)
    {
        $query = DB::table('users')->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
            ->selectRaw('(SELECT MIN(fecha) FROM citas WHERE user_id = users.id AND psicologo_id = ? AND estado = "realizada") as primera_cita', [$psicologoId])
            ->whereExists(function ($q) use ($psicologoId) {
                $q->select(DB::raw(1))->from('citas')->whereColumn('citas.user_id', 'users.id')
                    ->where('citas.psicologo_id', $psicologoId)->where('citas.estado', 'realizada');
            });

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")->orWhere('apellidos', 'like', "%{$buscar}%")->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        return $query->orderBy('nombres')->orderBy('apellidos')->paginate($cantidad);
    }

    public static function obtenerTodosPacientes($buscar = null, $cantidad = 10)
    {
        $query = DB::table('users')->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
            ->selectRaw('(SELECT MIN(fecha) FROM citas WHERE user_id = users.id AND estado = "realizada") as primera_cita')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->from('citas')->whereColumn('citas.user_id', 'users.id')->where('citas.estado', '!=', 'cancelada');
            });

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")->orWhere('apellidos', 'like', "%{$buscar}%")->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        return $query->orderBy('nombres')->orderBy('apellidos')->paginate($cantidad);
    }

    public function getConversationsAttribute()
    {
        return DB::table('conversations')->where('user_one_id', $this->id)->orWhere('user_two_id', $this->id)->get();
    }

    public function getSentMessagesAttribute()
    {
        return DB::table('messages')->where('sender_id', $this->id)->get();
    }

    public function getGruposHorariosAttribute()
    {
        return DB::table('grupos_horarios')->where('user_id', $this->id)->get();
    }

    public function getHorariosAttribute()
    {
        return DB::table('horarios')->where('user_id', $this->id)->get();
    }

    public function getHistoriaClinicaAttribute()
    {
        return DB::table('historia_clinicas')->where('user_id', $this->id)->first();
    }

    public function unreadMessagesCount()
    {
        $conversationIds = DB::table('conversations')->where('user_one_id', $this->id)->orWhere('user_two_id', $this->id)->pluck('id');
        return DB::table('messages')->whereIn('conversation_id', $conversationIds)->where('sender_id', '!=', $this->id)->whereNull('read_at')->count();
    }

    public static function contarMensajesNoLeidos($userId)
    {
        $conversationIds = DB::table('conversations')->where('user_one_id', $userId)->orWhere('user_two_id', $userId)->pluck('id');
        return DB::table('messages')->whereIn('conversation_id', $conversationIds)->where('sender_id', '!=', $userId)->whereNull('read_at')->count();
    }

    public static function obtenerPsicologosDisponibles()
    {
        $psicologos = DB::table('users')
            ->join('grupos_horarios', 'users.id', '=', 'grupos_horarios.user_id')
            ->select('users.*', DB::raw("CONCAT(users.nombres, ' ', users.apellidos) as name"))
            ->where('users.role', self::ROLE_PSICOLOGO)
            ->where('users.status', 1)
            ->where('grupos_horarios.activo', 1)
            ->distinct()->get();

        $diasMapSort = ['Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Miercoles' => 3, 'Jueves' => 4, 'Viernes' => 5];

        foreach ($psicologos as $psicologo) {
            $target = (object) $psicologo;
            $target->gruposHorarios = DB::table('grupos_horarios')->where('user_id', $target->id)->where('activo', 1)->get();

            $slots = [];
            $diasLaborables = [];

            foreach ($target->gruposHorarios as $grupo) {
                $g = (object) $grupo;
                $horarios = DB::table('horarios')->where('grupo_horario_id', $g->id)->whereIn('activo', [1, 2])->get();
                $g->horarios = $horarios;

                $horariosSorted = $horarios->sortBy(function ($h) use ($diasMapSort) {
                    return ($diasMapSort[$h->dia] ?? 9) . '-' . $h->hora_inicio;
                });

                foreach ($horariosSorted as $h) {
                    $diaName = $h->dia === 'Miercoles' ? 'Miércoles' : $h->dia;
                    if (!in_array($diaName, $diasLaborables)) {
                        $diasLaborables[] = $diaName;
                    }
                    $inicio = \Carbon\Carbon::parse($h->hora_inicio)->format('g:i A');
                    $fin = \Carbon\Carbon::parse($h->hora_fin)->format('g:i A');
                    $blockStr = $diaName . ': ' . $inicio . ' - ' . $fin;
                    if (!in_array($blockStr, $slots)) {
                        $slots[] = $blockStr;
                    }
                }
            }

            $target->dias_laborables = $diasLaborables;
            $target->slots = $slots;
        }

        return $psicologos;
    }

    public static function contarUsuarios($role = null)
    {
        $query = DB::table('users')->where('status', 1);
        if (!is_null($role) && $role !== '') {
            $roleNormalized = strtolower(trim((string) $role));
            $allowedRoles = [self::ROLE_ADMIN, self::ROLE_PACIENTE, self::ROLE_PSICOLOGO];
            if (in_array($roleNormalized, $allowedRoles, true)) {
                $query->whereRaw("LOWER(TRIM(COALESCE(role, ''))) = ?", [$roleNormalized]);
            }
        }
        return $query->count();
    }

    /**
     * Obtiene los pacientes de un psicólogo para el chat
     */
    public static function obtenerPacientesDePsicologo($psicologoId)
    {
        $pacientesIds = DB::table('citas')
            ->where('psicologo_id', $psicologoId)
            ->pluck('user_id')
            ->unique();

        if ($pacientesIds->isEmpty()) {
            return collect();
        }

        return DB::table('users')
            ->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
            ->whereIn('id', $pacientesIds)
            ->where('status', 1)
            ->get();
    }

    /**
     * Obtiene los psicólogos de un paciente para el chat
     */
    public static function obtenerPsicologosDePaciente($pacienteId)
    {
        $psicologosIds = DB::table('citas')
            ->where('user_id', $pacienteId)
            ->pluck('psicologo_id')
            ->unique();

        if ($psicologosIds->isEmpty()) {
            return collect();
        }

        return DB::table('users')
            ->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
            ->whereIn('id', $psicologosIds)
            ->where('status', 1)
            ->get();
    }

    public static function aprobarPsicologo($id)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('users')->where('id', $id)->update([
                'aprobado' => 1,
                'updated_at' => now(),
            ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function obtenerUsuarioPorEmail($email)
    {
        return DB::table('users')
            ->where('email', $email)
            ->where('status', 1)
            ->first();
    }

    public static function obtenerUsuarioPorCedula($cedula)
    {
        return DB::table('users')
            ->where('cedula', $cedula)
            ->where('status', 1)
            ->first();
    }

    public static function crearTokenRecuperacion($email, $token)
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );
    }

    public static function verificarRespuestaSeguridad($hashGuardado, $respuestaIngresada)
    {
        $respuestaIngresada = trim($respuestaIngresada);
        $respuestaIngresada = mb_strtolower($respuestaIngresada, 'UTF-8');
        $unwanted_array = [
            'Š' => 'S',
            'š' => 's',
            'Ž' => 'Z',
            'ž' => 'z',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'B',
            'ß' => 'Ss',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'o',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ý' => 'y',
            'þ' => 'b',
            'ÿ' => 'y'
        ];
        $respuestaIngresada = strtr($respuestaIngresada, $unwanted_array);

        return Hash::check($respuestaIngresada, $hashGuardado);
    }
}
