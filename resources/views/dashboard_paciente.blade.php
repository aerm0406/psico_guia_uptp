<x-app-layout>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border-l-8 border-blue-700">
                <div class="p-8 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-800">Bienvenido a Psico-Guía {{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}.</h3>
                    <p class="mt-4 text-sm text-gray-600">Pide tu cita del psicólogo de la UPTP con el que te sientas en más confianza.</p>
                    <a href="{{ route('citas.index') }}" class="inline-flex items-center mt-6 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Ir a Mis Citas</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
