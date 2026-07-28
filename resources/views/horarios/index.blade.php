<x-app-layout>
    {{-- ================================Vista de Lista de Horarios================================== --}}


    <div class="py-4 sm:py-10">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-3xl border-t-8 border-blue-600 overflow-hidden">
                <div class="p-4 sm:p-6">
                    {{-- ================================Encabezado y Botón Crear================================== --}}
                    <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                            <h3 class="text-2xl font-bold text-gray-800 dark:text-white">
                                @isset($grupoSeleccionado)
                                    Editar grupo: <span class="text-blue-900 dark:text-blue-400">{{ $grupoSeleccionado->nombre }}</span>
                                @else
                                    Bloques de Horario
                                @endisset
                            </h3>
                            @if(isset($grupoActivo) && !isset($grupoSeleccionado))
                                <span class="text-sm bg-green-100 dark:bg-green-900/30 text-emerald-800 dark:text-emerald-400 px-3 py-1 rounded-full w-fit">
                                    Horario activo: {{ $grupoActivo->nombre }}
                                </span>
                            @elseif(!isset($grupoActivo) && !isset($grupoSeleccionado))
                                <span class="text-sm bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 px-3 py-1 rounded-full w-fit">
                                    Sin horario activo
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2 items-center">
                            @if(!isset($grupoSeleccionado))
                                <a href="{{ route('horarios.exportarPdf', isset($grupoActivo) ? ['grupo' => $grupoActivo->id] : []) }}" target="_blank" class="bg-gray-400 dark:bg-gray-600 text-white w-10 h-10 p-0 rounded-full hover:bg-gray-500 dark:hover:bg-gray-500 transition flex items-center justify-center" title="Descargar horario en PDF">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                </a>
                                <button id="openGroupModal" type="button" {{ (isset($grupoActivo) || !empty($tieneCitasPendientes)) ? 'disabled' : '' }} class="bg-gray-400 dark:bg-gray-600 text-white w-10 h-10 p-0 rounded-full hover:bg-gray-500 dark:hover:bg-gray-500 transition flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed" title="Guardar grupo de horarios" aria-disabled="{{ (isset($grupoActivo) || !empty($tieneCitasPendientes)) ? 'true' : 'false' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-8H7v8M7 3v8h10V3" />
                                    </svg>
                                </button>
                                <a href="{{ route('grupos_horarios.index') }}" class="bg-blue-400 dark:bg-blue-600 text-white px-4 py-2 rounded-full hover:bg-blue-500 dark:hover:bg-blue-700 transition">
                                    Horarios
                                </a>
                            @endif

                            <a href="{{ route('horarios.create', isset($grupoSeleccionado) ? ['grupo' => $grupoSeleccionado->id] : []) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-full transition shadow hover:shadow-md">
                                + Crear
                            </a>
                        </div>
                    </div>

                    {{-- Banner contextual cuando se edita un grupo específico --}}
                    @isset($grupoSeleccionado)
                        <div class="mb-5 flex flex-col sm:flex-row sm:items-center gap-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl px-4 py-3">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="8" y1="2" x2="8" y2="6" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="3" y1="10" x2="21" y2="10" stroke-width="2"/>
                                </svg>
                                <span class="text-sm text-blue-800 dark:text-blue-300">
                                    Estás viendo los bloques del grupo <strong>{{ $grupoSeleccionado->nombre }}</strong>.
                                    Los cambios que hagas aquí afectarán solo a este grupo.
                                </span>
                            </div>
                            <div class="flex gap-2 flex-shrink-0">
                                <a href="{{ route('horarios.index') }}" class="flex-shrink-0 text-sm bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-3 py-1.5 rounded-full transition">
                                    Cancelar
                                </a>
                                <a href="{{ route('grupos_horarios.index') }}" class="flex-shrink-0 text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-full transition">
                                    Volver a Grupos
                                </a>
                            </div>
                        </div>
                    @endisset

                    @if (!empty($tieneCitasPendientes))
                        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400">
                            No puedes editar ni eliminar bloques de horario mientras tengas citas pendientes o en espera.
                        </div>
                    @endif

                    <!-- Modal Guardar Grupo -->
                    <div id="groupModal" class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-40 dark:bg-opacity-60 z-50">
                        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md p-6 shadow-lg">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Guardar Cambios en Grupo de Horarios</h3>
                            <form method="POST" action="{{ route('grupos_horarios.store_from_horarios') }}">
                                @csrf
                                <input type="hidden" name="action" value="{{ isset($grupoActivo) ? 'update' : 'create' }}" />
                                <div class="mb-4">
                                    @if(isset($grupoActivo))
                                        <p class="text-sm text-gray-700 dark:text-gray-300">Hay un grupo activo "{{ $grupoActivo->nombre }}"; los cambios se aplicarán directamente ahí. Si deseas crear uno nuevo, ingresa un nombre.</p>
                                    @else
                                        <p class="text-sm text-gray-700 dark:text-gray-300">No hay grupo activo. Ingresa el nombre para crear uno nuevo.</p>
                                    @endif
                                </div>
                                <div class="mb-4" id="nombreField">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="nombre_grupo">Nombre del nuevo grupo</label>
                                    <input id="nombre_grupo" name="nombre" type="text" placeholder="Muy ocupado" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500" {{ !isset($grupoActivo) ? 'required' : 'disabled' }}/>
                                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" id="closeGroupModal" class="px-3 py-2 border rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Cancelar</button>
                                    <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- ================================Filtro por Día================================== --}}
                    <!-- Filtro por día -->
                    <div class="mb-6">
                        <form method="GET" class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                            @isset($grupoSeleccionado)
                                <input type="hidden" name="grupo" value="{{ $grupoSeleccionado->id }}">
                            @endisset
                            <label class="text-gray-700 dark:text-gray-300">Filtrar por día:</label>
                            <select name="dia" onchange="this.form.submit()" class="rounded-xl border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todos los días</option>
                                @foreach ($dias as $dia)
                                    <option value="{{ $dia }}" {{ $filtroDia === $dia ? 'selected' : '' }}>{{ $dia }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    {{-- ================================Calendario Semanal================================== --}}
                    <!-- Calendario semanal -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        @foreach ($horariosPorDia as $dia => $horariosDia)
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-4 border border-gray-100 dark:border-gray-700">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-3 text-center">{{ $dia }}</h4>
                                @if ($horariosDia->isEmpty())
                                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">Sin bloques</p>
                                @else
                                    <div class="space-y-2">
                                        @foreach ($horariosDia as $horario)
                                            <div class="relative bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-lg transition text-center {{ $horario->activo == \App\Models\Horario::STATUS_INACTIVE ? 'opacity-50' : '' }}">
                                                <div class="mb-2">
                                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($horario->hora_inicio)->format('g:i A') }} - {{ \Carbon\Carbon::parse($horario->hora_fin)->format('g:i A') }}</span>
                                                </div>

                                                <div class="flex items-center justify-center gap-2 flex-wrap">
                                                    <button type="button" onclick="openBlockModal('blockModal-{{ $horario->id }}')" class="inline-flex items-center gap-1 text-white bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded-full text-xs font-semibold">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3">
                                                            <path d="M12 5.25C7.73 5.25 3.98 7.61 2 12c1.98 4.39 5.73 6.75 10 6.75s8.02-2.36 10-6.75C20.02 7.61 16.27 5.25 12 5.25zM12 15.75a3.75 3.75 0 1 1 0-7.5 3.75 3.75 0 0 1 0 7.5z" />
                                                            <path d="M12 10.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z" />
                                                        </svg>
                                                        Ver
                                                    </button>

                                                    @if(!empty($tieneCitasPendientes))
                                                        <button type="button" disabled class="inline-flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-400 rounded-full w-8 h-8 cursor-not-allowed" title="No puedes editar mientras tengas citas pendientes">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM21.41 6.34c.78-.78.78-2.05 0-2.83l-1.92-1.92a2 2 0 0 0-2.83 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <a href="{{ route('horarios.edit', ['horario' => $horario->id] + (isset($grupoSeleccionado) ? ['grupo' => $grupoSeleccionado->id] : [])) }}" class="inline-flex items-center justify-center bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-full w-8 h-8">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM21.41 6.34c.78-.78.78-2.05 0-2.83l-1.92-1.92a2 2 0 0 0-2.83 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                                                            </svg>
                                                        </a>
                                                    @endif

                                                    @if(!empty($tieneCitasPendientes))
                                                        <button type="button" disabled class="inline-flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-300 rounded-full w-8 h-8 cursor-not-allowed" title="No puedes eliminar mientras tengas citas pendientes">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                                                <path d="M3 6h18v2H3V6zm2 3h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V9zm3 2v8h2v-8H8zm4 0v8h2v-8h-2zm4 0v8h2v-8h-2zM9 2h6v2H9V2z" />
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <form action="{{ route('horarios.destroy', $horario->id) }}" method="POST" data-ajax-delete-block="true" class="m-0 p-0">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="inline-flex items-center justify-center bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-800/50 text-red-600 dark:text-red-400 rounded-full w-8 h-8">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                                                    <path d="M3 6h18v2H3V6zm2 3h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V9zm3 2v8h2v-8H8zm4 0v8h2v-8h-2zm4 0v8h2v-8h-2zM9 2h6v2H9V2z" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>

                                                <!-- Modal del bloque de horario -->
                                                <div id="blockModal-{{ $horario->id }}" class="block-modal fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 p-4" onclick="if(event.target === this) closeBlockModal('blockModal-{{ $horario->id }}')">
                                                    <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-sm p-6 overflow-y-auto shadow-2xl border-t-8 border-blue-600 text-left">
                                                        <div class="flex justify-between items-center mb-6">
                                                            <h3 class="text-2xl font-bold text-blue-900 dark:text-blue-400">Detalle del bloque</h3>
                                                            <button type="button" onclick="closeBlockModal('blockModal-{{ $horario->id }}')" class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-bold text-xl">✕</button>
                                                        </div>

                                                        <div class="flex items-center justify-between mb-4">
                                                            <div>
                                                                <h4 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $horario->dia }}</h4>
                                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($horario->hora_inicio)->format('g:i A') }} - {{ \Carbon\Carbon::parse($horario->hora_fin)->format('g:i A') }}</p>
                                                            </div>
                                                        </div>

                                                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-700">
                                                            @php
                                                                $statusLabel = 'Eliminado';
                                                                $statusClass = 'text-gray-500 dark:text-gray-400';
                                                                if ($horario->activo == \App\Models\Horario::STATUS_ACTIVE) {
                                                                    $statusLabel = 'Activo';
                                                                    $statusClass = 'text-emerald-600 dark:text-emerald-400';
                                                                } elseif ($horario->activo == \App\Models\Horario::STATUS_INACTIVE) {
                                                                    $statusLabel = 'Inactivo';
                                                                    $statusClass = 'text-red-500 dark:text-red-400';
                                                                }
                                                            @endphp
                                                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-1"><span class="font-semibold">Día:</span> {{ $horario->dia }}</p>
                                                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-1"><span class="font-semibold">Hora inicio:</span> {{ \Carbon\Carbon::parse($horario->hora_inicio)->format('g:i A') }}</p>
                                                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-1"><span class="font-semibold">Hora fin:</span> {{ \Carbon\Carbon::parse($horario->hora_fin)->format('g:i A') }}</p>
                                                            <p class="text-gray-600 dark:text-gray-300 text-sm"><span class="font-semibold">Estado:</span> <span class="font-semibold {{ $statusClass }}">{{ $statusLabel }}</span></p>
                                                            @if ($horario->descripcion)
                                                                <p class="text-gray-600 dark:text-gray-300 text-sm mt-2"><span class="font-semibold">Descripción:</span> {{ $horario->descripcion }}</p>
                                                            @endif
                                                        </div>

                                                        <div class="mt-6 text-right">
                                                            <button type="button" onclick="closeBlockModal('blockModal-{{ $horario->id }}')" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-full transition">Cerrar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    function openBlockModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeBlockModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
    var openGroupBtn = document.getElementById('openGroupModal');
    if (openGroupBtn) {
        openGroupBtn.addEventListener('click', function() {
            if (this.disabled) {
                return;
            }
            document.getElementById('groupModal').classList.remove('hidden');
            document.getElementById('groupModal').classList.add('flex');
        });
    }

    var closeGroupBtn = document.getElementById('closeGroupModal');
    if (closeGroupBtn) {
        closeGroupBtn.addEventListener('click', function() {
            document.getElementById('groupModal').classList.add('hidden');
            document.getElementById('groupModal').classList.remove('flex');
        });
    }

    var groupModalEl = document.getElementById('groupModal');
    if (groupModalEl) {
        groupModalEl.addEventListener('click', function(event) {
            if (event.target.id === 'groupModal') {
                this.classList.add('hidden');
                this.classList.remove('flex');
            }
        });
    }

    // Mostrar/ocultar campo de nombre basado en action oculto
    function updateNombreField() {
        var nombreField = document.getElementById('nombreField');
        var nombreInput = document.getElementById('nombre_grupo');
        var actionInput = document.querySelector('input[name="action"]');

        if (!actionInput) {
            return;
        }

        var action = actionInput.value;

        if (action === 'create') {
            nombreField.style.display = 'block';
            nombreInput.required = true;
            nombreInput.disabled = false;
        } else {
            nombreField.style.display = 'none';
            nombreInput.required = false;
            nombreInput.disabled = true;
        }
    }

    updateNombreField();

    function handleAjaxDeleteBlock(form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            AppModal.confirm('Eliminar Bloque', '¿Eliminar este bloque?').then(function(confirmed) {
                if (!confirmed) return;

                var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!token) {
                    AppModal.alert('Error', 'No se pudo obtener CSRF token. Recarga la página e inténtalo de nuevo.');
                    return;
                }

                var formData = new FormData(form);
                formData.append('_method', 'DELETE');

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
                            throw new Error(body.message || 'No se pudo eliminar el bloque.');
                        }).catch(function() {
                            throw new Error('No se pudo eliminar el bloque.');
                        });
                    }
                    return response.json();
                }).then(function(result) {
                    if (result && result.status === 'success') {
                        var blockEl = form.closest('.relative.bg-white');
                        if (blockEl) {
                            blockEl.style.transition = 'opacity 0.3s, transform 0.3s';
                            blockEl.style.opacity = '0';
                            blockEl.style.transform = 'scale(0.95)';
                            setTimeout(function() { blockEl.remove(); }, 300);
                        }
                        if (window.showToast) {
                            window.showToast(result.message || 'Bloque eliminado correctamente.', 'success');
                        } else {
                            var msgContainer = document.querySelector('.p-6');
                            if (msgContainer) {
                                var existingMsg = msgContainer.querySelector('.ajax-success-msg');
                                if (existingMsg) existingMsg.remove();
                                var msg = document.createElement('div');
                                msg.className = 'ajax-success-msg mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-400';
                                msg.textContent = result.message || 'Bloque eliminado correctamente.';
                                msgContainer.insertBefore(msg, msgContainer.children[1]);
                                setTimeout(function() {
                                    msg.style.transition = 'opacity 0.5s';
                                    msg.style.opacity = '0';
                                    setTimeout(function() { msg.remove(); }, 500);
                                }, 3000);
                            }
                        }
                    } else {
                        throw new Error(result.message || 'No se pudo eliminar el bloque.');
                    }
                }).catch(function(error) {
                    console.error('Error al eliminar el bloque:', error);
                    AppModal.alert('Error', error.message || 'Error al eliminar el bloque. Recarga la página e inténtalo nuevamente.');
                });
            });
        });
    }

    document.querySelectorAll('form[data-ajax-delete-block="true"]').forEach(handleAjaxDeleteBlock);
</script>
</x-app-layout>
