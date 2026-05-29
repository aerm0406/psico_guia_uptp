<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl rounded-3xl border-t-8 border-blue-600 overflow-hidden">
                <div class="p-6">
                    {{-- ================================Encabezado y Botones================================== --}}
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center gap-4">
                            <h3 class="text-2xl font-bold text-gray-800">
                                Grupos de Horarios
                            </h3>
                        </div>
                        <div class="flex gap-2 items-center">
                            <a href="{{ route('horarios.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-500 transition">
                                ← Volver
                            </a>

                            {{--
                            <a href="{{ route('grupos_horarios.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-full hover:bg-blue-700 transition">
                                + Crear
                            </a>
                            --}}
                        </div>
                    </div>



                    @if(!empty($tieneCitasPendientes))
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                            No puedes modificar grupos de horarios mientras tengas citas pendientes o confirmadas.
                        </div>
                    @endif

                    @if($grupos->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            @foreach($grupos as $grupo)
                                <div class="relative bg-white rounded-2xl border border-gray-200 shadow-sm p-5 hover:shadow-lg transition {{ $grupo->activo == \App\Models\GrupoHorario::STATUS_ACTIVE ? 'ring-2 ring-blue-400' : '' }}">
                                    <div class="flex justify-between items-start mb-3">
                                        <h3 class="text-xl font-bold text-gray-900">{{ $grupo->nombre }}</h3>
                                        <span class="text-xs font-semibold px-3 py-1 rounded-xl {{ $grupo->activo == \App\Models\GrupoHorario::STATUS_ACTIVE ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-600' }}">
                                            {{ $grupo->activo == \App\Models\GrupoHorario::STATUS_ACTIVE ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </div>

                                    <p class="text-sm text-gray-600 mb-4">
                                        <strong>{{ $grupo->horarios->count() }}</strong> bloques de horario
                                    </p>

                                    <div class="flex flex-wrap gap-2">


                                        @if($grupo->activo != \App\Models\GrupoHorario::STATUS_ACTIVE)
                                            @if(!empty($tieneCitasPendientes))
                                                <button type="button" disabled class="inline-flex items-center gap-1 text-white bg-emerald-400 px-3 py-2 rounded-full text-xs font-semibold opacity-50 cursor-not-allowed" title="No puedes activar mientras tengas citas pendientes">
                                                    Activar
                                                </button>
                                            @else
                                                <form action="{{ route('grupos_horarios.activate', $grupo->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center gap-1 text-white bg-emerald-400 hover:bg-emerald-500 px-3 py-2 rounded-full text-xs font-semibold">
                                                        Activar
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            @if(!empty($tieneCitasPendientes))
                                                <button type="button" disabled class="inline-flex items-center gap-1 text-white bg-amber-400 px-3 py-2 rounded-full text-xs font-semibold opacity-50 cursor-not-allowed" title="No puedes desactivar mientras tengas citas pendientes">
                                                    Desactivar
                                                </button>
                                            @else
                                                <form action="{{ route('grupos_horarios.deactivate', $grupo->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center gap-1 text-white bg-amber-400 hover:bg-amber-500 px-3 py-2 rounded-full text-xs font-semibold">
                                                        Desactivar
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        <button type="button" data-target="grupoModal-{{ $grupo->id }}" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-full w-8 h-8 transition" title="Ver" onclick="openGrupoModal(this)">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 4.5C4.833 4.5 1 12 1 12s3.833 7.5 11 7.5 11-7.5 11-7.5-3.833-7.5-11-7.5zm0 13c-3.037 0-5.5-2.463-5.5-5.5S8.963 6.5 12 6.5s5.5 2.463 5.5 5.5-2.463 5.5-5.5 5.5z" />
                                                <path d="M12 9.5a2.5 2.5 0 100 5 2.5 2.5 0 000-5z" />
                                            </svg>
                                        </button>

                                        @if(!empty($tieneCitasPendientes))
                                            <button type="button" disabled class="inline-flex items-center justify-center bg-gray-100 text-gray-400 rounded-full w-8 h-8 cursor-not-allowed" title="No puedes editar mientras tengas citas pendientes">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M17.414 2.586a2 2 0 00-2.828 0l-9.192 9.192a1 1 0 00-.263.465l-1 3a1 1 0 001.263 1.263l3-1a1 1 0 00.465-.263l9.192-9.192a2 2 0 000-2.828z" />
                                                    <path d="M13.586 4L16 6.414" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        @else
                                            <a href="{{ route('horarios.index', ['grupo' => $grupo->id]) }}" class="inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full w-8 h-8" title="Editar bloques de este grupo">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M17.414 2.586a2 2 0 00-2.828 0l-9.192 9.192a1 1 0 00-.263.465l-1 3a1 1 0 001.263 1.263l3-1a1 1 0 00.465-.263l9.192-9.192a2 2 0 000-2.828z" />
                                                    <path d="M13.586 4L16 6.414" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </a>
                                        @endif

                                        @if(!empty($tieneCitasPendientes))
                                            <button type="button" disabled class="bg-red-100 text-red-300 p-2 rounded-full w-8 h-8 cursor-not-allowed" title="No puedes eliminar mientras tengas citas pendientes">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                                    <path d="M3 6h18v2H3V6zm2 3h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V9zm3 2v8h2v-8H8zm4 0v8h2v-8h-2zm4 0v8h2v-8h-2zM9 2h6v2H9V2z" />
                                                </svg>
                                            </button>
                                        @else
                                            <form action="{{ route('grupos_horarios.destroy', $grupo->id) }}" method="POST" data-ajax-reload="true" data-confirm-message="¿Estás seguro de eliminar este grupo?" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-600 p-2 rounded-full w-8 h-8 transition" title="Eliminar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                                        <path d="M3 6h18v2H3V6zm2 3h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V9zm3 2v8h2v-8H8zm4 0v8h2v-8h-2zm4 0v8h2v-8h-2zM9 2h6v2H9V2z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <!-- Modal de vista del grupo -->
                                    <div id="grupoModal-{{ $grupo->id }}" class="modal-container fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 p-4">
                                        <div class="bg-white rounded-2xl w-full max-w-6xl p-6 overflow-y-auto" style="max-height: 90vh;">
                                            <div class="flex justify-between items-center mb-4">
                                                <h3 class="text-2xl text-blue-900 font-bold">Grupo de Horario: {{ $grupo->nombre }}</h3>
                                                <button onclick="closeGrupoModal('grupoModal-{{ $grupo->id }}')" class="text-gray-700 hover:text-gray-900 text-xl font-bold">✕</button>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                                @php
                                                    $dias = \App\Models\Horario::diasSemana();
                                                    $horariosPorDia = [];
                                                    foreach ($dias as $dia) {
                                                        $horariosPorDia[$dia] = $grupo->horarios->where('dia', $dia)->sortBy('hora_inicio');
                                                    }
                                                @endphp

                                                @foreach ($dias as $dia)
                                                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                                        <h4 class="text-lg font-semibold text-gray-800 mb-3 text-center">{{ $dia }}</h4>
                                                        @if ($horariosPorDia[$dia]->isEmpty())
                                                            <p class="text-sm text-gray-500 text-center">Sin bloques</p>
                                                        @else
                                                            <div class="space-y-2">
                                                                @foreach ($horariosPorDia[$dia] as $horario)
                                                                    <div class="bg-white p-3 rounded-xl border border-gray-200 text-center {{ $horario->activo == \App\Models\Horario::STATUS_INACTIVE ? 'opacity-50' : '' }}">
                                                                        <span class="text-sm font-semibold">{{ \Carbon\Carbon::parse($horario->hora_inicio)->format('g:i A') }} - {{ \Carbon\Carbon::parse($horario->hora_fin)->format('g:i A') }}</span>
                                                                        @if($horario->descripcion)
                                                                            <p class="text-xs text-gray-500 mt-1">{{ $horario->descripcion }}</p>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="mt-5 text-right">
                                                <button onclick="closeGrupoModal('grupoModal-{{ $grupo->id }}')" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-full">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-white rounded-[32px] border-2 border-dashed border-slate-200 p-16 text-center">
                            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Sin horarios configurados</h3>
                            <p class="text-slate-500 max-w-sm mx-auto leading-relaxed mb-6">
                                Crea tu horario para gestionarlo. Una vez que definas tus bloques de atención, podrás organizarlos por grupos aquí.
                            </p>
                            <a href="{{ route('horarios.index') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-full transition-all active:scale-95 shadow-lg shadow-blue-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Gestionar Bloques
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function openGrupoModal(button) {
            var targetId = button.getAttribute('data-target');
            var modal = document.getElementById(targetId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeGrupoModal(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
        }

        function handleAjaxReloadForm(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                if (form.dataset.ajaxReload !== 'true') {
                    return;
                }

                if (form.dataset.confirmMessage && !confirm(form.dataset.confirmMessage)) {
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

        document.addEventListener('click', function(event) {
            var target = event.target;
            if (target.classList.contains('modal-container')) {
                target.classList.remove('flex');
                target.classList.add('hidden');
            }
        });
    </script>
</x-app-layout>