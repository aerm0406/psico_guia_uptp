<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completa tu Perfil - Psico-Guía UPTP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            const getStoredTheme = () => localStorage.getItem('theme');
            const getSystemTheme = () => window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

            let theme = getStoredTheme();
            if (!theme) theme = 'auto';

            if (theme === 'auto') {
                const systemTheme = getSystemTheme();
                if (systemTheme === 'dark') document.documentElement.classList.add('dark');
            } else if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-indigo-50 dark:bg-[#020617] dark:from-[#020617] dark:to-[#0f172a] min-h-screen flex items-center justify-center py-12 px-4">

    <div class="w-full max-w-2xl" x-data="{
        step: 1,
        maxSteps: {{ auth()->user()->role === 'paciente' ? 3 : 2 }},
        discapacidad: '{{ old('discapacidad', '') }}',
        tiene_hijos: '{{ old('tiene_hijos', '') }}',
        perfil_academico: '{{ old('perfil_academico', '') }}',
        password: '',
        password_confirmation: '',
        q1: '{{ old('pregunta_seguridad_1', '') }}',
        q2: '{{ old('pregunta_seguridad_2', '') }}',
        q3: '{{ old('pregunta_seguridad_3', '') }}',
        get passwordStrength() {
            return {
                length: this.password.length >= 8 && this.password.length <= 16,
                upper: /[A-Z]/.test(this.password),
                lower: /[a-z]/.test(this.password),
                number: /[0-9]/.test(this.password),
                special: /[@$!%*?&]/.test(this.password)
            };
        },
        get passwordsMatch() {
            return this.password === this.password_confirmation;
        }
    }">

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-indigo-200 dark:shadow-none">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Completa tu Perfil</h1>
            <p class="mt-2 text-slate-500 dark:text-slate-400 text-sm max-w-md mx-auto">
                Paso <span x-text="step"></span> de <span x-text="maxSteps"></span>
            </p>
        </div>

        {{-- Card --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-3xl shadow-xl border border-slate-100 dark:border-slate-700 overflow-hidden">
            
            {{-- Progress Bar Visual --}}
            <div class="h-1.5 bg-slate-100 dark:bg-gray-700 w-full relative">
                <div class="h-full bg-indigo-600 absolute left-0 top-0 transition-all duration-300" :style="`width: ${(step / maxSteps) * 100}%`"></div>
            </div>

            <div class="p-8">

                {{-- Errores globales --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-2xl">
                        <p class="text-sm font-bold text-red-700 dark:text-red-400 mb-1">Por favor corrige los siguientes errores:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm text-red-600 dark:text-red-400">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.complete.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- PASO 1: Datos Personales --}}
                    <div x-show="step === 1" x-transition.opacity.duration.300ms>
                        <h2 class="text-base font-bold text-indigo-700 dark:text-indigo-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400 rounded-full text-xs flex items-center justify-center font-black">1</span>
                            Datos Personales
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="nombres" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Nombres <span class="text-red-500">*</span></label>
                                @if($user->nombres)
                                    <div class="w-full bg-slate-100 dark:bg-slate-700 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-500 dark:text-slate-400 font-medium flex items-center justify-between">
                                        <span>{{ $user->nombres }}</span>
                                    </div>
                                @else
                                    <input id="nombres" name="nombres" type="text" class="w-full bg-slate-50 dark:bg-slate-900 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500" value="{{ old('nombres') }}" required />
                                @endif
                            </div>
                            <div>
                                <label for="apellidos" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Apellidos <span class="text-red-500">*</span></label>
                                @if($user->apellidos)
                                    <div class="w-full bg-slate-100 dark:bg-slate-700 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-500 dark:text-slate-400 font-medium flex items-center justify-between">
                                        <span>{{ $user->apellidos }}</span>
                                    </div>
                                @else
                                    <input id="apellidos" name="apellidos" type="text" class="w-full bg-slate-50 dark:bg-slate-900 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500" value="{{ old('apellidos') }}" required />
                                @endif
                            </div>
                            <div>
                                <label for="cedula" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Cédula <span class="text-red-500">*</span></label>
                                @if($user->cedula)
                                    <div class="w-full bg-slate-100 dark:bg-slate-700 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-500 dark:text-slate-400 font-medium flex items-center justify-between">
                                        <span>{{ $user->cedula }}</span>
                                    </div>
                                @else
                                    <input id="cedula" name="cedula" type="text" class="w-full bg-slate-50 dark:bg-slate-900 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500" value="{{ old('cedula') }}" required />
                                @endif
                            </div>
                            <div>
                                <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Correo <span class="text-red-500">*</span></label>
                                @if($user->email)
                                    <div class="w-full bg-slate-100 dark:bg-slate-700 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-500 dark:text-slate-400 font-medium flex items-center justify-between">
                                        <span>{{ $user->email }}</span>
                                    </div>
                                @else
                                    <input id="email" name="email" type="email" class="w-full bg-slate-50 dark:bg-slate-900 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500" value="{{ old('email') }}" required />
                                @endif
                            </div>
                            <div>
                                <label for="genero" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Género <span class="text-red-500">*</span></label>
                                <select id="genero" name="genero" class="w-full bg-slate-50 dark:bg-slate-900 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                    <option value="Masculino" {{ old('genero', $user->genero) == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                    <option value="Femenino" {{ old('genero', $user->genero) == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                </select>
                            </div>
                            <div>
                                <label for="fecha_nacimiento" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Nacimiento <span class="text-red-500">*</span></label>
                                <input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="w-full bg-slate-50 dark:bg-slate-900 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500" value="{{ old('fecha_nacimiento', $user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->format('Y-m-d') : '') }}" required />
                            </div>
                            <div>
                                <label for="telefono" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Teléfono Móvil <span class="text-red-500">*</span></label>
                                <input id="telefono" name="telefono" type="text" class="w-full bg-slate-50 dark:bg-slate-900 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500" value="{{ old('telefono', $user->telefono) }}" placeholder="Ej: 0412-1234567" required />
                            </div>
                            <div>
                                <label for="ubicacion" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Ubicación <span class="text-red-500">*</span></label>
                                <input id="ubicacion" name="ubicacion" type="text" class="w-full bg-slate-50 dark:bg-slate-900 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500" value="{{ old('ubicacion', $user->ubicacion) }}" placeholder="Ej: Páez, Acarigua" required />
                            </div>
                            <div>
                                <label for="estado_civil" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Estado Civil <span class="text-red-500">*</span></label>
                                <select id="estado_civil" name="estado_civil" class="w-full bg-slate-50 dark:bg-slate-900 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                    <option value="Soltero(a)" {{ old('estado_civil', $user->estado_civil) == 'Soltero(a)' ? 'selected' : '' }}>Soltero(a)</option>
                                    <option value="Casado(a)" {{ old('estado_civil', $user->estado_civil) == 'Casado(a)' ? 'selected' : '' }}>Casado(a)</option>
                                    <option value="Divorciado(a)" {{ old('estado_civil', $user->estado_civil) == 'Divorciado(a)' ? 'selected' : '' }}>Divorciado(a)</option>
                                    <option value="Viudo(a)" {{ old('estado_civil', $user->estado_civil) == 'Viudo(a)' ? 'selected' : '' }}>Viudo(a)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-700">
                            <div>
                                <span class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-3">¿Tiene Hijos? <span class="text-red-500">*</span></span>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-1"><input type="radio" name="tiene_hijos" value="Si" x-model="tiene_hijos" class="text-indigo-600 focus:ring-indigo-500" /> Sí</label>
                                    <label class="flex items-center gap-1"><input type="radio" name="tiene_hijos" value="No" x-model="tiene_hijos" class="text-indigo-600 focus:ring-indigo-500" /> No</label>
                                </div>
                                <div x-show="tiene_hijos === 'Si'" class="mt-3" x-transition>
                                    <input name="numero_hijos" type="number" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500" value="{{ old('numero_hijos') }}" placeholder="Cantidad" />
                                </div>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-3">¿Tiene Discapacidad? <span class="text-red-500">*</span></span>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-1"><input type="radio" name="discapacidad" value="Si" x-model="discapacidad" class="text-indigo-600 focus:ring-indigo-500" /> Sí</label>
                                    <label class="flex items-center gap-1"><input type="radio" name="discapacidad" value="No" x-model="discapacidad" class="text-indigo-600 focus:ring-indigo-500" /> No</label>
                                </div>
                                <div x-show="discapacidad === 'Si'" class="mt-3" x-transition>
                                    <select name="tipo_discapacidad" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                        <option value="" disabled selected>Seleccione...</option>
                                        <option value="Física" {{ old('tipo_discapacidad') == 'Física' ? 'selected' : '' }}>Física</option>
                                        <option value="Intelectual o Mental" {{ old('tipo_discapacidad') == 'Intelectual o Mental' ? 'selected' : '' }}>Intelectual o Mental</option>
                                        <option value="Psíquica" {{ old('tipo_discapacidad') == 'Psíquica' ? 'selected' : '' }}>Psíquica</option>
                                        <option value="Sensorial - Auditiva" {{ old('tipo_discapacidad') == 'Sensorial - Auditiva' ? 'selected' : '' }}>Sensorial - Auditiva</option>
                                        <option value="Sensorial - Visual" {{ old('tipo_discapacidad') == 'Sensorial - Visual' ? 'selected' : '' }}>Sensorial - Visual</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end border-t border-slate-100 dark:border-slate-700 pt-6">
                            <button type="button" @click="let invalid = [...$el.closest('div[x-show]').querySelectorAll('input, select')].find(i => !i.checkValidity()); if(invalid) { invalid.reportValidity(); } else { step++; }" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-indigo-200 dark:shadow-none">
                                Siguiente &rarr;
                            </button>
                        </div>
                    </div>

                    {{-- PASO 2: Académico (Solo Pacientes) --}}
                    @if(auth()->user()->role === 'paciente')
                    <div x-show="step === 2" x-cloak x-transition.opacity.duration.300ms style="display: none;">
                        <h2 class="text-base font-bold text-indigo-700 dark:text-indigo-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400 rounded-full text-xs flex items-center justify-center font-black">2</span>
                            Información Académica
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="perfil_academico" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Perfil Académico <span class="text-red-500">*</span></label>
                                <select id="perfil_academico" name="perfil_academico" x-model="perfil_academico" class="w-full bg-slate-50 dark:bg-slate-900 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500">
                                    <option value="" disabled selected>Seleccione...</option>
                                    <option value="Estudiante">Estudiante</option>
                                    <option value="Profesor">Profesor</option>
                                    <option value="Obrero">Obrero</option>
                                    <option value="Administrativo">Administrativo</option>
                                    <option value="Pre-escolar">Pre-escolar</option>
                                    <option value="Otros">Otros</option>
                                </select>
                            </div>

                            <template x-if="perfil_academico === 'Estudiante'">
                                <div class="contents">
                                    <div>
                                        <label for="pnf" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">PNF <span class="text-red-500">*</span></label>
                                        <select id="pnf" name="pnf" class="w-full bg-slate-50 dark:bg-slate-900 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500">
                                            <option value="" disabled selected>Seleccione...</option>
                                            <option value="Administracion" {{ old('pnf') == 'Administracion' ? 'selected' : '' }}>Administración</option>
                                            <option value="Mecanica" {{ old('pnf') == 'Mecanica' ? 'selected' : '' }}>Mecánica</option>
                                            <option value="Mantenimiento" {{ old('pnf') == 'Mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                                            <option value="Electricidad" {{ old('pnf') == 'Electricidad' ? 'selected' : '' }}>Electricidad</option>
                                            <option value="Veterinaria" {{ old('pnf') == 'Veterinaria' ? 'selected' : '' }}>Veterinaria</option>
                                            <option value="Informatica" {{ old('pnf') == 'Informatica' ? 'selected' : '' }}>Informática</option>
                                            <option value="PDA" {{ old('pnf') == 'PDA' ? 'selected' : '' }}>PDA</option>
                                            <option value="Distribucion_Logistica" {{ old('pnf') == 'Distribucion_Logistica' ? 'selected' : '' }}>Distribución y Logística</option>
                                            <option value="Agroalimentacion" {{ old('pnf') == 'Agroalimentacion' ? 'selected' : '' }}>Agroalimentación</option>
                                            <option value="Seguridad_Alimentaria_Nutricional" {{ old('pnf') == 'Seguridad_Alimentaria_Nutricional' ? 'selected' : '' }}>Seguridad Alimentaria</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="semestre" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Semestre <span class="text-red-500">*</span></label>
                                        <select id="semestre" name="semestre" class="w-full bg-slate-50 dark:bg-slate-900 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500">
                                            <option value="" disabled selected>Seleccione...</option>
                                            @foreach(range(1, 12) as $s)
                                                <option value="{{ $s }}" {{ old('semestre') == $s ? 'selected' : '' }}>{{ $s }}° Semestre</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="horario_file" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Documento del Horario (Opcional)</label>
                                        <input id="horario_file" name="horario_file" type="file"
                                            class="w-full bg-slate-50 dark:bg-slate-900 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mt-8 flex justify-between border-t border-slate-100 dark:border-slate-700 pt-6">
                            <button type="button" @click="step--" class="px-6 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-sm rounded-xl transition-all">
                                &larr; Atrás
                            </button>
                            <button type="button" @click="let invalid = [...$el.closest('div[x-show]').querySelectorAll('input, select')].find(i => !i.checkValidity()); if(invalid) { invalid.reportValidity(); } else { step++; }" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-indigo-200 dark:shadow-none">
                                Siguiente &rarr;
                            </button>
                        </div>
                    </div>
                    @endif

                    {{-- PASO FINAL: Seguridad --}}
                    <div x-show="step === maxSteps" x-cloak x-transition.opacity.duration.300ms style="display: none;">
                        <h2 class="text-base font-bold text-indigo-700 dark:text-indigo-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400 rounded-full text-xs flex items-center justify-center font-black" x-text="maxSteps"></span>
                            Seguridad de la Cuenta
                        </h2>

                        @if(auth()->user()->must_change_password)
                        <div class="mb-6 p-5 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                            <h3 class="text-sm font-bold text-indigo-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                Nueva Contraseña
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Contraseña <span class="text-red-500">*</span></label>
                                    <input name="password" type="password" x-model="password" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Mínimo 8 caracteres" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Confirmar Contraseña <span class="text-red-500">*</span></label>
                                    <input name="password_confirmation" type="password" x-model="password_confirmation" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Repite la contraseña" />
                                    <p x-show="password_confirmation.length > 0 && !passwordsMatch" style="display: none;" class="text-xs font-medium text-red-500 mt-1">
                                        La confirmación de la contraseña no coincide.
                                    </p>
                                </div>
                            </div>
                            
                            {{-- Validaciones visuales en tiempo real --}}
                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-medium bg-white dark:bg-slate-800 p-3 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm">
                                <div :class="passwordStrength.length ? 'text-green-600' : 'text-slate-400'" class="flex items-center gap-2 transition-colors">
                                    <svg x-show="passwordStrength.length" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <svg x-show="!passwordStrength.length" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    8 - 16 caracteres
                                </div>
                                <div :class="passwordStrength.upper ? 'text-green-600' : 'text-slate-400'" class="flex items-center gap-2 transition-colors">
                                    <svg x-show="passwordStrength.upper" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <svg x-show="!passwordStrength.upper" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Una letra mayúscula
                                </div>
                                <div :class="passwordStrength.lower ? 'text-green-600' : 'text-slate-400'" class="flex items-center gap-2 transition-colors">
                                    <svg x-show="passwordStrength.lower" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <svg x-show="!passwordStrength.lower" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Una letra minúscula
                                </div>
                                <div :class="passwordStrength.number ? 'text-green-600' : 'text-slate-400'" class="flex items-center gap-2 transition-colors">
                                    <svg x-show="passwordStrength.number" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <svg x-show="!passwordStrength.number" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Un número
                                </div>
                                <div :class="passwordStrength.special ? 'text-green-600' : 'text-slate-400'" class="flex items-center gap-2 sm:col-span-2 transition-colors">
                                    <svg x-show="passwordStrength.special" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <svg x-show="!passwordStrength.special" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Un carácter especial (@$!%*?&)
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="mb-6">
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Preguntas de Seguridad
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 p-3 rounded-xl border border-yellow-100 dark:border-yellow-800">
                                <strong class="font-bold">¡Importante!</strong> Estas preguntas te ayudarán a recuperar el acceso a tu cuenta si olvidas tu contraseña. Tus respuestas deben ser seguras y fáciles de recordar para ti.
                            </p>

                            @php
                                $preguntas = [
                                    "¿Cuál es tu color favorito?",
                                    "¿Cuál es tu postre favorito?",
                                    "¿Nombre de tu cantante o banda favorita?",
                                    "¿Cuál fue el nombre de tu primera mascota?",
                                    "¿En qué ciudad naciste?",
                                    "¿Cuál es tu película favorita?",
                                    "¿Nombre de tu mejor amigo de la infancia?",
                                    "¿Cuál es tu deporte favorito?"
                                ];
                            @endphp

                            <div class="space-y-4">
                                {{-- Pregunta 1 --}}
                                <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-700">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Pregunta 1 <span class="text-red-500">*</span></label>
                                    <select x-model="q1" name="pregunta_seguridad_1" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-t-xl border-b-0 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 focus:ring-0 focus:border-indigo-500" required>
                                        <option value="" disabled selected>Selecciona una pregunta...</option>
                                        @foreach($preguntas as $p) <option value="{{ $p }}" :disabled="q2 === '{{ $p }}' || q3 === '{{ $p }}'" :class="{'text-slate-300 bg-slate-50 dark:bg-slate-900': q2 === '{{ $p }}' || q3 === '{{ $p }}'}">{{ $p }}</option> @endforeach
                                    </select>
                                    <input name="respuesta_seguridad_1" value="{{ old('respuesta_seguridad_1') }}" type="text" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-b-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white font-medium focus:ring-0 focus:border-indigo-500" placeholder="Escribe tu respuesta..." required />
                                    @error('pregunta_seguridad_1') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    @error('respuesta_seguridad_1') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>

                                {{-- Pregunta 2 --}}
                                <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-700">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Pregunta 2 <span class="text-red-500">*</span></label>
                                    <select x-model="q2" name="pregunta_seguridad_2" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-t-xl border-b-0 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 focus:ring-0 focus:border-indigo-500" required>
                                        <option value="" disabled selected>Selecciona una pregunta...</option>
                                        @foreach($preguntas as $p) <option value="{{ $p }}" :disabled="q1 === '{{ $p }}' || q3 === '{{ $p }}'" :class="{'text-slate-300 bg-slate-50 dark:bg-slate-900': q1 === '{{ $p }}' || q3 === '{{ $p }}'}">{{ $p }}</option> @endforeach
                                    </select>
                                    <input name="respuesta_seguridad_2" value="{{ old('respuesta_seguridad_2') }}" type="text" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-b-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white font-medium focus:ring-0 focus:border-indigo-500" placeholder="Escribe tu respuesta..." required />
                                    @error('pregunta_seguridad_2') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    @error('respuesta_seguridad_2') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>

                                {{-- Pregunta 3 --}}
                                <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-700">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Pregunta 3 <span class="text-red-500">*</span></label>
                                    <select x-model="q3" name="pregunta_seguridad_3" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-t-xl border-b-0 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 focus:ring-0 focus:border-indigo-500" required>
                                        <option value="" disabled selected>Selecciona una pregunta...</option>
                                        @foreach($preguntas as $p) <option value="{{ $p }}" :disabled="q1 === '{{ $p }}' || q2 === '{{ $p }}'" :class="{'text-slate-300 bg-slate-50 dark:bg-slate-900': q1 === '{{ $p }}' || q2 === '{{ $p }}'}">{{ $p }}</option> @endforeach
                                    </select>
                                    <input name="respuesta_seguridad_3" value="{{ old('respuesta_seguridad_3') }}" type="text" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-b-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white font-medium focus:ring-0 focus:border-indigo-500" placeholder="Escribe tu respuesta..." required />
                                    @error('pregunta_seguridad_3') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    @error('respuesta_seguridad_3') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-between items-center border-t border-slate-100 dark:border-slate-700 pt-6">
                            <button type="button" @click="step--" class="px-6 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-sm rounded-xl transition-all">
                                &larr; Atrás
                            </button>
                            <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-all shadow-lg shadow-indigo-200 dark:shadow-none hover:shadow-indigo-300 active:scale-[0.98] flex items-center gap-2">
                                Guardar Perfil
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-slate-400 mt-6">
            Psico-Guía UPTP — Tu información está protegida y es confidencial.
        </p>

    </div>
</body>
</html>
