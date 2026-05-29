<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl rounded-3xl border-l-8 border-blue-700">
                <div class="p-8 space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h2 class="font-semibold text-2xl text-gray-800 leading-tight mb-2">{{ __('Solicitud de cita') }}</h2>
                        <p class="text-gray-500 text-sm italic">Completa el formulario para solicitar una cita. Selecciona el psicólogo, y a la vez, sugiere los bloques de horario en los que te gustaría ser atendido y describe brevemente el motivo de tu consulta.</p>
                    </div>
                    @if (session('success'))
                        <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                            <strong>Corrige los errores:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($psicologos->isEmpty())
                        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-700">
                            Lo sentimos. No hay psicólogos disponibles en este momento. Vuelva más tarde e intentelo nuevamente.
                        </div>
                        <div class="flex justify-end mt-4">
                            <a href="{{ route('citas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">← Volver</a>
                        </div>
                    @else
                        @if(!empty($tieneCitaPendiente))
                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-700 mb-4">
                                Ya tienes una solicitud de cita pendiente. No puedes enviar otra solicitud hasta que esta sea procesada.
                            </div>
                            <div class="flex justify-end">
                                <a href="{{ route('citas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">← Volver</a>
                            </div>
                        @else
                            @php
                                $dayOrder = [
                                    'Lunes' => 1,
                                    'Martes' => 2,
                                    'Miércoles' => 3,
                                    'Miercoles' => 3,
                                    'Jueves' => 4,
                                    'Viernes' => 5,
                                    'Sábado' => 6,
                                    'Sabado' => 6,
                                    'Domingo' => 7,
                                ];

                                $psicologoSlots = $psicologos->mapWithKeys(function ($psicologo) use ($dayOrder) {
                                    $horarios = $psicologo->gruposHorarios
                                        ->flatMap(fn($grupo) => $grupo->horarios)
                                        ->sortBy(fn($h) => sprintf('%02d:%s', $dayOrder[$h->dia] ?? 99, $h->hora_inicio));

                                    return [
                                        $psicologo->id => $horarios->map(function($h) {
                                            $inicio = \Carbon\Carbon::parse($h->hora_inicio)->format('g:i A');
                                            $fin = \Carbon\Carbon::parse($h->hora_fin)->format('g:i A');
                                            return $h->dia . ' ' . $inicio . ' - ' . $fin;
                                        })->values()->toArray(),
                                    ];
                                });
                            @endphp
                            <form method="POST" action="{{ route('citas.store') }}" class="space-y-4">
                                @csrf

                                <div>
                                    <x-input-label for="psicologo_id" :value="__('Psicólogo disponible')" />
                                    <select id="psicologo_id" name="psicologo_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                        <option value="">Selecciona un psicólogo</option>
                                        @foreach ($psicologos as $psicologo)
                                            @php
                                                $slots = $psicologoSlots[$psicologo->id] ?? [];
                                            @endphp
                                            <option value="{{ $psicologo->id }}" data-slots='@json($slots)' {{ old('psicologo_id') == $psicologo->id ? 'selected' : '' }}>
                                                {{ $psicologo->name }}
                                            </option>
                                        @endforeach
                                    </select>
                            <x-input-error :messages="$errors->get('psicologo_id')" class="mt-2" />
                        </div>

                        <div id="availableSlotsBlock" class="mb-4 hidden">
                            <x-input-label :value="__('Bloques disponibles del psicólogo seleccionado')" />
                            <div id="availableSlotsButtons" class="mt-2 grid grid-cols-1 sm:grid-cols-4 gap-2"></div>
                            <p id="availableSlotsEmpty" class="mt-2 text-sm text-gray-500 hidden">El psicólogo no tiene bloques activos. Selecciona otro o vuelve a intentarlo más tarde.</p>
                            <p class="text-xs text-gray-500 mt-1">Selecciona uno o varios bloques. El campo se llenará automáticamente.</p>
                            <p class="text-sm text-gray-700 mt-3">Seleccionados: <span id="selectedSlotsText">{{ old('bloques_sugeridos') ?: 'Ninguno' }}</span></p>
                            <input type="hidden" id="bloques_sugeridos" name="bloques_sugeridos" value="{{ old('bloques_sugeridos') }}" />
                            <x-input-error :messages="$errors->get('bloques_sugeridos')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="motivo" :value="__('Motivo breve de consulta')" />
                            <textarea id="motivo" name="motivo" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>{{ old('motivo') }}</textarea>
                            <x-input-error :messages="$errors->get('motivo')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('citas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500" @if(!empty($tieneCitaPendiente)) disabled @endif>
                                {{ __('Enviar solicitud') }}
                            </button>
                        </div>
                    </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

