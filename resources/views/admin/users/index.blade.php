{{-- resources/views/admin/users/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Usuários</h2>

            {{-- Botão: Criar Usuário --}}
            @can('users.create')
                <button
                    type="button"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 text-sm"
                    x-data
                    x-on:click="$dispatch('open-modal', 'criar-usuario')">
                    Novo Usuário
                </button>
            @endcan

            {{-- Modal: Criar Usuário --}}
            @can('users.create')
                <x-modal name="criar-usuario" :show="false" focusable maxWidth="2xl">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Novo Usuário
                        </h3>

                        @include('admin.users.partials.form', [
                            'action' => route('admin.users.store'),
                            'method' => 'POST',
                            'user' => null,
                            'roles' => $roles,
                        ])
                    </div>
                </x-modal>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

            {{-- Flash success --}}
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Filtro: ativos / deletados / todos --}}
            <form method="GET" class="mb-4">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <label class="text-sm text-gray-700 dark:text-gray-300">Mostrar:</label>
                    <select name="status"
                            class="w-full sm:w-auto rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm"
                            onchange="this.form.submit()">
                        <option value="" @selected(request('status') === null || request('status') === '')>Apenas ativos</option>
                        <option value="trashed" @selected(request('status') === 'trashed')>Apenas deletados</option>
                        <option value="all" @selected(request('status') === 'all')>Todos</option>
                    </select>
                </div>
            </form>

            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow">
                <div class="overflow-x-auto">
                    <table class="min-w-[720px] w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-[11px] sm:text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                    Nome
                                </th>
                                <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-[11px] sm:text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                    Email
                                </th>
                                <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-[11px] sm:text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                    Papéis
                                </th>
                                <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-[11px] sm:text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                    Ações
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($users as $u)
                                <tr class="bg-white dark:bg-gray-800">
                                    {{-- Nome + selo Deletado --}}
                                    <td class="px-3 py-2 sm:px-4 sm:py-3 text-gray-800 dark:text-gray-200 text-sm">
                                        {{ $u->name }}
                                        @if ($u->trashed())
                                            <span class="ml-2 rounded bg-amber-100 text-amber-800 text-xxs px-1.5 py-0.5">
                                                Deletado
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Email --}}
                                    <td class="px-3 py-2 sm:px-4 sm:py-3 text-gray-800 dark:text-gray-200 text-sm">
                                        {{ $u->email }}
                                    </td>

                                    {{-- Papéis --}}
                                    <td class="px-3 py-2 sm:px-4 sm:py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($u->roles as $r)
                                                <span class="rounded bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $r->name }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-gray-500 dark:text-gray-400">—</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    {{-- Ações --}}
                                    <td class="px-3 py-2 sm:px-4 sm:py-3">
                                        <div class="flex flex-wrap items-center gap-2">

                                            @if (!$u->trashed())
                                                {{-- ATIVO: Editar / Permissões / Remover (soft) --}}
                                                @can('users.update')
                                                    <button
                                                        class="rounded border px-2.5 py-1 text-xs sm:text-sm text-gray-800 hover:bg-gray-50 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700"
                                                        x-data
                                                        x-on:click="$dispatch('open-modal', 'editar-usuario-{{ $u->id }}')">
                                                        Editar
                                                    </button>
                                                @endcan

                                                @canany(['users.assign-roles', 'users.assign-permissions'])
                                                    <button
                                                        type="button"
                                                        class="rounded border px-2.5 py-1 text-xs sm:text-sm text-gray-800 hover:bg-gray-50 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700"
                                                        x-data
                                                        x-on:click="$dispatch('open-modal', 'roles-perms-{{ $u->id }}')">
                                                        Permissões
                                                    </button>
                                                @endcanany

                                                @can('users.delete')
                                                    <button
                                                        type="button"
                                                        x-on:click="$dispatch('open-modal', 'confirmar-exclusao-{{ $u->id }}')"
                                                        class="rounded bg-red-600 px-2.5 py-1 text-xs sm:text-sm text-white hover:bg-red-700">
                                                        Remover
                                                    </button>
                                                @endcan

                                                {{-- Modal: Remover (soft delete) --}}
                                                @can('users.delete')
                                                    <x-modal name="confirmar-exclusao-{{ $u->id }}" :show="false" focusable>
                                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="p-6">
                                                            @csrf @method('DELETE')

                                                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                                Excluir usuário <strong>{{ $u->name }}</strong>?
                                                            </h2>

                                                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                                Esta ação **não remove definitivamente**. Você poderá restaurar depois.
                                                            </p>

                                                            <div class="mt-6 flex flex-wrap justify-end gap-2">
                                                                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                                                                    Cancelar
                                                                </x-secondary-button>

                                                                <x-danger-button type="submit">
                                                                    Confirmar exclusão
                                                                </x-danger-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>
                                                @endcan

                                            @else
                                                {{-- DELETADO: Restaurar / Excluir definitivamente --}}
                                                @can('users.delete')
                                                    <form method="POST" action="{{ route('admin.users.restore', $u->id) }}">
                                                        @csrf @method('PATCH')
                                                        <button class="rounded border px-2.5 py-1 text-xs sm:text-sm dark:border-gray-600">
                                                            Restaurar
                                                        </button>
                                                    </form>

                                                    <form method="POST"
                                                          class="inline-block"
                                                          action="{{ route('admin.users.forceDelete', $u->id) }}"
                                                          onsubmit="return confirm('Excluir DEFINITIVAMENTE? Esta ação não poderá ser desfeita.');">
                                                        @csrf @method('DELETE')
                                                        <button class="rounded bg-red-600 px-2.5 py-1 text-xs sm:text-sm text-white hover:bg-red-700">
                                                            Excluir definitivamente
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                {{-- Modal: Editar Usuário --}}
                                @can('users.update')
                                    <x-modal name="editar-usuario-{{ $u->id }}" :show="false" focusable maxWidth="2xl">
                                        <div class="flex flex-col gap-1 p-6 md:p-8">
                                            <form method="POST" action="{{ route('admin.users.update', $u) }}" class="space-y-6 m-0">
                                                @csrf @method('PUT')

                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                    Editar usuário — {{ $u->name }}
                                                </h3>

                                                {{-- Nome --}}
                                                <div class="space-y-2">
                                                    <label class="block text-sm text-gray-700 dark:text-gray-300">Nome</label>
                                                    <input name="name" type="text" value="{{ old('name', $u->name) }}"
                                                           class="w-full rounded border px-3 py-2 text-gray-900 dark:text-gray-100
                                                                  bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600
                                                                  focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:focus:ring-indigo-500"
                                                           required>
                                                    @error('name') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                                                </div>

                                                {{-- Email --}}
                                                <div class="space-y-2">
                                                    <label class="block text-sm text-gray-700 dark:text-gray-300">E-mail</label>
                                                    <input name="email" type="email" value="{{ old('email', $u->email) }}"
                                                           class="w-full rounded border px-3 py-2 text-gray-900 dark:text-gray-100
                                                                  bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600
                                                                  focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:focus:ring-indigo-500"
                                                           required>
                                                    @error('email') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                                                </div>

                                                {{-- Nova senha (opcional) --}}
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="space-y-2">
                                                        <label class="block text-sm text-gray-700 dark:text-gray-300">
                                                            Nova senha (opcional)
                                                        </label>
                                                        <input name="password" type="password"
                                                               class="w-full rounded border px-3 py-2 text-gray-900 dark:text-gray-100
                                                                      bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600
                                                                      focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:focus:ring-indigo-500"
                                                               placeholder="Deixe vazio para manter">
                                                        @error('password') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="block text-sm text-gray-700 dark:text-gray-300">Confirmar nova senha</label>
                                                        <input name="password_confirmation" type="password"
                                                               class="w-full rounded border px-3 py-2 text-gray-900 dark:text-gray-100
                                                                      bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600
                                                                      focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:focus:ring-indigo-500">
                                                    </div>
                                                </div>

                                                {{-- Rodapé --}}
                                                <div class="pt-4 mt-2 flex flex-wrap justify-end gap-2 border-t border-gray-200 dark:border-gray-700">
                                                    <button type="button"
                                                            class="rounded border px-2.5 py-1 text-xs sm:text-sm text-gray-800 hover:bg-gray-50
                                                                   dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700"
                                                            x-on:click="$dispatch('close')">
                                                        Cancelar
                                                    </button>

                                                    <button class="rounded bg-indigo-600 px-2.5 py-1 text-xs sm:text-sm text-white hover:bg-indigo-700">
                                                        Salvar
                                                    </button>
                                                </div>

                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    Deixe os campos de senha em branco para manter a atual.
                                                </p>
                                            </form>
                                        </div>
                                    </x-modal>
                                @endcan

                                {{-- Modal: Permissões --}}
                                @can('users.assign-roles')
                                    <x-modal name="roles-perms-{{ $u->id }}" :show="false" focusable maxWidth="2xl">
                                        <div class="p-6">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                                Permissões
                                            </h3>

                                            @include('admin.users.partials.role_perms', [
                                                'user' => $u,
                                                'roles' => $roles,
                                                'permissions' => $permissions,
                                            ])
                                        </div>
                                    </x-modal>
                                @endcan

                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 sm:px-6">
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
