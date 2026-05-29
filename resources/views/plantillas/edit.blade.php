<x-app-layout>
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-8 flex items-center gap-4">
                <a href="{{ route('plantillas.index') }}" class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-slate-500 shadow-sm border border-slate-200 hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Editar Plantilla</h2>
                    <p class="text-sm font-bold text-slate-500 mt-1 uppercase tracking-widest">{{ $plantilla->titulo }}</p>
                </div>
            </div>

            <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100" x-data="{
                numCampos: {{ count($plantilla->segmentos) > 0 ? count($plantilla->segmentos) : 1 }},
                segmentos: {{ json_encode(count($plantilla->segmentos) > 0 ? $plantilla->segmentos : ['']) }},
                actualizarSegmentos() {
                    let n = parseInt(this.numCampos);
                    if (n < 1) n = 1;
                    if (n > 10) n = 10;
                    this.numCampos = n;
                    
                    while(this.segmentos.length < this.numCampos) {
                        this.segmentos.push('');
                    }
                    if(this.segmentos.length > this.numCampos) {
                        this.segmentos = this.segmentos.slice(0, this.numCampos);
                    }
                }
            }">
                <form action="{{ route('plantillas.update', $plantilla->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    
                    <div class="space-y-6">
                        <!-- Título -->
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Título de la sección <span class="text-rose-500">*</span></label>
                            <input type="text" name="titulo" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow" placeholder="Ej: Prueba de Inteligencia" value="{{ old('titulo', $plantilla->titulo) }}">
                            @error('titulo') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Descripción General (Opcional)</label>
                            <input type="text" name="descripcion_general" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow" placeholder="Ej: Evaluación cognitiva detallada" value="{{ old('descripcion_general', $plantilla->descripcion_general) }}">
                        </div>

                        <hr class="border-slate-100 my-8">

                        <!-- Campos (Segmentos) -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">¿Cuántos campos (segmentos)?</label>
                                <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-xl border border-slate-200">
                                    <button type="button" @click="if(numCampos > 1) { numCampos--; actualizarSegmentos(); }" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white text-slate-600 shadow-sm hover:bg-indigo-50 hover:text-indigo-600 transition-colors">-</button>
                                    <input type="number" min="1" max="10" x-model="numCampos" @change="actualizarSegmentos()" class="w-12 h-8 text-center bg-transparent border-none text-slate-700 font-bold focus:ring-0 p-0">
                                    <button type="button" @click="if(numCampos < 10) { numCampos++; actualizarSegmentos(); }" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white text-slate-600 shadow-sm hover:bg-indigo-50 hover:text-indigo-600 transition-colors">+</button>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(seg, index) in segmentos" :key="index">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs shrink-0" x-text="index + 1"></div>
                                        <input type="text" x-model="segmentos[index]" :name="'segmentos['+index+']'" required class="w-full bg-slate-50 border-slate-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-sm" placeholder="Título del campo">
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="pt-6 flex justify-end gap-3 mt-8">
                            <a href="{{ route('plantillas.index') }}" class="px-6 py-2.5 bg-slate-50 text-slate-600 hover:bg-slate-100 font-bold rounded-xl transition-colors text-sm flex items-center justify-center">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md shadow-indigo-200 transition-all active:scale-[0.98] text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Actualizar Plantilla
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
