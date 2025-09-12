<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
                Registros Arquivados (Lixeira)
            </h2>
            <a href="{{ route('registros.index') }}"
               class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
               Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-200">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($registros as $r)
                    @php
                        $capa = $r->imagens->firstWhere('posicao', 'frente') ?? $r->imagens->first();
                        $capaUrl = $capa ? asset('storage/'.$capa->path) : 'https://via.placeholder.com/640x360?text=Sem+Foto';
                    @endphp

                    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700">
                        <img src="{{ $capaUrl }}" class="h-48 w-full object-cover" alt="Capa" loading="lazy">
                        <div class="p-4">
                            <div class="mb-2 flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $r->placa }}</h3>
                                <span class="rounded-full bg-gray-200 dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-800 dark:text-gray-200">
                                    Deletado em {{ optional($r->deleted_at)->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $r->marca?->nome }} — {{ $r->modelo }}</p>

                            <div class="mt-4 flex gap-2">
                                {{-- RESTAURAR --}}
                                <form method="POST" action="{{ route('registros.restore', $r->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-md bg-emerald-600 px-3 py-1.5 text-white hover:bg-emerald-700">
                                        Restaurar
                                    </button>
                                </form>

                                {{-- EXCLUIR DEFINITIVAMENTE (com limpeza de arquivos no controller) --}}
                                <form method="POST" action="{{ route('registros.forceDelete', $r->id) }}"
                                      onsubmit="return confirm('Remover permanentemente (apaga também os arquivos)?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-md bg-rose-700 px-3 py-1.5 text-white hover:bg-rose-800">
                                        Excluir definitivamente
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="rounded-md border border-dashed p-8 text-center text-sm text-gray-500 dark:text-gray-300">
                            Nenhum registro na lixeira.
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $registros->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
