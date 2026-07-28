<div class="space-y-8" x-data="{ 
    password: '',
    generatePassword() {
        const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const lower = 'abcdefghijklmnopqrstuvwxyz';
        const nums = '0123456789';
        const spec = '@$!%*?&';
        const all = upper + lower + nums + spec;
        
        let pw = upper[Math.floor(Math.random() * upper.length)];
        pw += lower[Math.floor(Math.random() * lower.length)];
        pw += nums[Math.floor(Math.random() * nums.length)];
        pw += spec[Math.floor(Math.random() * spec.length)];
        
        for (let i = 0; i < 8; i++) {
            pw += all[Math.floor(Math.random() * all.length)];
        }
        this.password = pw.split('').sort(() => 0.5 - Math.random()).join('');
    }
}">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
        <div>
            <label for="nombres" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Nombres</label>
            <input type="text" name="nombres" id="nombres" value="{{ old('nombres', $usuario->nombres ?? '') }}" required
                class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium">
            @error('nombres') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="apellidos" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Apellidos</label>
            <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos', $usuario->apellidos ?? '') }}" required
                class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium">
            @error('apellidos') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Correo Electrónico</label>
            <input type="email" name="email" id="email" value="{{ old('email', $usuario->email ?? '') }}" required
                class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium">
            @error('email') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="cedula" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Cédula</label>
            <input type="text" name="cedula" id="cedula" value="{{ old('cedula', $usuario->cedula ?? '') }}" required
                class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium">
            @error('cedula') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="role" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Rol del Usuario</label>
            <select name="role" id="role" required
                class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none appearance-none font-medium cursor-pointer">
                <option value="paciente" {{ old('role', $usuario->role ?? '') === 'paciente' ? 'selected' : '' }}>Paciente</option>
                <option value="psicologo" {{ old('role', $usuario->role ?? '') === 'psicologo' ? 'selected' : '' }}>Psicólogo</option>
                <option value="admin" {{ old('role', $usuario->role ?? '') === 'admin' ? 'selected' : '' }}>Administrador</option>
            </select>
            @error('role') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>
        
        <div>
            <label for="genero" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Género</label>
            <select name="genero" id="genero" class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                <option value="">Seleccione</option>
                <option value="Masculino" {{ old('genero', $usuario->genero ?? '') === 'Masculino' ? 'selected' : '' }}>Masculino</option>
                <option value="Femenino" {{ old('genero', $usuario->genero ?? '') === 'Femenino' ? 'selected' : '' }}>Femenino</option>
            </select>
            @error('genero') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="fecha_nacimiento" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Fecha de Nacimiento</label>
            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento', $usuario->fecha_nacimiento ?? '') }}" 
                class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
            @error('fecha_nacimiento') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="telefono" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Teléfono</label>
            <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $usuario->telefono ?? '') }}" 
                class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
            @error('telefono') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="estado_civil" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Estado Civil</label>
            <select name="estado_civil" id="estado_civil" class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                <option value="">Seleccione</option>
                <option value="Soltero(a)" {{ old('estado_civil', $usuario->estado_civil ?? '') === 'Soltero(a)' ? 'selected' : '' }}>Soltero(a)</option>
                <option value="Casado(a)" {{ old('estado_civil', $usuario->estado_civil ?? '') === 'Casado(a)' ? 'selected' : '' }}>Casado(a)</option>
                <option value="Divorciado(a)" {{ old('estado_civil', $usuario->estado_civil ?? '') === 'Divorciado(a)' ? 'selected' : '' }}>Divorciado(a)</option>
                <option value="Viudo(a)" {{ old('estado_civil', $usuario->estado_civil ?? '') === 'Viudo(a)' ? 'selected' : '' }}>Viudo(a)</option>
            </select>
            @error('estado_civil') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label for="ubicacion" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Dirección de Habitación</label>
            <input type="text" name="ubicacion" id="ubicacion" value="{{ old('ubicacion', $usuario->ubicacion ?? '') }}" 
                class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
            @error('ubicacion') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        <div x-data="{ discapacidad: '{{ old('discapacidad', $usuario->discapacidad ?? 'No') }}' }">
            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">¿Posee alguna discapacidad?</label>
            <select name="discapacidad" x-model="discapacidad" class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                <option value="Si">Sí</option>
                <option value="No">No</option>
            </select>
            <div x-show="discapacidad === 'Si'" class="mt-3">
                <input type="text" name="tipo_discapacidad" placeholder="Especifique la discapacidad" value="{{ old('tipo_discapacidad', $usuario->tipo_discapacidad ?? '') }}" 
                    class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
            </div>
            @error('discapacidad') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        <div x-data="{ tiene_hijos: '{{ old('tiene_hijos', $usuario->tiene_hijos ?? 'No') }}' }">
            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">¿Tiene hijos?</label>
            <select name="tiene_hijos" x-model="tiene_hijos" class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                <option value="Si">Sí</option>
                <option value="No">No</option>
            </select>
            <div x-show="tiene_hijos === 'Si'" class="mt-3">
                <input type="number" name="numero_hijos" placeholder="Cantidad de hijos" min="1" value="{{ old('numero_hijos', $usuario->numero_hijos ?? '') }}" 
                    class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
            </div>
            @error('tiene_hijos') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        <div x-data="{ perfil: '{{ old('perfil_academico', $usuario->perfil_academico ?? '') }}' }">
            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Perfil Académico</label>
            <select name="perfil_academico" x-model="perfil" class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                <option value="">Seleccione</option>
                <option value="Estudiante">Estudiante</option>
                <option value="Profesor">Profesor</option>
                <option value="Obrero">Obrero</option>
                <option value="Administrativo">Administrativo</option>
                <option value="Pre-escolar">Pre-escolar</option>
                <option value="Otros">Otros</option>
            </select>
            
            <div x-show="perfil === 'Estudiante'" class="mt-3 space-y-3">
                <select name="pnf" class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                    <option value="">Seleccione el PNF</option>
                    @foreach(['Agroalimentación','Contaduría','Informática','Mantenimiento','Electricidad','Mecánica','Procesos Químicos','Electrónica'] as $pnf)
                        <option value="{{ $pnf }}" {{ old('pnf', $usuario->pnf ?? '') === $pnf ? 'selected' : '' }}>{{ $pnf }}</option>
                    @endforeach
                </select>
                <select name="semestre" class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                    <option value="">Seleccione el Semestre</option>
                    @for($s=1; $s<=12; $s++)
                        <option value="{{ $s }}" {{ old('semestre', $usuario->semestre ?? '') == $s ? 'selected' : '' }}>{{ $s }}º Semestre</option>
                    @endfor
                </select>
            </div>
            @error('perfil_academico') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Contraseña @if(isset($usuario)) (Opcional) @endif</label>
            <div class="relative">
                <input type="text" name="password" id="password" x-model="password" @if(!isset($usuario)) required @endif
                    class="w-full px-5 py-3 bg-indigo-50/30 border border-indigo-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-mono font-bold text-indigo-700"
                    placeholder="@if(isset($usuario)) Dejar en blanco para no cambiar @endif">
                <button type="button" @click="generatePassword()" class="absolute right-2 top-1.5 p-1.5 text-indigo-400 hover:text-indigo-600 hover:bg-white rounded-xl transition-all" title="Regenerar Contraseña">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
            </div>
            <p class="text-[10px] text-slate-400 mt-2 leading-tight">
                Cumple requisitos de seguridad (Mínimo 8 caracteres, al menos una mayúscula, un número y un símbolo especial).
            </p>
            @error('password') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
