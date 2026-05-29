<x-app-layout>


    <div class="pt-12 pb-4 bg-slate-50 min-h-[calc(100vh-4rem)] flex flex-col">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex-1 flex flex-col w-full">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Historial Clínico</h1>
                    <p class="mt-2 text-slate-500 text-sm">Gestiona la evolución y expedientes de tus pacientes atendidos.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input id="search-input" type="text" placeholder="Buscar paciente..." class="w-64 pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                        <svg class="w-5 h-5 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>

            @php
                $historias = $historias ?? collect();
            @endphp

            @if($historias->isEmpty())
                <div class="bg-white rounded-[32px] border-2 border-dashed border-slate-200 p-16 text-center shadow-sm">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Sin expedientes activos</h3>
                    <p class="text-slate-500 max-w-sm mx-auto leading-relaxed">
                        Los pacientes aparecerán aquí automáticamente una vez que completes su primera cita.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($historias as $historia)
                        <div class="paciente-card bg-white rounded-[32px] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300 group overflow-hidden flex flex-col" data-nombre="{{ strtolower($historia['paciente']->name) }}">
                            <div class="p-8 flex-1">
                                <!-- Patient Info -->
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-600 to-violet-700 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-200 group-hover:scale-110 transition-transform">
                                        {{ $historia['paciente']->avatar ?? strtoupper(substr($historia['paciente']->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-lg font-bold text-slate-900 truncate tracking-tight">{{ $historia['paciente']->name }}</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 uppercase tracking-wider">
                                            Activo
                                        </span>
                                    </div>
                                </div>

                                <!-- Stats Row -->
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-slate-50 rounded-2xl p-3">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Sesiones</p>
                                        <p class="text-lg font-black text-slate-800">{{ $historia['citas_realizadas'] }}</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-2xl p-3">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Última</p>
                                        <p class="text-sm font-bold text-slate-800">{{ $historia['ultima_sesion'] instanceof \Carbon\Carbon ? $historia['ultima_sesion']->locale('es')->translatedFormat('d F') : \Carbon\Carbon::parse($historia['ultima_sesion'])->locale('es')->translatedFormat('d F') }}</p>
                                    </div>
                                </div>

                                <!-- Latest Diagnosis/Note Snippet
                                <div class="mb-4">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Diagnóstico / Evolución</p>
                                    <p class="text-sm text-slate-600 leading-relaxed italic line-clamp-2">
                                        {{ $historia['diagnostico'] ?? $historia['notas'] ?? 'Sin diagnóstico registrado aún.' }}
                                    </p>
                                </div>
                                 -->
                            </div>

                            <!-- Footer Link -->
                            <a href="{{ route('historias.show', $historia['paciente']->id) }}" class="bg-slate-50/50 group-hover:bg-indigo-600 p-4 text-center border-t border-slate-50 transition-colors">
                                <span class="text-sm font-bold text-indigo-600 group-hover:text-white flex items-center justify-center gap-2">
                                    Abrir Expediente
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </span>
                            </a>
                        </div>
                    @endforeach
                </div>

                <!-- Mensaje de sin resultados (oculto por defecto) -->
                <div id="no-results-msg" class="hidden bg-white rounded-[32px] border-2 border-dashed border-slate-200 p-12 text-center shadow-sm">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Sin coincidencias</h3>
                    <p class="text-slate-500 text-sm">No se encontraron pacientes que coincidan con tu búsqueda.</p>
                </div>

                <div class="mt-auto flex justify-center pb-2 pt-12">
                    {{ $historias->appends(request()->query())->links('pacientes.partials.pagination') }}
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const noResultsMsg = document.getElementById('no-results-msg');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase().trim();
                    const cards = document.querySelectorAll('.paciente-card');
                    let visibleCount = 0;
                    
                    cards.forEach(card => {
                        const nombre = card.getAttribute('data-nombre');
                        if (nombre.includes(query)) {
                            card.style.display = 'flex';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    if (noResultsMsg) {
                        if (visibleCount === 0 && cards.length > 0) {
                            noResultsMsg.classList.remove('hidden');
                        } else {
                            noResultsMsg.classList.add('hidden');
                        }
                    }

                    // Hide pagination if searching client-side
                    const paginationNav = document.querySelector('nav[aria-label="Pagination Navigation"]');
                    if (paginationNav) {
                        paginationNav.style.display = query !== '' ? 'none' : '';
                    }
                });
            }
        });
    </script>
</x-app-layout>