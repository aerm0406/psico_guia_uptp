<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            BIENVENIDO A PSICO-GUÍA
        </h2>
    </x-slot>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                <a href="{{ route('agenda.index') }}" class="group block bg-white hover:shadow-lg transition-all rounded-3xl p-8 border border-gray-100 flex-col justify-between min-h-[280px]">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800 group-hover:text-blue-900">Mi Agenda (Itinerario)</h3>
                                <p class="text-gray-500 text-sm mt-1">Próximas citas confirmadas.</p>
                            </div>
                            <div class="p-3 bg-cyan-50 rounded-2xl">
                                <span class="text-3xl">📅</span>
                            </div>
                        </div>
                        <div class="space-y-3 mb-4">
                            @if(isset($confirmadasHoy) && $confirmadasHoy->count() > 0)
                                @foreach($confirmadasHoy as $cita)
                                    <div class="flex items-center text-sm p-3 bg-gray-50 rounded-xl">
                                        <span class="w-3 h-3 bg-green-400 rounded-full mr-3"></span>
                                        <span class="flex-1 font-semibold text-gray-700">{{ optional($cita->paciente)->name ?: 'Paciente confirmado' }}</span>
                                        <span class="text-gray-500 font-medium">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-sm p-3 bg-gray-50 rounded-xl text-gray-500">
                                    No tienes citas confirmadas cercanas.
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="w-full bg-blue-500 group-hover:bg-blue-600 text-white py-3 rounded-xl text-md font-bold text-center transition-colors">
                        Ver Agenda
                    </div>
                </a>

                <a href="{{ route('horarios.index') }}" class="group block bg-cyan-50 hover:shadow-lg transition-all rounded-3xl p-8 border border-blue-100 flex-col justify-between min-h-[280px]">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-blue-800">Horarios</h3>
                            <p class="text-gray-500 text-sm mt-1">Ajusta o crea bloques de tiempo para gestionar citas.</p>
                        </div>
                        <div class="p-3 bg-white rounded-full shadow-sm">
                            <span class="text-3xl">🕒</span>
                        </div>
                    </div>
                    <div class="w-full bg-blue-500 group-hover:bg-blue-600 text-white py-3 rounded-xl text-md font-bold text-center transition-colors">
                        Crear Horarios
                    </div>
                </a>

                <div class="group bg-white hover:shadow-lg transition-all rounded-3xl p-8 border border-gray-100 flex flex-col justify-between min-h-[280px]" x-data="{ query: '', results: [], open: false, isSearching: false, search() { if(this.query.length < 2) { this.results = []; this.open = false; return; } this.isSearching = true; fetch(`/historias/buscar/paciente?q=${this.query}`).then(res => res.json()).then(data => { this.results = data; this.open = true; this.isSearching = false; }).catch(() => { this.isSearching = false; }) } }">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800 group-hover:text-blue-900">Historias Clínicas</h3>
                                <p class="text-gray-500 text-sm mt-1">Consulta de expedientes de pacientes.</p>
                            </div>
                            <div class="p-3 bg-blue-50 rounded-2xl">
                                <span class="text-3xl">📂</span>
                            </div>
                        </div>
                        <div class="relative mb-4" @click.away="open = false">
                            <input type="text" x-model="query" @input.debounce.300ms="search" placeholder="Buscar paciente..." class="block w-full pl-4 pr-10 py-3 bg-gray-50 border border-gray-200 text-gray-700 rounded-xl text-sm focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all" autocomplete="off">
                            <div class="absolute inset-y-0 right-0 py-3 pr-4 flex items-center pointer-events-none">
                                <svg x-show="!isSearching" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <svg x-show="isSearching" class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                            
                            <div x-show="open" x-transition.opacity class="absolute z-10 w-full mt-2 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden" style="display: none;">
                                <ul class="max-h-48 overflow-y-auto">
                                    <template x-for="paciente in results" :key="paciente.id">
                                        <li>
                                            <a :href="`/historias/${paciente.id}`" class="block px-4 py-3 hover:bg-blue-50 text-gray-700 text-sm border-b border-gray-50 last:border-0 transition-colors">
                                                <div class="flex items-center">
                                                    <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                                                    <span class="font-semibold" x-text="paciente.name"></span>
                                                </div>
                                            </a>
                                        </li>
                                    </template>
                                    <li x-show="results.length === 0" class="px-4 py-3 text-gray-500 text-sm text-center">
                                        No se encontraron resultados
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('historias.index') }}" class="block w-full bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-xl text-md font-bold text-center transition-colors mt-auto">
                        Ver Todos
                    </a>
                </div>

                <a href="{{ route('pacientes.index') }}" class="group block bg-white hover:shadow-lg transition-all rounded-3xl p-8 border border-gray-100 flex-col justify-between min-h-[280px]">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Mis Pacientes</h3>
                            <p class="text-gray-500 text-sm mt-1">Detalles personales y de psicología.</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-full">
                            <span class="text-3xl">👤</span>
                        </div>
                    </div>
                    <div class="w-full bg-blue-500 group-hover:bg-blue-600 text-white py-3 rounded-xl text-md font-bold text-center transition-colors">
                        Ver Perfil
                    </div>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>