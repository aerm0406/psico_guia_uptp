1<x-app-layout>
    <div class="pt-12 pb-4 bg-slate-50 dark:bg-gray-900 min-h-[calc(100vh-4rem)] flex flex-col">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex-1 flex flex-col w-full">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-3xl border border-gray-100 dark:border-gray-700">
                <div class="p-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Crear Nuevo Aviso</h3>

                    <form action="{{ route('publicaciones.store') }}" method="POST" enctype="multipart/form-data" x-data="{ tipo: 'texto', colorFondo: 'bg-blue-500' }">
                        @csrf

                        <!-- Tipo de Publicación -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tipo de Publicación</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="tipo" value="texto" x-model="tipo" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 transition-all" :class="tipo === 'texto' ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300'">
                                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                        <span class="text-xs font-medium">Texto Normal</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="tipo" value="color" x-model="tipo" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 transition-all" :class="tipo === 'color' ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300'">
                                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                        <span class="text-xs font-medium">Color de Fondo</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="tipo" value="imagen" x-model="tipo" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 transition-all" :class="tipo === 'imagen' ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300'">
                                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-xs font-medium">Subir Imagen</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Selector de Color (sólo si es tipo color) -->
                        <div x-show="tipo === 'color'" x-transition class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Elige un color de fondo</label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="color in ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-pink-500', 'bg-red-500', 'bg-yellow-500', 'bg-gray-800']">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="color_fondo" :value="color" x-model="colorFondo" class="sr-only">
                                        <div :class="[color, colorFondo === color ? 'ring-2 ring-offset-2 ring-blue-500 dark:ring-offset-gray-800' : '']" class="w-10 h-10 rounded-full shadow-sm hover:scale-110 transition-transform"></div>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Subir Imagen (sólo si es tipo imagen) -->
                        <div x-show="tipo === 'imagen'" x-transition class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Sube tu Imagen</label>
                            <p class="text-xs text-gray-500 mb-3">(El peso máximo permitido es de 2MB)</p>
                            <input type="file" name="imagen" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                        </div>

                        <div class="mb-4">
                            <label for="titulo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Título / Texto Principal</label>
                            <input type="text" name="titulo" id="titulo" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>

                        <div class="mb-6">
                            <label for="contenido" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Contenido Adicional (Opcional)</label>
                            <textarea name="contenido" id="contenido" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Visibilidad</label>
                            <select name="alcance" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="todos">Todos los pacientes del sistema</option>
                                <option value="mis_pacientes">Solo mis pacientes activos</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-3 mt-8">
                            <a href="{{ route('publicaciones.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancelar</a>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition shadow-sm">
                                Publicar Aviso
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
