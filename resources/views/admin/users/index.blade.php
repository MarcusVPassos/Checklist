<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Usuários</h2>

            @can('users.create')
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
               Novo Usuário
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/20">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase">Nome</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase">Papéis</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($users as $u)
                            <tr>
                                <td class="px-4 py-2">{{ $u->name }}</td>
                                <td class="px-4 py-2">{{ $u->email }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($u->roles as $r)
                                            <span class="rounded bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs">{{ $r->name }}</span>
                                        @empty
                                            <span class="text-xs text-gray-500">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        @canany(['users.assign-roles','users.assign-permissions'])
                                        <a href="{{ route('admin.users.roles-perms', $u) }}"
                                           class="rounded border px-3 py-1 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                                           Permissões
                                        </a>
                                        @endcanany

                                        @can('users.delete')
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                              onsubmit="return confirm('Remover este usuário?')">
                                            @csrf @method('DELETE')
                                            <button class="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700">
                                                Remover
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-3">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
