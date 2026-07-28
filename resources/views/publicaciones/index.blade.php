<x-app-layout>
    <div class="pt-12 pb-4 bg-slate-50 dark:bg-gray-900 min-h-[calc(100vh-4rem)] flex flex-col">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex-1 flex flex-col w-full space-y-6">



            <!-- Tarjeta Crear Aviso -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden relative">
                <div class="absolute right-0 top-0 opacity-10 pointer-events-none">
                    <svg class="w-48 h-48 -mt-8 -mr-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
                <div class="p-6 relative z-10">
                    <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">CREAR AVISO</h3>
                    <a href="{{ route('publicaciones.create') }}" class="inline-flex items-center justify-center px-6 py-2.5 bg-blue-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm w-auto">
                        Nuevo Anuncio
                    </a>
                </div>
            </div>

            <!-- Feed de Publicaciones -->
            <div class="space-y-6">
                @forelse($publicaciones as $pub)
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <!-- Cabecera de la publicación -->
                        <div class="p-4 sm:p-5 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold overflow-hidden">
                                    @if(auth()->user()->profile_photo_path)
                                        <img src="{{ route('media.profile_photos', basename(auth()->user()->profile_photo_path)) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ substr(auth()->user()->nombres, 0, 1) }}{{ substr(auth()->user()->apellidos, 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">
                                        {{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ \Carbon\Carbon::parse($pub->created_at)->format('d/m/Y h:i A') }}
                                        &middot;
                                        <svg class="w-3 h-3 inline-block {{ $pub->alcance === 'todos' ? 'text-gray-400' : 'text-blue-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($pub->alcance === 'todos')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            @endif
                                        </svg>
                                    </p>
                                </div>
                            </div>
                            <!-- Opciones -->
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-full hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                </button>
                                
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-100" 
                                     x-transition:enter-start="transform opacity-0 scale-95" 
                                     x-transition:enter-end="transform opacity-100 scale-100" 
                                     x-transition:leave="transition ease-in duration-75" 
                                     x-transition:leave-start="transform opacity-100 scale-100" 
                                     x-transition:leave-end="transform opacity-0 scale-95" 
                                     class="absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 dark:divide-gray-700 z-50" 
                                     style="display: none;">
                                    <div class="py-1">
                                        <a href="{{ route('publicaciones.edit', $pub->id) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            Editar
                                        </a>
                                    </div>
                                    <div class="py-1">
                                        <form action="{{ route('publicaciones.destroy', $pub->id) }}" method="POST" onsubmit="event.preventDefault(); AppModal.show('Eliminar Publicación', '¿Seguro que deseas eliminar esta publicación?', { type: 'confirm', intent: 'danger' }).then(c => { if(c) this.submit(); });">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="group flex w-full items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                                <svg class="mr-3 h-5 w-5 text-red-500 group-hover:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="px-4 sm:px-5 pb-4">
                            @if($pub->tipo === 'color')
                                <div class="rounded-2xl {{ $pub->color_fondo ?? 'bg-gray-800' }} p-12 text-center aspect-video flex flex-col items-center justify-center shadow-inner">
                                    <h3 class="text-2xl font-bold text-white leading-snug">{{ $pub->titulo }}</h3>
                                    @if($pub->contenido)
                                        <p class="text-white/90 mt-4 text-sm">{{ $pub->contenido }}</p>
                                    @endif
                                </div>
                            @elseif($pub->tipo === 'imagen' && $pub->media_path)
                                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-2">{{ $pub->titulo }}</h3>
                                @if($pub->contenido)
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">{{ $pub->contenido }}</p>
                                @endif
                                <div class="rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-900 shadow-inner">
                                    <img @click="$dispatch('open-image-modal', '{{ route('media.publicaciones', basename($pub->media_path)) }}')" src="{{ route('media.publicaciones', basename($pub->media_path)) }}" class="w-full h-auto object-cover max-h-[500px] cursor-pointer hover:opacity-90 transition-opacity">
                                </div>
                            @else
                                <!-- Texto normal -->
                                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-2">{{ $pub->titulo }}</h3>
                                <div class="text-sm text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ $pub->contenido }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm">
                        <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No has creado ninguna publicación aún.</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Haz clic en "Nuevo Anuncio" para comenzar.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Image Modal -->
        <div x-data="{ open: false, src: '' }"
             @open-image-modal.window="src = $event.detail; open = true"
             x-show="open" 
             style="display: none;" 
             class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="open = false"
             @click.self="open = false">
             
             <!-- Close Button -->
             <button @click="open = false" class="absolute top-4 right-4 text-white hover:text-gray-300 p-2 rounded-full bg-black/50 transition-colors">
                 <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
             </button>
             
             <!-- Modal Image -->
             <img :src="src" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl" @click.stop>
        </div>
    </div>
</x-app-layout>
