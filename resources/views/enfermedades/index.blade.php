<x-app-layout>
    <div class="pt-12 pb-4 bg-slate-50 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs / Back Link -->
            <a href="{{ $returnTo ? route('historias.show', [$returnTo, 'editing' => $editing]) : route('historias.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-900 text-sm font-medium mb-6 transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Volver a {{ $returnTo ? 'Historia Clínica' : 'Historias' }}
            </a>

            <!-- Header & Filters -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Gestión de Enfermedades y Condiciones</h1>
                    <p class="mt-2 text-slate-500 text-sm">Administra el catálogo de condiciones médicas y trastornos.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-white border border-slate-200 rounded-full shadow-sm pr-2 focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 transition-all h-11">
                        <div class="relative flex-1 h-full min-w-[180px]">
                            <input type="text" id="disease-search" value="{{ $search }}" placeholder="Buscar por nombre..." 
                                class="w-full pl-10 pr-4 py-0 h-full bg-transparent border-none text-sm focus:ring-0 outline-none">
                            <div class="absolute left-3 top-3">
                                <svg id="search-icon-disease" class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <div id="search-spinner-disease" class="hidden animate-spin rounded-full h-4 w-4 border-2 border-indigo-500 border-t-transparent absolute top-0 left-0"></div>
                            </div>
                        </div>
                        <div class="h-4 w-px bg-slate-200 mx-1"></div>
                        <select id="disease-filter" class="bg-transparent border-none text-[10px] font-black text-slate-500 py-0 h-full pl-2 pr-8 focus:ring-0 outline-none cursor-pointer uppercase tracking-tighter">
                            <option value="">TODAS</option>
                            <option value="mental" {{ $categoriaFiltro == 'mental' ? 'selected' : '' }}>PSIQUIÁTRICA</option>
                            <option value="biopsicosocial" {{ $categoriaFiltro == 'biopsicosocial' ? 'selected' : '' }}>BIOPSICOSOCIAL</option>
                            <option value="fisica" {{ $categoriaFiltro == 'fisica' ? 'selected' : '' }}>MÉDICA</option>
                        </select>
                    </div>

                    <a href="{{ route('enfermedades.create', ['tipo' => $tipo, 'return_to' => $returnTo, 'editing' => $editing]) }}" 
                        class="flex flex-shrink-0 items-center justify-center w-11 h-11 bg-indigo-600 text-white text-lg font-bold rounded-full hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 active:scale-95"
                        title="Añadir Enfermedad">
                        +
                    </a>
                </div>
            </div>

            <!-- List Section -->
            <div id="disease-content">
                @include('enfermedades.components.disease_list')
            </div>
        </div>
    </div>
        </div>
    </div>

    <!-- Modal de Detalles -->
    <div id="modalDetalles" class="fixed inset-0 z-[60] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="cerrarModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                <div class="bg-white px-8 pt-8 pb-4">
                    <div class="flex items-center justify-between mb-6">
                        <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <button onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div>
                        <h3 class="text-2xl font-black text-slate-900 leading-tight mb-2" id="modalNombre">Nombre de la Enfermedad</h3>
                        <div class="flex flex-wrap gap-2 mb-8">
                            <span id="modalCategoria" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-600">Categoría</span>
                            <span id="modalTipo" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-600">Variación</span>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Descripción y Notas</h4>
                                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100/50">
                                    <p id="modalDescripcion" class="text-sm text-slate-600 leading-relaxed italic">Sin descripción adicional.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('disease-search');
        const diseaseFilter = document.getElementById('disease-filter');
        const searchIcon = document.getElementById('search-icon-disease');
        const searchSpinner = document.getElementById('search-spinner-disease');
        let searchTimeout;

        const performDiseaseFilter = () => {
            const query = searchInput.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.disease-row');
            const noResultsRow = document.getElementById('no-results-disease');
            const pagination = document.getElementById('disease-pagination');
            let visibleCount = 0;

            // Ocultar paginado si hay búsqueda activa
            if (pagination) {
                if (query.length > 0) {
                    pagination.classList.add('hidden');
                } else {
                    pagination.classList.remove('hidden');
                }
            }

            // FASE 1: Filtrado Instantáneo (Client-side)
            rows.forEach(row => {
                const searchText = row.getAttribute('data-search');
                if (searchText.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Mostrar/Ocultar mensaje de no resultados (Client-side)
            if (noResultsRow) {
                if (visibleCount === 0 && rows.length > 0) {
                    noResultsRow.classList.remove('hidden');
                } else {
                    noResultsRow.classList.add('hidden');
                }
            }

            // FASE 2: Debounce para Búsqueda Profunda (Server-side)
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const categoria = diseaseFilter.value;
                const tipo = '{{ $tipo }}';
                const returnTo = '{{ $returnTo }}';
                
                searchIcon.classList.add('opacity-0');
                searchSpinner.classList.remove('hidden');

                const editing = '{{ $editing }}';
                fetch(`{{ route('enfermedades.index') }}?search=${query}&categoria_filtro=${categoria}&tipo=${tipo}&return_to=${returnTo}&editing=${editing}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.text())
                .then(html => {
                    document.getElementById('disease-content').innerHTML = html;
                    searchIcon.classList.remove('opacity-0');
                    searchSpinner.classList.add('hidden');
                })
                .catch(() => {
                    searchIcon.classList.remove('opacity-0');
                    searchSpinner.classList.add('hidden');
                });
            }, 400);
        };

        searchInput?.addEventListener('input', performDiseaseFilter);
        diseaseFilter?.addEventListener('change', performDiseaseFilter);

        function seleccionarEnfermedad(id, nombre) {
            // El 'tipo' que recibimos en la URL ahora es el contexto directo ('pers_psq', 'fam_med', etc.)
            const contexto = '{{ $tipo }}';
            
            if (!confirm(`¿Deseas vincular "${nombre}" a la historia clínica del paciente?`)) return;

            fetch('{{ route('historias.enfermedad.vincular') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    historia_clinica_id: '{{ $historiaId }}',
                    enfermedad_id: id,
                    contexto: contexto
                })
            })
            .then(async res => {
                const data = await res.json();
                if (res.ok && data.success) {
                    if (window.showToast) {
                        window.showToast('Enfermedad vinculada correctamente', 'success');
                    } else {
                        alert('Enfermedad vinculada correctamente');
                    }
                } else {
                    const errorMsg = data.message || 'No se pudo vincular la enfermedad. Verifica el contexto.';
                    if (window.showToast) {
                        window.showToast(errorMsg, 'error');
                    } else {
                        alert(errorMsg);
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (window.showToast) window.showToast('Error de conexión', 'error');
            });
        }

        function verEnfermedad(enfermedad) {
            const modal = document.getElementById('modalDetalles');
            
            document.getElementById('modalNombre').textContent = enfermedad.nombre;
            
            // Categoría
            const catEl = document.getElementById('modalCategoria');
            const catMap = { 'mental': 'Psiquiátrica', 'fisica': 'Médica', 'biopsicosocial': 'Biopsicosocial' };
            const catClasses = { 
                'mental': 'bg-indigo-50 text-indigo-600', 
                'fisica': 'bg-rose-50 text-rose-600', 
                'biopsicosocial': 'bg-emerald-50 text-emerald-600' 
            };
            catEl.textContent = catMap[enfermedad.categoria] || 'General';
            catEl.className = 'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider ' + (catClasses[enfermedad.categoria] || 'bg-slate-100 text-slate-600');
            
            // Tipo
            const tipoEl = document.getElementById('modalTipo');
            if (enfermedad.tipo) {
                tipoEl.textContent = enfermedad.tipo;
                tipoEl.classList.remove('hidden');
            } else {
                tipoEl.classList.add('hidden');
            }
            
            // Descripción
            document.getElementById('modalDescripcion').textContent = enfermedad.descripcion || 'Sin notas o descripción adicional registrada.';
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModal() {
            const modal = document.getElementById('modalDetalles');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Cerrar con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') cerrarModal();
        });
    </script>
</x-app-layout>
