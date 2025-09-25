<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Marcas</h2>

            @can('marcas.create')
            <button type="button"
                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
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
                               class="w-full rounded border px-3 py-2 bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600"
                               placeholder="Buscar por nome...">
                        <x-primary-button>Filtrar</x-primary-button>
                        @if(request()->has('q') && request('q')!=='')
                            <a href="{{ route('marcas.index') }}" class="px-3 py-2 rounded border">Limpar</a>
                        @endif
                    </form>

                    {{-- Tabela --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="py-2 pr-4">Nome</th>
                                    <th class="py-2 pr-4">Criada em</th>
                                    <th class="py-2 pr-4 w-0">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200/60 dark:divide-gray-700/60">
                                @forelse($marcas as $m)
                                    <tr>
                                        <td class="py-2 pr-4">{{ $m->nome }}</td>
                                        <td class="py-2 pr-4">{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                                        <td class="py-2 pr-0 text-right">
                                            @can('marcas.update')
                                            <button type="button"
                                                class="inline-flex items-center rounded-md border px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-900"
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
                                        <div class="p-6">
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
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-gray-500 dark:text-gray-400">
                                            Nenhuma marca encontrada.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginação --}}
                    <div>{{ $marcas->withQueryString()->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Criar --}}
    @can('marcas.create')
    <x-modal name="criar-marca" :show="false" focusable maxWidth="2xl">
        <div class="p-6">
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
