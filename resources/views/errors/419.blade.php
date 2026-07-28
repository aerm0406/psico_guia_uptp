<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
        <div class="w-full max-w-md px-6 py-8 bg-white shadow-lg overflow-hidden rounded-2xl border border-gray-100">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">419 | Página expirada</h1>
                <p class="mt-4 text-gray-600 text-sm">Tu sesión o token de seguridad expiró. Por favor, recarga la página y vuelve a intentarlo.</p>
                <div class="mt-8">
                    <a href="{{ url('/login') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors font-semibold shadow-md">Ir al inicio de sesión</a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
