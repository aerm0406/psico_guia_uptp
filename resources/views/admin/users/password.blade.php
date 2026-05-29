<x-app-layout>
    <div class="py-12 bg-slate-50 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-sm font-bold text-slate-400 hover:text-indigo-600 transition-colors mb-6 group">
                <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Volver al listado
            </a>

            <div class="mb-10">
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">Cambiar Contraseña</h1>
                <p class="mt-2 text-slate-500 font-medium tracking-tight">
                    Modificando acceso para: <span class="text-indigo-600 font-bold">{{ $usuario->name }}</span> 
                   {{-- <span class="text-slate-400 ml-1">({{ $usuario->email }})</span>--}}
                </p>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden">
                <form action="{{ route('admin.users.password.update', $usuario->id) }}" method="POST" class="p-10">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Nueva Contraseña --}}
                            <div>
                                <label for="password" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Nueva Contraseña</label>
                                <input type="password" name="password" id="password" required minlength="8"
                                    class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium"
                                    placeholder="Mínimo 8 caracteres">
                                @error('password') <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p> @enderror
                            </div>

                            {{-- Confirmar Contraseña --}}
                            <div>
                                <label for="password_confirmation" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Confirmar Contraseña</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                                    class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-medium"
                                    placeholder="Repite la contraseña">
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-50 flex flex-col md:flex-row items-center justify-end gap-4">
                            <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all">
                                Actualizar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Info Alert --}}
            <div class="mt-8 p-6 bg-amber-50 rounded-[2rem] border border-amber-100 flex gap-4">
                <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-xl flex-shrink-0 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h4 class="text-sm font-black text-amber-800 uppercase tracking-tight mb-1">Seguridad</h4>
                    <p class="text-xs text-amber-700 font-medium leading-relaxed">
                        Asegúrate de proporcionar al usuario sus nuevas credenciales una vez actualizada la contraseña. El sistema no enviará notificaciones automáticas en este entorno de desarrollo.
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
