<x-app-layout>
    <div class="pt-12 pb-4 bg-slate-50 dark:bg-gray-900 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs / Back Link -->
            <a href="{{ route('enfermedades.index', ['tipo' => $tipo, 'return_to' => $returnTo, 'editing' => $editing]) }}" class="inline-flex items-center gap-2 text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-gray-200 text-sm font-medium mb-6 transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Volver al listado
            </a>

            <!-- Header -->
            <div class="mb-10">
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Nueva Enfermedad</h1>
                <p class="mt-2 text-slate-500 dark:text-gray-400 text-sm">Registra una nueva condición médica o trastorno para que esté disponible en las historias clínicas.</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white dark:bg-gray-800 rounded-[2rem] border border-slate-100 dark:border-gray-700 shadow-sm overflow-hidden text-slate-900 dark:text-gray-100">
                <form action="{{ route('enfermedades.store') }}" method="POST" class="p-8">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ $returnTo }}">
                    <input type="hidden" name="editing" value="{{ $editing }}">
                    <input type="hidden" name="tipo_contexto" value="{{ $tipo }}">

                    <div class="space-y-6">
                        <div>
                            <label for="nombre" class="block text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Nombre de la Enfermedad</label>
                            <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="w-full h-12 px-4 bg-slate-50 dark:bg-gray-700 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all placeholder:text-slate-300 dark:placeholder:text-gray-500 text-sm font-medium text-gray-900 dark:text-white" placeholder="Ej: Ansiedad, Asma, Depresión..." required>
                            @error('nombre') <p class="mt-2 text-xs text-rose-500 dark:text-rose-400 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="categoria" class="block text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Categoría</label>
                                <select name="categoria" id="categoria" class="w-full h-12 px-4 bg-slate-50 dark:bg-gray-700 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-medium text-gray-900 dark:text-white" required>
                                    <option value="mental" {{ (old('categoria', $tipo) == 'mental' || $tipo == 'pers_psq' || $tipo == 'fam_psq') ? 'selected' : '' }}>Psiquiátrica (Salud Mental)</option>
                                    <option value="biopsicosocial" {{ (old('categoria', $tipo) == 'biopsicosocial') ? 'selected' : '' }}>Biopsicosocial</option>
                                    <option value="fisica" {{ (old('categoria', $tipo) == 'fisica' || $tipo == 'pers_med' || $tipo == 'fam_med') ? 'selected' : '' }}>Médica (Salud General)</option>
                                </select>
                                @error('categoria') <p class="mt-2 text-xs text-rose-500 dark:text-rose-400 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="variacion" class="block text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Variación / Tipo (Opcional)</label>
                                <input type="text" name="variacion" id="variacion" value="{{ old('variacion') }}" class="w-full h-12 px-4 bg-slate-50 dark:bg-gray-700 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all placeholder:text-slate-300 dark:placeholder:text-gray-500 text-sm font-medium text-gray-900 dark:text-white" placeholder="Ej: Crónica, Tipo 1, Grave...">
                                @error('variacion') <p class="mt-2 text-xs text-rose-500 dark:text-rose-400 font-bold">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-3">Descripción / Notas (Opcional)</label>
                            <textarea name="descripcion" rows="4"
                                class="w-full bg-slate-50 dark:bg-gray-700 border-none rounded-2xl p-4 text-sm text-slate-600 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500/20 transition-all placeholder-slate-400 dark:placeholder-gray-500"
                                placeholder="Breve descripción o síntomas comunes...">{{ old('descripcion') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-10 flex items-center justify-end gap-3 border-t border-slate-50 dark:border-gray-700 pt-8">
                        <a href="{{ route('enfermedades.index', ['tipo' => $tipo, 'return_to' => $returnTo, 'editing' => $editing]) }}" class="px-6 py-2.5 text-sm font-bold text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-gray-200 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-100 dark:shadow-indigo-900/30 transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
