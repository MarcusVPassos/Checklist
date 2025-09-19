<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                Papéis & Permissões — {{ $user->name }}
            </h2>
            <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:underline">Voltar</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/20">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.roles-perms.update', $user) }}"
                class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Papéis</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                        {{-- ROLES --}}
                        @foreach ($roles as $role)
                            @can('assign-role', [$role->name, $user])
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                        @checked($user->roles->pluck('id')->contains($role->id))
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ $role->name }}</span>
                                </label>
                            @endcan
                        @endforeach
                    </div>
                </div>

                <hr class="border-gray-200 dark:border-gray-700" />

                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Permissões (diretas)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 max-h-80 overflow-auto pr-1">

                        {{-- PERMISSÕES --}}
                        @foreach ($permissions as $perm)
                            @can('assign-permission', $perm->name)
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                        @checked($user->permissions->pluck('id')->contains($perm->id))
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm">{{ $perm->name }}</span>
                                </label>
                            @endcan
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        Dica: prefira conceder via papéis. Permissões diretas são úteis para exceções.
                    </p>
                </div>

                <div class="flex justify-end gap-2">
                    <x-secondary-button type="button" onclick="history.back()">Cancelar</x-secondary-button>
                    <x-primary-button type="submit">Atualizar</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
