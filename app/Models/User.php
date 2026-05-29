<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class User
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    const ROLE_PSICOLOGO = 'psicologo';
    const ROLE_PACIENTE = 'paciente';
    const ROLE_ADMIN = 'admin';

    /**
     * Genera una contraseña segura cumpliendo con:
     * - Mínimo 8, máximo 16 caracteres.
     * - Al menos 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial (@$!%*?&).
     */
    public static function generarPasswordSegura()
    {
        $length = rand(10, 14);
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '@$!%*?&';

        // Asegurar al menos uno de cada uno
        $password = $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $special[rand(0, strlen($special) - 1)];

        // Completar el resto
        $all = $uppercase . $lowercase . $numbers . $special;
        for ($i = 0; $i < $length - 4; $i++) {
            $password .= $all[rand(0, strlen($all) - 1)];
        }

        return str_shuffle($password);
    }

    /**
     * Accesor para obtener el nombre completo de forma dinámica.
     */
    public function getNameAttribute()
    {
        return trim(($this->nombres ?? '') . ' ' . ($this->apellidos ?? ''));
    }

    /**
     * Accesor para obtener solo el primer nombre y primer apellido.
     */
    public function getShortNameAttribute()
    {
        $firstName = explode(' ', trim($this->nombres ?? ''))[0];
        $firstLastName = explode(' ', trim($this->apellidos ?? ''))[0];
        return trim($firstName . ' ' . $firstLastName);
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

    /**
     * Filtro de busqueda de usuarios
     */

    /**
     * Realiza una búsqueda filtrada de usuarios.
     * @param string|null $buscar Término de búsqueda (Nombre, apellido, email o cédula).
     * @param string|null $role Rol opcional para filtrar.
     * @param int $cantidad Número de elementos por página.
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function buscarUsuarios($buscar = null, $role = null, $cantidad = 8)
    {
        $query = DB::table('users')
            ->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
            ->where('status', 1);

        if ($role) {
            $query->where('role', $role);
        }

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                  ->orWhere('apellidos', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('cedula', 'like', "%{$buscar}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate($cantidad);
    }

    /**
     * Obtiene un usuario específico por su ID siempre que esté activo.
     * @param int $id
     * @return \stdClass|null
     */
    public static function obtenerUsuarioPorId($id)
    {
        $user = DB::table('users')
            ->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
            ->where('id', $id)
            ->where('status', 1)
            ->first();

        if ($user) {
            $user->primera_cita = DB::table('citas')
                ->where('user_id', $id)
                ->whereNotNull('fecha')
                ->orderBy('fecha', 'asc')
                ->value('fecha');
        }

        return $user;
    }

    /**
     * Instancia un modelo User desde Query Builder para envío de notificaciones.
     */
    public static function instanciarParaNotificacion($id)
    {
        $data = DB::table('users')->where('id', $id)->first();
        if (!$data) return null;
        $notifiable = new class {
            use \Illuminate\Notifications\Notifiable;
            public $id;
            public $email;
            public $nombres;
            public $apellidos;
            public $name;
            public $role;

            public function getKey() {
                return $this->id;
            }

            public function getMorphClass() {
                return \App\Models\User::class;
            }

            public function routeNotificationForMail($notification) {
                return $this->email;
            }

            public function notifications() {
                return new class($this->id) {
                    private $userId;
                    public function __construct($userId) {
                        $this->userId = $userId;
                    }
                    public function create($data) {
                        \Illuminate\Support\Facades\DB::table('notifications')->insert([
                            'id' => $data['id'] ?? \Illuminate\Support\Str::uuid()->toString(),
                            'type' => $data['type'],
                            'notifiable_type' => 'App\Models\User',
                            'notifiable_id' => $this->userId,
                            'data' => is_array($data['data']) ? json_encode($data['data']) : $data['data'],
                            'read_at' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                };
            }
        };

        foreach ((array) $data as $key => $value) {
            $notifiable->{$key} = $value;
        }
        $notifiable->name = trim($data->nombres . ' ' . $data->apellidos);

        return $notifiable;
    }

    public static function crearUsuario($data)
    {
        try {
            DB::beginTransaction();
            $id = DB::table('users')->insertGetId([
                'email' => $data['email'] ?? null,
                'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
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
                'name' => trim(($data['nombres'] ?? '') . ' ' . ($data['apellidos'] ?? '')),
                'nombres' => $data['nombres'] ?? null,
                'apellidos' => $data['apellidos'] ?? null,
                'cedula' => $data['cedula'] ?? null,
                'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
                'role' => $data['role'],
                'email' => null,
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

    /**
     * Actualiza los datos de un usuario existente.
     * Uso: Query Builder.
     */
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
                'updated_at' => now(), // Se marca la fecha de edición
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
            }

            $res = DB::table('users')->where('id', $id)->update($updateData);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza la contraseña de un usuario.
     * @param int $id
     * @param string $newPassword Contraseña ya hasheada.
     * @return int
     */
    public static function actualizarContrasena($id, $newPassword)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('users')->where('id', $id)->update([
                'password' => \Illuminate\Support\Facades\Hash::make($newPassword),
                'updated_at' => now(),
            ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Realiza un borrado lógico del usuario cambiando su status a 0.
     * @param int $id
     * @return int
     */
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

    /**
     * Obtiene la lista de psicólogos activos.
     * @return \Illuminate\Support\Collection
     */
    /**
     * Obtiene la lista completa de usuarios con rol de psicólogo.
     */
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
            $pacientesIds = \Illuminate\Support\Facades\DB::table('citas')->where('psicologo_id', $userId)
                ->pluck('user_id')
                ->unique();
            return \Illuminate\Support\Facades\DB::table('users')
                ->select('users.*', \Illuminate\Support\Facades\DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
                ->whereIn('id', $pacientesIds)->get();
        } else {
            $psicologosIds = \Illuminate\Support\Facades\DB::table('citas')->where('user_id', $userId)
                ->pluck('psicologo_id')
                ->unique();
            return \Illuminate\Support\Facades\DB::table('users')
                ->select('users.*', \Illuminate\Support\Facades\DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
                ->whereIn('id', $psicologosIds)->get();
        }
    }

    public static function obtenerEstadisticas()
    {
        return [
            'total' => DB::table('users')->count(),
            'pacientes' => DB::table('users')->where('role', self::ROLE_PACIENTE)->count(),
            'psicologos' => DB::table('users')->where('role', self::ROLE_PSICOLOGO)->count(),
            'admins' => DB::table('users')->where('role', self::ROLE_ADMIN)->count(),
        ];
    }

    /**
     * Métodos Consolidados de Pacientes
     */

    /**
     * Lista pacientes que tienen alguna cita con un psicólogo específico.
     * @param int $psicologoId
     * @param string|null $buscar
     * @param int $cantidad
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function obtenerPacientesConCitas($psicologoId, $buscar = null, $cantidad = 5)
    {
        $query = DB::table('users')->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
            ->selectRaw('(SELECT MIN(fecha) FROM citas WHERE user_id = users.id AND psicologo_id = ? AND estado = "realizada") as primera_cita', [$psicologoId])
            ->whereExists(function ($q) use ($psicologoId) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                  ->from('citas')
                  ->whereColumn('citas.user_id', 'users.id')
                  ->where('citas.psicologo_id', $psicologoId)
                  ->where('citas.estado', '!=', 'cancelada');
            });

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                  ->orWhere('apellidos', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        return $query->orderBy('nombres')->orderBy('apellidos')->paginate($cantidad);
    }

    /**
     * Lista todos los pacientes del sistema que tengan al menos una cita.
     * @param string|null $buscar
     * @param int $cantidad
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function obtenerTodosPacientes($buscar = null, $cantidad = 10)
    {
        $query = DB::table('users')->select('users.*', DB::raw("CONCAT(nombres, ' ', apellidos) as name"))
            ->selectRaw('(SELECT MIN(fecha) FROM citas WHERE user_id = users.id AND estado = "realizada") as primera_cita')
            ->whereExists(function ($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                  ->from('citas')
                  ->whereColumn('citas.user_id', 'users.id')
                  ->where('citas.estado', '!=', 'cancelada');
            });

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                  ->orWhere('apellidos', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        return $query->orderBy('nombres')->orderBy('apellidos')->paginate($cantidad);
    }

    public function getConversationsAttribute()
    {
        return DB::table('conversations')
            ->where('user_one_id', $this->id)
            ->orWhere('user_two_id', $this->id)
            ->get();
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
        // Get conversations where the user is part of (Using Query Builder)
        $conversationIds = DB::table('conversations')
            ->where('user_one_id', $this->id)
            ->orWhere('user_two_id', $this->id)
            ->pluck('id');

        return DB::table('messages')
            ->whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $this->id)
            ->whereNull('read_at')
            ->count();
    }

    public static function contarMensajesNoLeidos($userId)
    {
        // Get conversations where the user is part of (Using Query Builder)
        $conversationIds = DB::table('conversations')
            ->where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->pluck('id');

        return DB::table('messages')
            ->whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Obtiene la lista de psicólogos que tienen horarios activos configurados.
     * Uso: Query Builder con carga manual de relaciones para máxima velocidad y cumplimiento MVC.
     */
    /**
     * Obtiene los psicólogos que tienen horarios activos configurados.
     */
    public static function obtenerPsicologosDisponibles()
    {
        // 1. Obtener psicólogos que tienen al menos un grupo activo
        $psicologos = DB::table('users')
            ->join('grupos_horarios', 'users.id', '=', 'grupos_horarios.user_id')
            ->select('users.*', DB::raw("CONCAT(users.nombres, ' ', users.apellidos) as name"))
            ->where('users.role', self::ROLE_PSICOLOGO)
            ->where('users.status', 1)
            ->where('grupos_horarios.activo', 1)
            ->distinct()
            ->get();

        // 2. Cargar grupos y horarios manualmente para cada psicólogo (Estructura esperada por la vista)
        foreach ($psicologos as $psicologo) {
            $psicologo->gruposHorarios = DB::table('grupos_horarios')
                ->where('user_id', $psicologo->id)
                ->where('activo', 1)
                ->get();

            foreach ($psicologo->gruposHorarios as $grupo) {
                $grupo->horarios = DB::table('horarios')
                    ->where('grupo_horario_id', $grupo->id)
                    ->whereIn('activo', [1, 2]) // Active and Inactive
                    ->get();
            }
        }

        return $psicologos;
    }

    public static function contarUsuarios($role = null)
    {
        $query = DB::table('users');
        if ($role) {
            $query->where('role', $role);
        }
        return $query->count();
    }
}
