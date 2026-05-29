<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completa tu Perfil — Psico-Guía UPTP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-slate-50 to-indigo-50 min-h-screen flex items-center justify-center py-12 px-4">

    <div class="w-full max-w-2xl" x-data="{
        discapacidad: '{{ old('discapacidad', '') }}',
        tiene_hijos: '{{ old('tiene_hijos', '') }}',
        perfil_academico: '{{ old('perfil_academico', '') }}'
    }">

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-indigo-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Completa tu Perfil</h1>
            <p class="mt-2 text-slate-500 text-sm max-w-md mx-auto">
                Antes de continuar, necesitamos algunos datos personales para brindarte una mejor atención.
                Esta información es confidencial y se usará únicamente para fines clínicos.
            </p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden">

            {{-- Progress Bar Visual --}}
            <div class="h-1.5 bg-indigo-600 w-full"></div>

            <div class="p-8">

                {{-- Errores globales --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
                        <p class="text-sm font-bold text-red-700 mb-1">Por favor corrige los siguientes errores:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm text-red-600">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.complete.store') }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    {{-- SECCIÓN 1: Datos Personales --}}
                    <div>
                        <h2 class="text-base font-bold text-indigo-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-black">1</span>
                            Datos Personales
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <div>
                                <label for="nombres" class="block text-xs font-bold text-slate-700 mb-1.5">Nombres <span class="text-red-500">*</span></label>
                                <input id="nombres" name="nombres" type="text"
                                    class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500 @error('nombres') ring-2 ring-red-400 @enderror"
                                    value="{{ old('nombres', $user->nombres) }}" placeholder="Ej: Ana María" required />
                                @error('nombres') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="apellidos" class="block text-xs font-bold text-slate-700 mb-1.5">Apellidos <span class="text-red-500">*</span></label>
                                <input id="apellidos" name="apellidos" type="text"
                                    class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500 @error('apellidos') ring-2 ring-red-400 @enderror"
                                    value="{{ old('apellidos', $user->apellidos) }}" placeholder="Ej: García López" required />
                                @error('apellidos') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="cedula" class="block text-xs font-bold text-slate-700 mb-1.5">Cédula de Identidad <span class="text-red-500">*</span></label>
                                <input id="cedula" name="cedula" type="text"
                                    class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500 @error('cedula') ring-2 ring-red-400 @enderror"
                                    value="{{ old('cedula', $user->cedula) }}" placeholder="Ej: 12345678" required />
                                @error('cedula') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Correo Electrónico <span class="text-red-500">*</span></label>
                                <input id="email" name="email" type="email"
                                    class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500 @error('email') ring-2 ring-red-400 @enderror"
                                    value="{{ old('email', $user->email) }}" placeholder="Ej: ana@ejemplo.com" required />
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="genero" class="block text-xs font-bold text-slate-700 mb-1.5">Género <span class="text-red-500">*</span></label>
                                <select id="genero" name="genero"
                                    class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500 @error('genero') ring-2 ring-red-400 @enderror" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                    <option value="Masculino" {{ old('genero', $user->genero) == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                    <option value="Femenino" {{ old('genero', $user->genero) == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                </select>
                                @error('genero') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="fecha_nacimiento" class="block text-xs font-bold text-slate-700 mb-1.5">Fecha de Nacimiento <span class="text-red-500">*</span></label>
                                <input id="fecha_nacimiento" name="fecha_nacimiento" type="date"
                                    class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500 @error('fecha_nacimiento') ring-2 ring-red-400 @enderror"
                                    value="{{ old('fecha_nacimiento', $user->fecha_nacimiento?->format('Y-m-d')) }}" required />
                                @error('fecha_nacimiento') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="estado_civil" class="block text-xs font-bold text-slate-700 mb-1.5">Estado Civil <span class="text-red-500">*</span></label>
                                <select id="estado_civil" name="estado_civil"
                                    class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500 @error('estado_civil') ring-2 ring-red-400 @enderror" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                    <option value="Soltero(a)" {{ old('estado_civil', $user->estado_civil) == 'Soltero(a)' ? 'selected' : '' }}>Soltero(a)</option>
                                    <option value="Casado(a)" {{ old('estado_civil', $user->estado_civil) == 'Casado(a)' ? 'selected' : '' }}>Casado(a)</option>
                                    <option value="Divorciado(a)" {{ old('estado_civil', $user->estado_civil) == 'Divorciado(a)' ? 'selected' : '' }}>Divorciado(a)</option>
                                    <option value="Viudo(a)" {{ old('estado_civil', $user->estado_civil) == 'Viudo(a)' ? 'selected' : '' }}>Viudo(a)</option>
                                </select>
                                @error('estado_civil') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                        </div>
                    </div>

                    <hr class="border-slate-100">

                    {{-- SECCIÓN 2: Contacto y Ubicación --}}
                    <div>
                        <h2 class="text-base font-bold text-indigo-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-black">2</span>
                            Contacto y Ubicación
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <div>
                                <label for="telefono" class="block text-xs font-bold text-slate-700 mb-1.5">Teléfono Móvil <span class="text-red-500">*</span></label>
                                <input id="telefono" name="telefono" type="text"
                                    class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500 @error('telefono') ring-2 ring-red-400 @enderror"
                                    value="{{ old('telefono', $user->telefono) }}" placeholder="Ej: 0412-1234567" required />
                                @error('telefono') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="ubicacion" class="block text-xs font-bold text-slate-700 mb-1.5">Ubicación <span class="text-red-500">*</span></label>
                                <input id="ubicacion" name="ubicacion" type="text"
                                    class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500 @error('ubicacion') ring-2 ring-red-400 @enderror"
                                    value="{{ old('ubicacion', $user->ubicacion) }}" placeholder="Ej: Portuguese, Páez, Acarigua" required />
                                @error('ubicacion') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                        </div>
                    </div>

                    <hr class="border-slate-100">

                    {{-- SECCIÓN 3: Información Académica (Solo para Pacientes) --}}
                    @if(auth()->user()->role === 'paciente')
                    <div>
                        <h2 class="text-base font-bold text-indigo-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-black">3</span>
                            Información Académica
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="perfil_academico" class="block text-xs font-bold text-slate-700 mb-1.5">Perfil Académico <span class="text-red-500">*</span></label>
                                <select id="perfil_academico" name="perfil_academico" x-model="perfil_academico"
                                    class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500 @error('perfil_academico') ring-2 ring-red-400 @enderror" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                    <option value="Estudiante">Estudiante</option>
                                    <option value="Profesor">Profesor</option>
                                    <option value="Obrero">Obrero</option>
                                    <option value="Administrativo">Administrativo</option>
                                </select>
                                @error('perfil_academico') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <template x-if="perfil_academico === 'Estudiante'">
                                <div class="contents">
                                    <div>
                                        <label for="pnf" class="block text-xs font-bold text-slate-700 mb-1.5">PNF <span class="text-red-500">*</span></label>
                                        <select id="pnf" name="pnf"
                                            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500">
                                            <option value="" disabled selected>Seleccione PNF...</option>
                                            <option value="Informatica" {{ old('pnf') == 'Informatica' ? 'selected' : '' }}>Informática</option>
                                            <option value="Agroalimentaria" {{ old('pnf') == 'Agroalimentaria' ? 'selected' : '' }}>Agroalimentaria</option>
                                            <option value="Mecanica" {{ old('pnf') == 'Mecanica' ? 'selected' : '' }}>Mecánica</option>
                                            <option value="Administracion" {{ old('pnf') == 'Administracion' ? 'selected' : '' }}>Administración</option>
                                            <option value="Electrica" {{ old('pnf') == 'Electrica' ? 'selected' : '' }}>Eléctrica</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="semestre" class="block text-xs font-bold text-slate-700 mb-1.5">Semestre <span class="text-red-500">*</span></label>
                                        <select id="semestre" name="semestre"
                                            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500">
                                            <option value="" disabled selected>Seleccione...</option>
                                            @foreach(range(1, 12) as $s)
                                                <option value="{{ $s }}" {{ old('semestre') == $s ? 'selected' : '' }}>{{ $s }}° Semestre</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="horario_file" class="block text-xs font-bold text-slate-700 mb-1.5">Documento del Horario (Opcional)</label>
                                        <input id="horario_file" name="horario_file" type="file"
                                            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                        <p class="mt-1 text-xs text-slate-400">PDF, JPG o PNG (Máx 4MB)</p>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <hr class="border-slate-100 mt-8">
                    </div>
                    @endif

                    {{-- SECCIÓN: Seguridad de la Cuenta (Solo si requiere cambio de clave) --}}
                    @if(auth()->user()->must_change_password)
                    <div>
                        <h2 class="text-base font-bold text-indigo-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-black">
                                {{ auth()->user()->role === 'paciente' ? '4' : '3' }}
                            </span>
                            Seguridad de la Cuenta
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Nueva Contraseña <span class="text-red-500">*</span></label>
                                <input id="password" name="password" type="password"
                                    class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500 @error('password') ring-2 ring-red-400 @enderror"
                                    placeholder="Mínimo 8 caracteres" required />
                                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5">Confirmar Contraseña <span class="text-red-500">*</span></label>
                                <input id="password_confirmation" name="password_confirmation" type="password"
                                    class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Repite la contraseña" required />
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-[10px] text-slate-400 leading-tight">
                                    <span class="font-bold text-indigo-500">Requerido:</span> Al menos una mayúscula, una minúscula, un número y un carácter especial (@$!%*?&).
                                </p>
                            </div>
                        </div>
                        <hr class="border-slate-100 mt-8">
                    </div>
                    @endif

                    {{-- SECCIÓN final: Información Adicional --}}
                    <div>
                        <h2 class="text-base font-bold text-indigo-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-black">
                                @php
                                    $step = 3;
                                    if(auth()->user()->role === 'paciente') $step++;
                                    if(auth()->user()->must_change_password) $step++;
                                @endphp
                                {{ $step }}
                            </span>
                            Información Adicional
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- ¿Tiene Hijos? --}}
                            <div>
                                <span class="block text-xs font-bold text-slate-700 mb-3">¿Tiene Hijos? <span class="text-red-500">*</span></span>
                                <div class="flex items-center gap-6">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="tiene_hijos" value="Si" x-model="tiene_hijos"
                                            class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500"
                                            {{ old('tiene_hijos') == 'Si' ? 'checked' : '' }} />
                                        <span class="text-sm text-slate-700 font-medium">Sí</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="tiene_hijos" value="No" x-model="tiene_hijos"
                                            class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500"
                                            {{ old('tiene_hijos') == 'No' ? 'checked' : '' }} />
                                        <span class="text-sm text-slate-700 font-medium">No</span>
                                    </label>
                                </div>
                                <div x-show="tiene_hijos === 'Si'" x-transition class="mt-3">
                                    <label for="numero_hijos" class="block text-xs font-bold text-slate-700 mb-1.5">¿Cuántos hijos? <span class="text-red-500">*</span></label>
                                    <input id="numero_hijos" name="numero_hijos" type="number" min="1" max="50"
                                        class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500 @error('numero_hijos') ring-2 ring-red-400 @enderror"
                                        value="{{ old('numero_hijos') }}" placeholder="Ej: 2" />
                                    @error('numero_hijos') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                @error('tiene_hijos') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- ¿Tiene Discapacidad? --}}
                            <div>
                                <span class="block text-xs font-bold text-slate-700 mb-3">¿Tiene Discapacidad? <span class="text-red-500">*</span></span>
                                <div class="flex items-center gap-6">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="discapacidad" value="Si" x-model="discapacidad"
                                            class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500"
                                            {{ old('discapacidad') == 'Si' ? 'checked' : '' }} />
                                        <span class="text-sm text-slate-700 font-medium">Sí</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="discapacidad" value="No" x-model="discapacidad"
                                            class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500"
                                            {{ old('discapacidad') == 'No' ? 'checked' : '' }} />
                                        <span class="text-sm text-slate-700 font-medium">No</span>
                                    </label>
                                </div>
                                <div x-show="discapacidad === 'Si'" x-transition class="mt-3">
                                    <label for="tipo_discapacidad" class="block text-xs font-bold text-slate-700 mb-1.5">Tipo de Discapacidad <span class="text-red-500">*</span></label>
                                    <select id="tipo_discapacidad" name="tipo_discapacidad"
                                        class="w-full bg-slate-50 border-0 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500 @error('tipo_discapacidad') ring-2 ring-red-400 @enderror">
                                        <option value="" disabled selected>Seleccione un valor...</option>
                                        <option value="Física" {{ old('tipo_discapacidad') == 'Física' ? 'selected' : '' }}>Física</option>
                                        <option value="Intelectual o Mental" {{ old('tipo_discapacidad') == 'Intelectual o Mental' ? 'selected' : '' }}>Intelectual o Mental</option>
                                        <option value="Psíquica" {{ old('tipo_discapacidad') == 'Psíquica' ? 'selected' : '' }}>Psíquica</option>
                                        <option value="Sensorial - Auditiva" {{ old('tipo_discapacidad') == 'Sensorial - Auditiva' ? 'selected' : '' }}>Sensorial - Auditiva</option>
                                        <option value="Sensorial - Visual" {{ old('tipo_discapacidad') == 'Sensorial - Visual' ? 'selected' : '' }}>Sensorial - Visual</option>
                                    </select>
                                    @error('tipo_discapacidad') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                @error('discapacidad') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                        </div>
                    </div>

                    {{-- Botón Guardar --}}
                    <div class="pt-4 border-t border-slate-100">
                        <button type="submit"
                            class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-2xl transition-all shadow-lg shadow-indigo-200 hover:shadow-indigo-300 active:scale-[0.98]">
                            Guardar y Continuar →
                        </button>
                        <p class="text-center text-xs text-slate-400 mt-3">
                            Todos los campos marcados con <span class="text-red-500">*</span> son obligatorios.
                        </p>
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
