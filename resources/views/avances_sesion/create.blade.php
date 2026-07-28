<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-12 bg-slate-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-8 flex items-center gap-4">
                <a href="{{ route('avances_sesion.index') }}" class="w-10 h-10 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center text-slate-500 dark:text-gray-400 shadow-sm border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Nuevo Avance de Sesión</h2>
                    <p class="text-sm font-bold text-slate-500 dark:text-gray-400 mt-1 uppercase tracking-widest">Crear nueva opción de avance</p>
                </div>
            </div>

            @if (session('error'))
                <div class="p-4 mb-6 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800 flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span><strong class="font-black uppercase tracking-wider text-[10px] block mb-0.5">Error</strong>{{ session('error') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="p-4 mb-6 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                    <strong class="font-black uppercase tracking-wider text-[10px] block mb-2">Por favor corrige los siguientes errores:</strong>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-[32px] p-8 shadow-sm border border-slate-100 dark:border-gray-700">
                <form action="{{ route('avances_sesion.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="nombre" class="block text-xs font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Nombre del Avance <span class="text-rose-500">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="w-full bg-slate-50 dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-gray-900 dark:text-white" required placeholder="Ej: Mejoría significativa" value="{{ old('nombre') }}">
                    </div>

                    <div>
                        <label for="valor" class="block text-xs font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Valor Numérico (1-10) <span class="text-rose-500">*</span></label>
                        <input type="number" name="valor" id="valor" min="1" max="10" class="w-full bg-slate-50 dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-gray-900 dark:text-white" required placeholder="Ej: 5" value="{{ old('valor') }}">
                        <p class="text-[10px] text-slate-500 mt-1">Este valor se usará para representar el progreso en las estadísticas.</p>
                    </div>
                    
                    <div>
                        <label for="descripcion" class="block text-xs font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Descripción (Opcional)</label>
                        <textarea name="descripcion" id="descripcion" rows="4" class="w-full bg-slate-50 dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-gray-900 dark:text-white" placeholder="Detalles sobre este avance...">{{ old('descripcion') }}</textarea>
                    </div>

                    <div class="pt-6 flex justify-end gap-3 mt-8 border-t border-slate-100 dark:border-gray-700">
                        <a href="{{ route('avances_sesion.index') }}" class="px-6 py-2.5 bg-slate-50 dark:bg-gray-700 text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-600 font-bold rounded-xl transition-colors text-sm flex items-center justify-center">
                            Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md shadow-indigo-200 dark:shadow-indigo-900/30 transition-all active:scale-[0.98] text-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
