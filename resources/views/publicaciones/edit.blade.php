<x-app-layout>
    <div class="pt-12 pb-4 bg-slate-50 dark:bg-gray-900 min-h-[calc(100vh-4rem)] flex flex-col">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex-1 flex flex-col w-full">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-3xl border border-gray-100 dark:border-gray-700">
                <div class="p-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Editar Aviso</h3>

                    <form action="{{ route('publicaciones.update', $publicacion->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="titulo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Título / Texto Principal</label>
                            <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $publicacion->titulo) }}" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>

                        <div class="mb-6">
                            <label for="contenido" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Contenido Adicional (Opcional)</label>
                            <textarea name="contenido" id="contenido" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('contenido', $publicacion->contenido) }}</textarea>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Visibilidad</label>
                            <select name="alcance" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="todos" {{ $publicacion->alcance === 'todos' ? 'selected' : '' }}>Todos los pacientes del sistema</option>
                                <option value="mis_pacientes" {{ $publicacion->alcance === 'mis_pacientes' ? 'selected' : '' }}>Solo mis pacientes activos</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-3 mt-8">
                            <a href="{{ route('publicaciones.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancelar</a>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition shadow-sm">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
