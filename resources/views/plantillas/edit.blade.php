<x-app-layout>
    <div class="py-12 bg-slate-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8 flex items-center gap-4">
                <a href="{{ route('plantillas.index') }}" class="w-10 h-10 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center text-slate-500 dark:text-gray-400 shadow-sm border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Editar Plantilla</h2>
                    <p class="text-sm font-bold text-slate-500 dark:text-gray-400 mt-1 uppercase tracking-widest">{{ $plantilla->titulo }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-[32px] p-8 shadow-sm border border-slate-100 dark:border-gray-700" x-data="{
                numCampos: {{ count($plantilla->segmentos) > 0 ? count($plantilla->segmentos) : 1 }},
                segmentos: {{ json_encode(count($plantilla->segmentos) > 0 ? $plantilla->segmentos : ['']) }},
                mostrarMensaje: false,
                actualizarSegmentos() {
                    let n = parseInt(this.numCampos);
                    if (n < 1) n = 1;
                    if (n > 4) n = 4;
                    this.numCampos = n;

                    while(this.segmentos.length < this.numCampos) {
                        this.segmentos.push('');
                    }
                    if(this.segmentos.length > this.numCampos) {
                        this.segmentos = this.segmentos.slice(0, this.numCampos);
                    }

                    if (n >= 4) {
                        this.mostrarMensaje = true;
                        setTimeout(() => {
                            this.mostrarMensaje = false;
                        }, 3000);
                    }
                }
            }">
                <form action="{{ route('plantillas.update', $plantilla->id) }}" method="POST">
                    @csrf
                    @method('PATCH')


                    <div class="space-y-6">
                        <!-- Título -->
                        <div>
                            <label class="block text-xs font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Título de la sección <span class="text-rose-500">*</span></label>
                            <input type="text" name="titulo" required class="w-full bg-slate-50 dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-gray-900 dark:text-white" placeholder="Ej: Prueba de Inteligencia" value="{{ old('titulo', $plantilla->titulo) }}">
                            @error('titulo') <span class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label class="block text-xs font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Descripción General (Opcional)</label>
                            <input type="text" name="descripcion_general" class="w-full bg-slate-50 dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-gray-900 dark:text-white" placeholder="Ej: Evaluación cognitiva detallada" value="{{ old('descripcion_general', $plantilla->descripcion_general) }}">
                        </div>

                        <hr class="border-slate-100 dark:border-gray-700 my-8">

                        <!-- Campos (Segmentos) -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <label class="block text-xs font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">¿Cuántos campos (segmentos)?</label>
                                <div class="flex items-center gap-3">
                                    <div x-show="mostrarMensaje"
                                         x-transition:enter="transition ease-out duration-300"
                                         x-transition:enter-start="opacity-0 transform translate-x-4"
                                         x-transition:enter-end="opacity-100 transform translate-x-0"
                                         x-transition:leave="transition ease-in duration-300"
                                         x-transition:leave-start="opacity-100 transform translate-x-0"
                                         x-transition:leave-end="opacity-0 transform translate-x-4"
                                         style="display: none;" 
                                         class="bg-amber-50 dark:bg-amber-900/80 border border-amber-200 dark:border-amber-700 text-amber-700 dark:text-amber-200 px-4 py-1.5 rounded-xl flex items-center gap-2">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        <span class="text-xs font-bold">Máximo 4 campos por sección.</span>
                                    </div>
                                    <div class="flex items-center gap-2 bg-slate-50 dark:bg-gray-700 p-1 rounded-xl border border-slate-200 dark:border-gray-600">
                                        <button type="button" @click="if(numCampos > 1) { numCampos--; actualizarSegmentos(); }" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300 shadow-sm hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">-</button>
                                        <input type="number" min="1" max="4" x-model="numCampos" @change="actualizarSegmentos()" class="w-12 h-8 text-center bg-transparent border-none text-slate-700 dark:text-gray-200 font-bold focus:ring-0 p-0">
                                        <button type="button" @click="if(numCampos < 4) { numCampos++; actualizarSegmentos(); }" :class="{'opacity-50 cursor-not-allowed': numCampos >= 4}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300 shadow-sm hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(seg, index) in segmentos" :key="index">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0" x-text="index + 1"></div>
                                        <input type="text" x-model="segmentos[index]" :name="'segmentos['+index+']'" required class="w-full bg-slate-50 dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-sm text-gray-900 dark:text-white" placeholder="Título del campo">
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="pt-6 flex justify-end gap-3 mt-8">
                            <a href="{{ route('plantillas.index') }}" class="px-6 py-2.5 bg-slate-50 dark:bg-gray-700 text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-600 font-bold rounded-xl transition-colors text-sm flex items-center justify-center">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md shadow-indigo-200 dark:shadow-indigo-900/30 transition-all active:scale-[0.98] text-sm flex items-center justify-center gap-2">
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
