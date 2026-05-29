<x-guest-layout>
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">419 | Página expirada</h1>
            <p class="mt-4 text-gray-600">Tu sesión o token de seguridad expiró. Por favor, recarga la página y vuelve a intentarlo.</p>
            <div class="mt-6">
                <a href="{{ url('/login') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Ir al inicio de sesión</a>
            </div>
        </div>
    </div>
</x-guest-layout>
