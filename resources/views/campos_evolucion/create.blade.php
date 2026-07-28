<x-app-layout>
    <div class="py-12 bg-slate-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8 flex items-center gap-4">
                <a href="{{ route('campos-evolucion.index') }}" class="w-10 h-10 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center text-slate-500 dark:text-gray-400 shadow-sm border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Crear Campo</h2>
                    <p class="text-sm font-bold text-slate-500 dark:text-gray-400 mt-1 uppercase tracking-widest">Añadir nuevo campo de evolución personalizado</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-[32px] p-8 shadow-sm border border-slate-100 dark:border-gray-700 max-w-2xl">
                <form action="{{ route('campos-evolucion.store') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        <!-- Título -->
                        <div>
                            <label class="block text-xs font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Título del campo <span class="text-rose-500">*</span></label>
                            <input type="text" name="titulo" required class="w-full bg-slate-50 dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-gray-900 dark:text-white" placeholder="Ej: Hábitos de sueño" value="{{ old('titulo') }}">
                            @error('titulo') <span class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Submit -->
                        <div class="pt-6 flex justify-end gap-3 mt-8">
                            <a href="{{ route('campos-evolucion.index') }}" class="px-6 py-2.5 bg-slate-50 dark:bg-gray-700 text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-600 font-bold rounded-xl transition-colors text-sm flex items-center justify-center">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md shadow-indigo-200 dark:shadow-indigo-900/30 transition-all active:scale-[0.98] text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Guardar Campo
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
