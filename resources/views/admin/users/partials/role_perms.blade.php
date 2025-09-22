{{-- admin/users/partials/roles_perms.blade.php --}}
<form method="POST" action="{{ route('admin.users.roles-perms.update', $user) }}" class="space-y-6">
    @csrf
    @method('PUT')

    {{-- ROLES --}}
    @can('users.assign-roles')
        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Papéis</h4>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
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
    @endcan

    <hr class="border-gray-200 dark:border-gray-700" />

    {{-- PERMISSÕES DIRETAS --}}
    @can('users.assign-permissions')
        <div>
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Permissões (diretas)</h4>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 max-h-80 overflow-auto pr-1">
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
    @endcan

    <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
        <x-secondary-button type="button" x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
        <x-primary-button type="submit">Atualizar</x-primary-button>
    </div>
</form>
