<div class="space-y-8" x-data="{ 
    password: '{{ $password ?? '' }}',
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
    {{-- Sección: Datos y Cuenta --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
        {{-- Fila 1: Nombres y Apellidos --}}
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

        {{-- Fila 2: Email y Rol --}}
        <div>
            <label for="email" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Correo Electrónico (Login)</label>
            <input type="email" name="email" id="email" value="{{ old('email', $usuario->email ?? '') }}" required
                class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium"
                placeholder="usuario@ejemplo.com">
            @error('email') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
            <p class="text-[10px] text-indigo-500 mt-2 font-black uppercase tracking-tight">Este correo será su usuario de acceso.</p>
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
        

        {{-- Fila 3: Cédula y Contraseña --}}
        <div>
            <label for="cedula" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Cédula (Opcional)</label>
            <input type="text" name="cedula" id="cedula" value="{{ old('cedula', $usuario->cedula ?? '') }}"
                class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium"
                placeholder="V-12345678">
            @error('cedula') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        @if(!isset($usuario))
            <div>
                <label for="password" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Contraseña Temporal</label>
                <div class="relative">
                    <input type="text" name="password" id="password" x-model="password" required
                        class="w-full px-5 py-3 bg-indigo-50/30 border border-indigo-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-mono font-bold text-indigo-700">
                    <button type="button" @click="generatePassword()" class="absolute right-2 top-1.5 p-1.5 text-indigo-400 hover:text-indigo-600 hover:bg-white rounded-xl transition-all" title="Regenerar Contraseña">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>
                </div>
                <p class="text-[10px] text-slate-400 mt-2 leading-tight">
                    Cumple requisitos de seguridad. El admin puede editarla o regenerarla.
                </p>
                @error('password') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
            </div>
        @else
            <div class="flex items-end pb-3">
                <p class="text-[10px] text-slate-400 font-medium italic">La cédula ya no es necesaria para iniciar sesión.</p>
            </div>
        @endif
    </div>
</div>
</div>
