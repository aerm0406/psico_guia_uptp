<x-app-layout>
    {{-- ================================Vista de Crear Bloque de Horario================================== --}}
    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl rounded-3xl border-t-8 border-blue-400 overflow-hidden">
                <div class="p-6 space-y-4">
                    <h3 class="text-2xl font-bold text-blue-900 mb-2">Crear Bloque de horario</h3>
                    {{-- ================================Mensaje Informativo================================== --}}
                    <p class="text-gray-500 text-sm italic">Configura los bloques de tiempo en los que atenderás a los estudiantes. Selecciona los días y horas disponibles.</p>
                    {{-- ================================Inclusión del Formulario================================== --}}
                    @include('horarios.form', ['dias' => $dias, 'grupoRetorno' => $grupoRetorno ?? null])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
