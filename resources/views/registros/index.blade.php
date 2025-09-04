<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">Registros</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-4">
                <a href="{{ route('registros.create') }}"
                   class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-white shadow hover:bg-indigo-700">
                    Novo Registro
                </a>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Placa</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Modelo</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Marca</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Fotos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($registros as $r)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $r->placa }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $r->modelo }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $r->marca?->nome }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        @foreach ($r->imagens as $img)
                                            <img src="{{ asset('storage/'.$img->path) }}"
                                                 class="h-10 w-10 rounded object-cover" alt="">
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-sm text-gray-500">Sem registros ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $registros->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
