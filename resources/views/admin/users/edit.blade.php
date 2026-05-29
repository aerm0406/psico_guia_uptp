<x-app-layout>
    <div class="pt-12 pb-4 bg-slate-50 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs / Back Link -->
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-900 text-sm font-medium mb-6 transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Volver al listado
            </a>

            <!-- Header -->
            <div class="mb-10 text-slate-900">
                <h1 class="text-3xl font-extrabold tracking-tight">Editar Usuario</h1>
                <p class="mt-2 text-slate-500 text-sm italic">Modificando a: <span class="font-bold text-indigo-600">{{ $usuario->name }} </span></p>{{-- ({{ $usuario->email }}) --}}
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden text-slate-900">
                <form action="{{ route('admin.users.update', $usuario->id) }}" method="POST" class="p-8">
                    @csrf
                    @method('PUT')
                    @include('admin.users.components.user_form', ['usuario' => $usuario])

                    <div class="mt-10 flex items-center justify-end gap-3 border-t border-slate-50 pt-8">
                        <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all">
                            Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