<script>
    const psicologoSelect = document.getElementById('psicologo_id');
    const availableSlotsBlock = document.getElementById('availableSlotsBlock');
    const availableSlotsButtons = document.getElementById('availableSlotsButtons');
    const availableSlotsEmpty = document.getElementById('availableSlotsEmpty');
    const hiddenBloques = document.getElementById('bloques_sugeridos');
    const selectedSlotsText = document.getElementById('selectedSlotsText');
    const psicologoSlots = @json($psicologoSlots ?? []);

    if (psicologoSelect && availableSlotsBlock && availableSlotsButtons && availableSlotsEmpty && hiddenBloques && selectedSlotsText) {
        let currentSlots = [];
        let selectedSlots = new Set(
            (hiddenBloques.value || '').split(';').map(function (slot) {
                return slot.trim();
            }).filter(Boolean)
        );

        function updateHiddenInput() {
            const values = Array.from(selectedSlots);
            hiddenBloques.value = values.join('; ');
            selectedSlotsText.textContent = values.length > 0 ? values.join('; ') : 'Ninguno';
        }

        function renderSlots(slots) {
            currentSlots = slots;
            availableSlotsButtons.innerHTML = '';

            if (slots.length === 0) {
                availableSlotsEmpty.classList.remove('hidden');
                selectedSlotsText.textContent = 'Ninguno';
                return;
            }

            availableSlotsEmpty.classList.add('hidden');

            slots.forEach(function (slot) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'rounded-lg border border-blue-300 text-blue-600 bg-white px-3 py-2 text-left text-sm font-medium hover:bg-blue-50 transition';
                button.textContent = slot;
                button.dataset.slot = slot;

                if (selectedSlots.has(slot)) {
                    button.classList.add('bg-blue-600', 'text-white');
                }

                button.addEventListener('click', function () {
                    if (selectedSlots.has(slot)) {
                        selectedSlots.delete(slot);
                        button.classList.remove('bg-blue-600', 'text-white');
                    } else {
                        selectedSlots.add(slot);
                        button.classList.add('bg-blue-600', 'text-white');
                    }
                    updateHiddenInput();
                });

                availableSlotsButtons.appendChild(button);
            });

            updateHiddenInput();
        }

        function onPsychologoChange() {
            const selectedId = psicologoSelect.value;
            const slots = psicologoSlots[selectedId] || [];
            const hasPsychologo = selectedId !== '';

            if (hasPsychologo && slots.length > 0) {
                availableSlotsBlock.classList.remove('hidden');
                availableSlotsEmpty.classList.add('hidden');
                renderSlots(slots);
            } else if (hasPsychologo) {
                availableSlotsBlock.classList.remove('hidden');
                availableSlotsButtons.innerHTML = '';
                availableSlotsEmpty.classList.remove('hidden');
                selectedSlotsText.textContent = 'Ninguno';
                selectedSlots.clear();
                updateHiddenInput();
            } else {
                availableSlotsBlock.classList.add('hidden');
                availableSlotsButtons.innerHTML = '';
                availableSlotsEmpty.classList.add('hidden');
                selectedSlotsText.textContent = 'Ninguno';
                selectedSlots.clear();
                updateHiddenInput();
            }
        }

        psicologoSelect.addEventListener('change', onPsychologoChange);

        if (psicologoSelect.value) {
            onPsychologoChange();
        }
    }
</script>
</x-app-layout>
