<x-app-layout>
    {{-- ================================Vista de Editar Bloque de Horario================================== --}}
    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl rounded-3xl border-t-8 border-blue-400 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Editar bloque de horario</h3>
                    {{-- ================================Inclusión del Formulario de Edición================================== --}}
                    @include('horarios.form', ['horario' => $horario, 'dias' => $dias, 'grupoRetorno' => $grupoRetorno ?? null])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
