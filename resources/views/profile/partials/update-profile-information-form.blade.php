<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-8" x-data="{
        editing: false,
        discapacidad: '{{ old('discapacidad', $user->discapacidad ?? 'No') }}',
        tiene_hijos: '{{ old('tiene_hijos', $user->tiene_hijos ?? 'No') }}',
        perfil_academico: '{{ old('perfil_academico', $user->perfil_academico ?? '') }}'
    }">
        @csrf
        @method('patch')

        @if(Auth::user()->role === 'paciente' || Auth::user()->role === 'psicologo')
        <!-- Sección: Información Personal y de Contacto -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-slate-900">{{ __('Información Personal y de Contacto') }}</h3>
                <div @click="editing = !editing" class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors cursor-pointer" :class="editing ? 'bg-indigo-600 text-white' : ''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <!-- Nombres -->
                <div>
                    <label for="nombres" class="block text-xs font-bold text-slate-900 mb-2">{{ __('Nombres') }}</label>
                    <input id="nombres" name="nombres" type="text"
                        :disabled="!editing"
                        class="w-full border-0 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                        :class="!editing ? 'bg-slate-50/50 text-slate-400 cursor-not-allowed' : 'bg-slate-50 text-slate-700'"
                        value="{{ old('nombres', $user->nombres) }}" autofocus autocomplete="given-name" placeholder="Ej: Ana María" />
                    <x-input-error class="mt-1 text-xs" :messages="$errors->get('nombres')" />
                </div>

                <!-- Apellidos -->
                <div>
                    <label for="apellidos" class="block text-xs font-bold text-slate-900 mb-2">{{ __('Apellidos') }}</label>
                    <input id="apellidos" name="apellidos" type="text"
                        :disabled="!editing"
                        class="w-full border-0 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                        :class="!editing ? 'bg-slate-50/50 text-slate-400 cursor-not-allowed' : 'bg-slate-50 text-slate-700'"
                        value="{{ old('apellidos', $user->apellidos) }}" autocomplete="family-name" placeholder="Ej: García López" />
                    <x-input-error class="mt-1 text-xs" :messages="$errors->get('apellidos')" />
                </div>

                <!-- Cédula -->
                <div>
                    <label for="cedula" class="block text-xs font-bold text-slate-900 mb-2">{{ __('Cédula') }}</label>
                    <input id="cedula" name="cedula" type="text"
                        :disabled="!editing"
                        class="w-full border-0 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                        :class="!editing ? 'bg-slate-50/50 text-slate-400 cursor-not-allowed' : 'bg-slate-50 text-slate-700'"
                        value="{{ old('cedula', $user->cedula) }}" placeholder="Ej: 12345678" />
                    <x-input-error class="mt-1 text-xs" :messages="$errors->get('cedula')" />
                </div>

                <!-- Género (Select) -->
                <div>
                    <label for="genero" class="block text-xs font-bold text-slate-900 mb-2">{{ __('Género') }}</label>
                    <select id="genero" name="genero"
                        :disabled="!editing"
                        class="w-full border-0 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                        :class="!editing ? 'bg-slate-50/50 text-slate-400 cursor-not-allowed' : 'bg-slate-50 text-slate-700'">
                        <option value="" disabled {{ is_null($user->genero) ? 'selected' : '' }}>Seleccione...</option>
                        <option value="Masculino" {{ old('genero', $user->genero) == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                        <option value="Femenino" {{ old('genero', $user->genero) == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                    </select>
                    <x-input-error class="mt-1 text-xs" :messages="$errors->get('genero')" />
                </div>

                <!-- Fecha de Nacimiento -->
                <div>
                    <label for="fecha_nacimiento" class="block text-xs font-bold text-slate-900 mb-2">{{ __('Fecha de Nacimiento') }}</label>
                    <input id="fecha_nacimiento" name="fecha_nacimiento" type="date"
                        :disabled="!editing"
                        class="w-full border-0 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                        :class="!editing ? 'bg-slate-50/50 text-slate-400 cursor-not-allowed' : 'bg-slate-50 text-slate-700'"
                        value="{{ old('fecha_nacimiento', $user->fecha_nacimiento?->format('Y-m-d')) }}" />
                    <x-input-error class="mt-1 text-xs" :messages="$errors->get('fecha_nacimiento')" />
                </div>

                <!-- Correo Electrónico -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-900 mb-2">{{ __('Correo Electrónico') }}</label>
                    <input id="email" name="email" type="email"
                        :disabled="!editing"
                        class="w-full border-0 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                        :class="!editing ? 'bg-slate-50/50 text-slate-400 cursor-not-allowed' : 'bg-slate-50 text-slate-700'"
                        value="{{ old('email', $user->email) }}" required autocomplete="username" />
                    <x-input-error class="mt-1 text-xs" :messages="$errors->get('email')" />
                </div>

                <!-- Teléfono Móvil -->
                <div>
                    <label for="telefono" class="block text-xs font-bold text-slate-900 mb-2">{{ __('Teléfono Móvil') }}</label>
                    <input id="telefono" name="telefono" type="text"
                        :disabled="!editing"
                        class="w-full border-0 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                        :class="!editing ? 'bg-slate-50/50 text-slate-400 cursor-not-allowed' : 'bg-slate-50 text-slate-700'"
                        value="{{ old('telefono', $user->telefono) }}" placeholder="Ej: 0412-1234567" />
                    <x-input-error class="mt-1 text-xs" :messages="$errors->get('telefono')" />
                </div>

                <!-- Estado Civil (Select) -->
                <div>
                    <label for="estado_civil" class="block text-xs font-bold text-slate-900 mb-2">{{ __('Estado Civil') }}</label>
                    <select id="estado_civil" name="estado_civil"
                        :disabled="!editing"
                        class="w-full border-0 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                        :class="!editing ? 'bg-slate-50/50 text-slate-400 cursor-not-allowed' : 'bg-slate-50 text-slate-700'">
                        <option value="" disabled {{ is_null($user->estado_civil) ? 'selected' : '' }}>Seleccione...</option>
                        <option value="Soltero(a)" {{ old('estado_civil', $user->estado_civil) == 'Soltero(a)' ? 'selected' : '' }}>Soltero(a)</option>
                        <option value="Casado(a)" {{ old('estado_civil', $user->estado_civil) == 'Casado(a)' ? 'selected' : '' }}>Casado(a)</option>
                        <option value="Divorciado(a)" {{ old('estado_civil', $user->estado_civil) == 'Divorciado(a)' ? 'selected' : '' }}>Divorciado(a)</option>
                        <option value="Viudo(a)" {{ old('estado_civil', $user->estado_civil) == 'Viudo(a)' ? 'selected' : '' }}>Viudo(a)</option>
                    </select>
                    <x-input-error class="mt-1 text-xs" :messages="$errors->get('estado_civil')" />
                </div>

                <!-- Ubicación -->
                <div>
                    <label for="ubicacion" class="block text-xs font-bold text-slate-900 mb-2">{{ __('Ubicación') }}</label>
                    <input id="ubicacion" name="ubicacion" type="text"
                        :disabled="!editing"
                        class="w-full border-0 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                        :class="!editing ? 'bg-slate-50/50 text-slate-400 cursor-not-allowed' : 'bg-slate-50 text-slate-700'"
                        value="{{ old('ubicacion', $user->ubicacion) }}" placeholder="Ej: Portuguese, Páez, Acarigua" />
                    <x-input-error class="mt-1 text-xs" :messages="$errors->get('ubicacion')" />
                </div>

                <!-- ¿Tiene Hijos? (Radio Si/No + Número condicional) -->
                <div class="flex flex-col justify-start">
                    <span class="block text-xs font-bold text-slate-900 mb-3">{{ __('¿Tiene Hijos?') }}</span>
                    <div class="flex items-center gap-6 mt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="tiene_hijos" value="Si"
                                x-model="tiene_hijos" :disabled="!editing"
                                class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500 disabled:opacity-50"
                                {{ old('tiene_hijos', $user->tiene_hijos) == 'Si' ? 'checked' : '' }} />
                            <span class="text-sm font-medium" :class="!editing ? 'text-slate-400' : 'text-slate-700'">Sí</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="tiene_hijos" value="No"
                                x-model="tiene_hijos" :disabled="!editing"
                                class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500 disabled:opacity-50"
                                {{ old('tiene_hijos', $user->tiene_hijos) == 'No' ? 'checked' : '' }} />
                            <span class="text-sm font-medium" :class="!editing ? 'text-slate-400' : 'text-slate-700'">No</span>
                        </label>
                    </div>
                    <!-- Número de hijos (solo si marcó Sí) -->
                    <div x-show="tiene_hijos === 'Si'" x-transition class="mt-3">
                        <label for="numero_hijos" class="block text-xs font-bold text-slate-900 mb-2">{{ __('¿Cuántos hijos tienes?') }}</label>
                        <input
                            id="numero_hijos" name="numero_hijos" type="number" min="1" max="50"
                            :disabled="!editing"
                            class="w-full border-0 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                            :class="!editing ? 'bg-slate-50/50 text-slate-400 cursor-not-allowed' : 'bg-slate-50 text-slate-700'"
                            value="{{ old('numero_hijos', $user->numero_hijos) }}"
                            placeholder="Ej: 2" />
                        <x-input-error class="mt-1 text-xs" :messages="$errors->get('numero_hijos')" />
                    </div>
                    <x-input-error class="mt-1 text-xs" :messages="$errors->get('tiene_hijos')" />
                </div>

                <!-- Discapacidad (Radio Si/No + Select condicional) -->
                <div class="md:col-span-2 flex flex-col justify-start">
                    <span class="block text-xs font-bold text-slate-900 mb-3">{{ __('¿Tiene Discapacidad?') }}</span>
                    <div class="flex items-center gap-6 mb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="discapacidad" value="Si"
                                x-model="discapacidad" :disabled="!editing"
                                class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500 disabled:opacity-50"
                                {{ old('discapacidad', $user->discapacidad) == 'Si' ? 'checked' : '' }} />
                            <span class="text-sm font-medium" :class="!editing ? 'text-slate-400' : 'text-slate-700'">Sí</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="discapacidad" value="No"
                                x-model="discapacidad" :disabled="!editing"
                                class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500 disabled:opacity-50"
                                {{ old('discapacidad', $user->discapacidad) == 'No' ? 'checked' : '' }} />
                            <span class="text-sm font-medium" :class="!editing ? 'text-slate-400' : 'text-slate-700'">No</span>
                        </label>
                    </div>
                    <!-- Select tipo de discapacidad (solo visible si marcó "Sí") -->
                    <div x-show="discapacidad === 'Si'" x-transition>
                        <label for="tipo_discapacidad" class="block text-xs font-bold text-slate-900 mb-2">{{ __('Tipo de Discapacidad') }}</label>
                        <select id="tipo_discapacidad" name="tipo_discapacidad"
                            :disabled="!editing"
                            class="w-full border-0 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                            :class="!editing ? 'bg-slate-50/50 text-slate-400 cursor-not-allowed' : 'bg-slate-50 text-slate-700'">
                            <option value="" disabled {{ is_null($user->tipo_discapacidad) ? 'selected' : '' }}>Seleccione un valor...</option>
                            <option value="Física" {{ old('tipo_discapacidad', $user->tipo_discapacidad) == 'Física' ? 'selected' : '' }}>Física</option>
                            <option value="Intelectual o Mental" {{ old('tipo_discapacidad', $user->tipo_discapacidad) == 'Intelectual o Mental' ? 'selected' : '' }}>Intelectual o Mental</option>
                            <option value="Psíquica" {{ old('tipo_discapacidad', $user->tipo_discapacidad) == 'Psíquica' ? 'selected' : '' }}>Psíquica</option>
                            <option value="Sensorial - Auditiva" {{ old('tipo_discapacidad', $user->tipo_discapacidad) == 'Sensorial - Auditiva' ? 'selected' : '' }}>Sensorial - Auditiva</option>
                            <option value="Sensorial - Visual" {{ old('tipo_discapacidad', $user->tipo_discapacidad) == 'Sensorial - Visual' ? 'selected' : '' }}>Sensorial - Visual</option>
                        </select>
                        <x-input-error class="mt-1 text-xs" :messages="$errors->get('tipo_discapacidad')" />
                    </div>
                    <x-input-error class="mt-1 text-xs" :messages="$errors->get('discapacidad')" />
                </div>

            </div>
            
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-4">
                    <p class="text-sm mt-2 text-red-600 font-medium">
                        {{ __('Tu correo no ha sido verificado.') }}
                        <button form="send-verification" class="mx-2 underline text-sm text-gray-600 hover:text-gray-900 rounded-md">
                            {{ __('Reenviar correo de verificación') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-emerald-600">
                            {{ __('Se ha enviado un nuevo enlace de verificación a tu correo.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        @if(Auth::user()->role === 'paciente')
        <hr class="border-slate-100 my-8">

        <!-- Sección: Información Académica -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-slate-900">{{ __('Información Académica') }}</h3>
                <div @click="editing = !editing" class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors cursor-pointer" :class="editing ? 'bg-indigo-600 text-white' : ''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div>
                    <label for="perfil_academico" class="block text-xs font-bold text-slate-900 mb-2">Perfil Académico <span class="text-red-500">*</span></label>
                    <select id="perfil_academico" name="perfil_academico" x-model="perfil_academico" :disabled="!editing"
                        class="w-full border-0 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                        :class="!editing ? 'bg-slate-50/50 text-slate-400 cursor-not-allowed' : 'bg-slate-50 text-slate-700'">
                        <option value="" disabled selected>Seleccione...</option>
                        <option value="Estudiante" {{ old('perfil_academico', $user->perfil_academico) == 'Estudiante' ? 'selected' : '' }}>Estudiante</option>
                        <option value="Profesor" {{ old('perfil_academico', $user->perfil_academico) == 'Profesor' ? 'selected' : '' }}>Profesor</option>
                        <option value="Obrero" {{ old('perfil_academico', $user->perfil_academico) == 'Obrero' ? 'selected' : '' }}>Obrero</option>
                        <option value="Administrativo" {{ old('perfil_academico', $user->perfil_academico) == 'Administrativo' ? 'selected' : '' }}>Administrativo</option>
                    </select>
                    <x-input-error class="mt-1 text-xs" :messages="$errors->get('perfil_academico')" />
                </div>

                <template x-if="perfil_academico === 'Estudiante'">
                    <div class="contents">
                        <div>
                            <label for="pnf" class="block text-xs font-bold text-slate-900 mb-2">PNF <span class="text-red-500">*</span></label>
                            <select id="pnf" name="pnf" :disabled="!editing"
                                class="w-full border-0 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                                :class="!editing ? 'bg-slate-50/50 text-slate-400 cursor-not-allowed' : 'bg-slate-50 text-slate-700'">
                                <option value="" disabled selected>Seleccione PNF...</option>
                                <option value="Informatica" {{ old('pnf', $user->pnf) == 'Informatica' ? 'selected' : '' }}>Informática</option>
                                <option value="Agroalimentaria" {{ old('pnf', $user->pnf) == 'Agroalimentaria' ? 'selected' : '' }}>Agroalimentaria</option>
                                <option value="Mecanica" {{ old('pnf', $user->pnf) == 'Mecanica' ? 'selected' : '' }}>Mecánica</option>
                                <option value="Administracion" {{ old('pnf', $user->pnf) == 'Administracion' ? 'selected' : '' }}>Administración</option>
                                <option value="Electrica" {{ old('pnf', $user->pnf) == 'Electrica' ? 'selected' : '' }}>Eléctrica</option>
                            </select>
                            <x-input-error class="mt-1 text-xs" :messages="$errors->get('pnf')" />
                        </div>
                        <div>
                            <label for="semestre" class="block text-xs font-bold text-slate-900 mb-2">Semestre <span class="text-red-500">*</span></label>
                            <select id="semestre" name="semestre" :disabled="!editing"
                                class="w-full border-0 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                                :class="!editing ? 'bg-slate-50/50 text-slate-400' : 'bg-slate-50'">
                                <option value="" disabled selected>Seleccione...</option>
                                @foreach(range(1, 12) as $s)
                                    <option value="{{ $s }}" {{ old('semestre', $user->semestre) == $s ? 'selected' : '' }}>{{ $s }}° Semestre</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-1 text-xs" :messages="$errors->get('semestre')" />
                        </div>
                        <div>
                            <label for="horario_file" class="block text-xs font-bold text-slate-900 mb-2">Horario</label>
                            @if($user->horario_path)
                                <div class="flex items-center gap-2 mb-1">
                                    <a href="{{ Storage::url($user->horario_path) }}" target="_blank" class="text-[10px] text-indigo-600 font-bold hover:underline flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        {{ __('Ver actual') }}
                                    </a>
                                </div>
                            @endif
                            <input id="horario_file" name="horario_file" type="file" :disabled="!editing"
                                class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 disabled:opacity-50" />
                            <x-input-error class="mt-1 text-xs" :messages="$errors->get('horario_file')" />
                        </div>
                    </div>
                </template>
            </div>
        </div>
        @endif

        @else
        <!-- Vista de Perfil Clásica para Otros Roles (Administración, etc) -->
        <header x-data="{ editing: true }">
            <h2 class="text-lg font-medium text-gray-900">{{ __('Información del perfil') }}</h2>
            <p class="mt-1 text-sm text-gray-600">{{ __('Actualiza tu información personal y dirección de correo electrónico.') }}</p>
        </header>
        
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
            
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Tu correo no ha sido verificado.') }}
                        <button form="send-verification" class="mx-2 underline text-sm text-gray-600 hover:text-gray-900 rounded-md">
                            {{ __('Haz clic aquí para reenviar el correo.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('Se ha enviado un nuevo enlace de verificación a tu correo.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
        @endif

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 mt-6" x-show="editing" x-transition>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-sm font-bold text-emerald-600 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ __('Guardado correctamente.') }}
                </p>
            @endif

            <button type="button" @click="editing = false" class="px-6 py-2.5 bg-slate-100 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-200 transition-all active:scale-95">
                {{ __('Cancelar') }}
            </button>

            <button type="submit" class="px-10 py-2.5 bg-[#050b1d] text-white text-sm font-bold rounded-xl hover:bg-slate-800 transition-all shadow-lg active:scale-95">
                {{ __('Guardar') }}
            </button>
        </div>
    </form>
</section>
