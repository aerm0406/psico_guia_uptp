<x-app-layout>
    <div class="pt-12 pb-20 bg-slate-50 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs / Back Link -->
            <!-- Breadcrumbs / Back Link -->
            <a href="{{ route('enfermedades.index', ['tipo' => $tipo, 'return_to' => $returnTo, 'editing' => $editing]) }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-900 text-sm font-medium mb-8 transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Volver al listado
            </a>

            <!-- Header Section -->
            <div class="mb-10">
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Editar Enfermedad</h1>
                <p class="mt-2 text-slate-500 text-sm">Modifica los detalles de la condición médica o trastorno.</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden">
                <form action="{{ route('enfermedades.update', $enfermedad->id) }}" method="POST" class="p-8 md:p-12">
                    @csrf
                    @method('PUT')
                    
                    <!-- Mantener el contexto para el retorno -->
                    <input type="hidden" name="tipo_contexto" value="{{ $tipo }}">
                    <input type="hidden" name="return_to" value="{{ $returnTo }}">
                    <input type="hidden" name="editing" value="{{ $editing }}">

                    <div class="space-y-6">
                        <div>
                            <label for="nombre" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre de la Enfermedad</label>
                            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $enfermedad->nombre) }}" class="w-full h-12 px-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all placeholder:text-slate-300 text-sm font-medium" placeholder="Ej: Ansiedad, Asma, Depresión..." required>
                            @error('nombre') <p class="mt-2 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="categoria" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Categoría</label>
                                <select name="categoria" id="categoria" class="w-full h-12 px-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-medium" required>
                                    <option value="mental" {{ old('categoria', $enfermedad->categoria) == 'mental' ? 'selected' : '' }}>Psiquiátrica (Salud Mental)</option>
                                    <option value="biopsicosocial" {{ old('categoria', $enfermedad->categoria) == 'biopsicosocial' ? 'selected' : '' }}>Biopsicosocial</option>
                                    <option value="fisica" {{ old('categoria', $enfermedad->categoria) == 'fisica' ? 'selected' : '' }}>Médica (Salud General)</option>
                                </select>
                                @error('categoria') <p class="mt-2 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="variacion" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Variación / Tipo (Opcional)</label>
                                <input type="text" name="variacion" id="variacion" value="{{ old('variacion', $enfermedad->tipo) }}" class="w-full h-12 px-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all placeholder:text-slate-300 text-sm font-medium" placeholder="Ej: Crónica, Tipo 1, Grave...">
                                @error('variacion') <p class="mt-2 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="descripcion" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Descripción / Notas (Opcional)</label>
                            <textarea name="descripcion" id="descripcion" rows="4" class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all placeholder:text-slate-300 text-sm font-medium resize-none" placeholder="Breve descripción o notas adicionales...">{{ old('descripcion', $enfermedad->descripcion) }}</textarea>
                            @error('descripcion') <p class="mt-2 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-10 flex items-center justify-end gap-3 border-t border-slate-50 pt-8">
                        <a href="{{ route('enfermedades.index', ['tipo' => $tipo, 'return_to' => $returnTo, 'editing' => $editing]) }}" class="px-6 py-1.5 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="h-10 px-4 bg-indigo-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
