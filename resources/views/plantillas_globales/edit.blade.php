<x-app-layout>
    <div class="py-12 bg-slate-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8 flex items-center gap-4">
                <a href="{{ route('plantillas-globales.index') }}" class="w-10 h-10 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center text-slate-500 dark:text-gray-400 shadow-sm border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Editar Plantilla Global</h2>
                    <p class="text-sm font-bold text-slate-500 dark:text-gray-400 mt-1 uppercase tracking-widest">{{ $plantilla->titulo }}</p>
                </div>
            </div>

            @php
                $seccionesData = $plantilla->secciones_decoded ?? [];
                // Preparar datos para Alpine.js
                $seccionesAlpine = [];
                foreach ($seccionesData as $seccion) {
                    $segs = $seccion['segmentos'] ?? [''];
                    if (empty($segs)) $segs = [''];
                    $seccionesAlpine[] = [
                        'titulo' => $seccion['titulo'] ?? '',
                        'descripcion_general' => $seccion['descripcion_general'] ?? '',
                        'numCampos' => count($segs),
                        'segmentos' => $segs,
                    ];
                }
                if (empty($seccionesAlpine)) {
                    $seccionesAlpine = [['titulo' => '', 'descripcion_general' => '', 'numCampos' => 1, 'segmentos' => ['']]];
                }
            @endphp

            <div class="bg-white dark:bg-gray-800 rounded-[32px] p-8 shadow-sm border border-slate-100 dark:border-gray-700" x-data="{
                secciones: @js($seccionesAlpine),

                agregarSeccion() {
                    this.secciones.push({
                        titulo: '',
                        descripcion_general: '',
                        numCampos: 1,
                        segmentos: ['']
                    });
                },

                eliminarSeccion(index) {
                    if (this.secciones.length > 1) {
                        this.secciones.splice(index, 1);
                    }
                },

                actualizarSegmentos(seccion) {
                    let n = parseInt(seccion.numCampos);
                    if (n < 1) n = 1;
                    if (n > 10) n = 10;
                    seccion.numCampos = n;

                    while (seccion.segmentos.length < n) {
                        seccion.segmentos.push('');
                    }
                    if (seccion.segmentos.length > n) {
                        seccion.segmentos = seccion.segmentos.slice(0, n);
                    }
                }
            }">
                <form action="{{ route('plantillas-globales.update', $plantilla->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        {{-- Título y descripción de la plantilla --}}
                        <div>
                            <label class="block text-xs font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Nombre de la Plantilla <span class="text-rose-500">*</span></label>
                            <input type="text" name="titulo" required class="w-full bg-slate-50 dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-gray-900 dark:text-white" placeholder="Ej: Evaluación Psicológica Integral" value="{{ old('titulo', $plantilla->titulo) }}">
                            @error('titulo') <span class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Descripción General (Opcional)</label>
                            <input type="text" name="descripcion" class="w-full bg-slate-50 dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-gray-900 dark:text-white" placeholder="Ej: Plantilla estándar para evaluación inicial" value="{{ old('descripcion', $plantilla->descripcion) }}">
                        </div>

                        <hr class="border-slate-100 dark:border-gray-700 my-8">

                        {{-- Título de secciones --}}
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Secciones del Historial</h3>
                                <p class="text-xs text-slate-400 dark:text-gray-500 font-bold uppercase tracking-widest">Cada sección contiene campos (segmentos) editables</p>
                            </div>
                            <button type="button" @click="agregarSeccion()" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Agregar Sección
                            </button>
                        </div>

                        {{-- Lista de secciones --}}
                        <div class="space-y-6">
                            <template x-for="(seccion, secIndex) in secciones" :key="secIndex">
                                <div class="bg-slate-50 dark:bg-gray-700/50 rounded-2xl p-6 border border-slate-200 dark:border-gray-600 relative">
                                    {{-- Header de la sección --}}
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-violet-100 dark:bg-violet-900/40 text-violet-600 dark:text-violet-400 rounded-xl flex items-center justify-center font-black text-xs" x-text="secIndex + 1"></div>
                                            <span class="text-xs font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Sección</span>
                                        </div>
                                        <button type="button" @click="eliminarSeccion(secIndex)" x-show="secciones.length > 1" class="p-2 text-rose-400 dark:text-rose-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-xl transition-all" title="Eliminar sección">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>

                                    {{-- Título de la sección --}}
                                    <div class="mb-4">
                                        <label class="block text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Título de la Sección <span class="text-rose-500">*</span></label>
                                        <input type="text" :name="'secciones_estructura['+secIndex+'][titulo]'" x-model="seccion.titulo" required
                                               class="w-full bg-white dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-sm font-bold text-gray-900 dark:text-white"
                                               placeholder="Ej: Antecedentes Personales">
                                    </div>

                                    {{-- Descripción de la sección --}}
                                    <div class="mb-4">
                                        <label class="block text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Descripción (Opcional)</label>
                                        <input type="text" :name="'secciones_estructura['+secIndex+'][descripcion_general]'" x-model="seccion.descripcion_general"
                                               class="w-full bg-white dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-sm text-gray-900 dark:text-white"
                                               placeholder="Ej: Historial médico y psicológico del paciente">
                                    </div>

                                    <hr class="border-slate-200 dark:border-gray-600 my-4">

                                    {{-- Segmentos --}}
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <label class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Campos (Segmentos)</label>
                                            <div class="flex items-center gap-2 bg-white dark:bg-gray-700 p-1 rounded-xl border border-slate-200 dark:border-gray-600">
                                                <button type="button" @click="if(seccion.numCampos > 1) { seccion.numCampos--; actualizarSegmentos(seccion); }" class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-50 dark:bg-gray-800 text-slate-600 dark:text-gray-300 shadow-sm hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors text-xs font-bold">-</button>
                                                <span class="w-6 text-center text-xs font-black text-slate-700 dark:text-gray-200" x-text="seccion.numCampos"></span>
                                                <button type="button" @click="if(seccion.numCampos < 10) { seccion.numCampos++; actualizarSegmentos(seccion); }" class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-50 dark:bg-gray-800 text-slate-600 dark:text-gray-300 shadow-sm hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors text-xs font-bold">+</button>
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <template x-for="(seg, segIndex) in seccion.segmentos" :key="segIndex">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-[10px] shrink-0" x-text="segIndex + 1"></div>
                                                    <input type="text" x-model="seccion.segmentos[segIndex]" :name="'secciones_estructura['+secIndex+'][segmentos]['+segIndex+']'" required
                                                           class="w-full bg-white dark:bg-gray-700 border-slate-200 dark:border-gray-600 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-shadow text-xs text-gray-900 dark:text-white"
                                                           placeholder="Título del campo">
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Submit --}}
                        <div class="pt-6 flex justify-end gap-3 mt-8">
                            <a href="{{ route('plantillas-globales.index') }}" class="px-6 py-2.5 bg-slate-50 dark:bg-gray-700 text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-600 font-bold rounded-xl transition-colors text-sm flex items-center justify-center">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md shadow-indigo-200 dark:shadow-indigo-900/30 transition-all active:scale-[0.98] text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Actualizar Plantilla
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
