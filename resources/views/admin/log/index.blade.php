<x-app-layout>
    {{-- Slot de cabeçalho do layout --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Logs do Sistema
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 space-y-6">

                    {{-- ===================== FORM DE FILTROS (GET) ===================== --}}
                    <form method="GET" action="{{ route('admin.logs.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">

                            {{-- Ação (select com valores distintos) --}}
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Ação</label>
                                <select name="action"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700">
                                    <option value="">-- Todas --</option>
                                    @foreach ($actions as $action)
                                        <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>
                                            {{ $action }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Model Type (FQCN) --}}
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Modelo</label>
                                <select name="model_type"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700">
                                    <option value="">-- Todos --</option>
                                    @foreach ($modelTypes as $type)
                                        <option value="{{ $type }}" @selected(($filters['model_type'] ?? '') === $type)>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Model ID (número) --}}
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">ID do Modelo</label>
                                <input type="number" name="model_id" inputmode="numeric" min="0"
                                    value="{{ $filters['model_id'] ?? '' }}"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700" />
                            </div>

                            {{-- Usuário causador --}}
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Usuário</label>
                                <select name="user_id"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700">
                                    <option value="">-- Todos --</option>
                                    @foreach ($users as $u)
                                        <option value="{{ $u->id }}" @selected(($filters['user_id'] ?? '') == $u->id)>
                                            {{ $u->name }} ({{ $u->id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Data inicial --}}
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">De (data)</label>
                                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700" />
                            </div>

                            {{-- Data final --}}
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Até (data)</label>
                                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700" />
                            </div>

                            {{-- Busca textual na descrição --}}
                            <div class="md:col-span-2 lg:col-span-2">
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Busca</label>
                                <input type="text" name="q" placeholder="Procurar na descrição..."
                                    value="{{ $filters['q'] ?? '' }}"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700" />
                            </div>
                        </div>

                        {{-- Botões de ação dos filtros --}}
                        <div class="flex items-center gap-3">
                            <button type="submit"
                                class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                                Filtrar
                            </button>

                            {{-- Link para limpar os filtros (voltar à rota sem query string) --}}
                            <a href="{{ route('admin.logs.index') }}"
                                class="px-4 py-2 rounded bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600">
                                Limpar
                            </a>
                        </div>
                    </form>
                    {{-- =================== FIM FORM DE FILTROS =================== --}}

                    {{-- ====== TABELA DE LOGS (simplificada e humana) ====== --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Evento
                                    </th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Por
                                    </th> {{-- <== cabeçalho, sem $log --}}
                                    <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Data
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($logs as $log)
                                    @php
                                        $c = $log->changes ?? [];
                                        $ator = $log->actor_name;
                                        $alvo = $c['target_name'] ?? ($log->model->name ?? ($c['placa'] ?? '—'));
                                        $perm = $c['permission'] ?? ($c['permission_name'] ?? ($c['perm'] ?? null));
                                        $role = $c['role'] ?? ($c['role_name'] ?? null);

                                        if (str_starts_with($log->action, 'permission-')) {
                                            $concedeu = $log->action === 'permission-attached';
                                            $verbo = $concedeu ? 'concedeu' : 'revogou';
                                            $prep = $concedeu ? 'para' : 'de';

                                            $desc = $perm
                                                ? "$ator $verbo a permissão {$perm} $prep {$alvo}"
                                                : "$ator $verbo uma permissão $prep {$alvo}";
                                        } elseif (str_starts_with($log->action, 'role-')) {
                                            $deu = $log->action === 'role-attached';
                                            $verbo = $deu ? 'deu acesso' : 'removeu o acesso';
                                            $prep = $deu ? 'para' : 'de';

                                            $desc = $role
                                                ? "$ator $verbo {$role} $prep {$alvo}"
                                                : "$ator $verbo $prep {$alvo}";
                                        } else {
                                            $desc = $log->human_description;
                                        }
                                    @endphp

                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        {{-- Evento --}}
                                        <td class="px-4 py-2 text-gray-800 dark:text-gray-200">
                                            {{ $desc }}
                                        </td>

                                        {{-- Por --}}
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                            {{ $ator }}
                                        </td>

                                        {{-- Data --}}
                                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">
                                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3"
                                            class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                            Nenhum log encontrado
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>



                    {{-- Paginação mantém filtros graças ao withQueryString() no controller --}}
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                    {{-- ================= FIM TABELA DE LOGS ================== --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
