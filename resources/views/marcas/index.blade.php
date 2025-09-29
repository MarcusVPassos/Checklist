<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Marcas</h2>

            @can('marcas.create')
            <button type="button"
                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700
                       dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
                x-data
                x-on:click="$dispatch('open-modal', 'criar-marca')">
                Nova Marca
            </button>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 space-y-6">

                    {{-- Filtros --}}
                    <form method="GET" action="{{ route('marcas.index') }}" class="flex gap-2">
                        <input type="text" name="q" value="{{ request('q') }}"
                               class="w-full rounded border px-3 py-2
                                      bg-white dark:bg-gray-900
                                      text-gray-900 dark:text-gray-100
                                      placeholder-gray-400 dark:placeholder-gray-500
                                      border-gray-300 dark:border-gray-600
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
                               placeholder="Buscar por nome...">
                        <x-primary-button>Filtrar</x-primary-button>
                        @if(request()->has('q') && request('q')!=='')
                            <a href="{{ route('marcas.index') }}"
                               class="px-3 py-2 rounded border
                                      text-gray-700 dark:text-gray-200
                                      bg-white dark:bg-gray-900
                                      border-gray-300 dark:border-gray-600
                                      hover:bg-gray-50 dark:hover:bg-gray-800
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
                                Limpar
                            </a>
                        @endif
                    </form>

                    {{-- Tabela --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="py-2 pr-4">Nome</th>
                                    <th class="py-2 pr-4">Criada em</th>
                                    <th class="py-2 pr-4 w-0">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200/60 dark:divide-gray-700/60">
                                @forelse($marcas as $m)
                                    <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900">
                                        <td class="py-2 pr-4 text-gray-900 dark:text-gray-100">{{ $m->nome }}</td>
                                        <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                                        <td class="py-2 pr-0 text-right">
                                            @can('marcas.update')
                                            <button type="button"
                                                class="inline-flex items-center rounded-md border px-3 py-1.5
                                                       text-gray-700 dark:text-gray-200
                                                       bg-white dark:bg-gray-900
                                                       border-gray-300 dark:border-gray-600
                                                       hover:bg-gray-50 dark:hover:bg-gray-800
                                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
                                                x-data
                                                x-on:click="$dispatch('open-modal', 'editar-marca-{{ $m->id }}')">
                                                Editar
                                            </button>
                                            @endcan
                                        </td>
                                    </tr>

                                    {{-- Modal Editar --}}
                                    @can('marcas.update')
                                    <x-modal name="editar-marca-{{ $m->id }}" :show="false" focusable maxWidth="2xl">
                                        <div class="p-6 bg-white dark:bg-gray-800">
                                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                                                Editar marca — {{ $m->nome }}
                                            </h3>

                                            @include('marcas.partials.form', [
                                                'action' => route('marcas.update', $m->id),
                                                'method' => 'PUT',
                                                'marca' => $m
                                            ])
                                        </div>
                                    </x-modal>
                                    @endcan
                                @empty
                                    <tr class="bg-white dark:bg-gray-800">
                                        <td colspan="5" class="py-6 text-center text-gray-500 dark:text-gray-400">
                                            Nenhuma marca encontrada.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginação --}}
                    <div class="text-gray-700 dark:text-gray-200">
                        {{ $marcas->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Criar --}}
    @can('marcas.create')
    <x-modal name="criar-marca" :show="false" focusable maxWidth="2xl">
        <div class="p-6 bg-white dark:bg-gray-800">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                Nova marca
            </h3>

            @include('marcas.partials.form', [
                'action' => route('marcas.store'),
                'method' => 'POST',
                'marca' => null
            ])
        </div>
    </x-modal>
    @endcan
</x-app-layout>
