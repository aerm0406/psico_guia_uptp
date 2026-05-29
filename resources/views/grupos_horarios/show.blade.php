<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Grupo: ') . $grupoHorario->nombre }}
            </h2>
                <div class="flex space-x-2">
                    @if(!empty($tieneCitasPendientes))
                        <button type="button" disabled class="bg-blue-500 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed" title="No puedes editar mientras tengas citas pendientes">
                            Editar Grupo
                        </button>
                    @else
                        <a href="{{ route('grupos_horarios.edit', $grupoHorario->id) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Editar Grupo
                        </a>
                    @endif
                    <a href="{{ route('grupos_horarios.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        ← Volver
                    </a>
                </div>
            </div>
            <div class="bg-white shadow-xl rounded-3xl border-t-8 border-blue-600 overflow-hidden">
                <div class="p-6">
                    {{-- ================================Estado del Grupo================================== --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Estado del Grupo</h3>
                        <span class="px-3 py-1 rounded {{ $grupoHorario->activo == \App\Models\GrupoHorario::STATUS_ACTIVE ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-800' }}">
                            {{ $grupoHorario->activo == \App\Models\GrupoHorario::STATUS_ACTIVE ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    {{-- ================================Calendario Semanal================================== --}}
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        @foreach ($horariosPorDia as $dia => $horariosDia)
                            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3 text-center">{{ $dia }}</h4>
                                @if ($horariosDia->isEmpty())
                                    <p class="text-sm text-gray-500 text-center">Sin bloques</p>
                                @else
                                    <div class="space-y-2">
                                        @foreach ($horariosDia as $horario)
                                            <div class="relative bg-white p-3 rounded-xl border border-gray-200 text-center {{ $horario->activo == \App\Models\Horario::STATUS_INACTIVE ? 'opacity-50' : '' }}">
                                                <div class="mb-2">
                                                    <span class="text-sm font-semibold">{{ $horario->hora_inicio }} - {{ $horario->hora_fin }}</span>
                                                </div>
                                                @if($horario->descripcion)
                                                    <p class="text-sm text-gray-500">{{ $horario->descripcion }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- ================================Acciones================================== --}}
                    <div class="mt-6 flex space-x-4">
                        @if($grupoHorario->activo != \App\Models\GrupoHorario::STATUS_ACTIVE)
                            @if(!empty($tieneCitasPendientes))
                                <button type="button" disabled class="bg-green-500 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed" title="No puedes activar mientras tengas citas pendientes">
                                    Activar Grupo
                                </button>
                            @else
                                <form action="{{ route('grupos_horarios.activate', $grupoHorario->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Activar Grupo
                                    </button>
                                </form>
                            @endif
                        @else
                            @if(!empty($tieneCitasPendientes))
                                <button type="button" disabled class="bg-yellow-500 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed" title="No puedes desactivar mientras tengas citas pendientes">
                                    Desactivar Grupo
                                </button>
                            @else
                                <form action="{{ route('grupos_horarios.deactivate', $grupoHorario->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                        Desactivar Grupo
                                    </button>
                                </form>
                            @endif
                        @endif

                        @if(!empty($tieneCitasPendientes))
                            <button type="button" disabled class="bg-red-500 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed" title="No puedes eliminar mientras tengas citas pendientes">
                                Eliminar Grupo
                            </button>
                        @else
                            <form action="{{ route('grupos_horarios.destroy', $grupoHorario->id) }}" method="POST" data-ajax-reload="true" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este grupo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    Eliminar Grupo
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleAjaxReloadForm(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                if (form.dataset.ajaxReload !== 'true') {
                    return;
                }

                var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!token) {
                    alert('No se pudo obtener CSRF token. Recarga la página e inténtalo de nuevo.');
                    return;
                }

                var formData = new FormData(form);
                if (form.method.toUpperCase() === 'POST' && form.querySelector('input[name="_method"]')) {
                    formData.set('_method', form.querySelector('input[name="_method"]').value);
                }

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: formData
                }).then(function(response) {
                    if (!response.ok) {
                        return response.json().then(function(body) {
                            throw new Error(body.message || 'No se pudo completar la acción.');
                        });
                    }
                    return response.json();
                }).then(function(result) {
                    if (result && result.status === 'success') {
                        window.location.reload();
                    } else {
                        throw new Error(result.message || 'No se pudo completar la acción.');
                    }
                }).catch(function(error) {
                    console.error('Error en acción de horario:', error);
                    alert(error.message || 'Error al procesar la acción. Recarga la página e inténtalo nuevamente.');
                });
            });
        }

        document.querySelectorAll('form[data-ajax-reload="true"]').forEach(handleAjaxReloadForm);
    </script>
</x-app-layout>