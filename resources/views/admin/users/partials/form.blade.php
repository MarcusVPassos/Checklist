{{-- admin/users/partials/form.blade.php --}}
<form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
    @csrf

    <div>
        <x-input-label for="name" value="Nome" class="text-gray-700 dark:text-gray-300" />
        <x-text-input id="name" name="name" type="text"
            class="mt-1 block w-full
                   bg-white dark:bg-gray-900
                   text-gray-900 dark:text-gray-100
                   border-gray-300 dark:border-gray-600
                   placeholder-gray-400 dark:placeholder-gray-500
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
            value="{{ old('name') }}" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1 text-red-600 dark:text-red-400" />
    </div>

    <div>
        <x-input-label for="email" value="Email" class="text-gray-700 dark:text-gray-300" />
        <x-text-input id="email" name="email" type="email"
            class="mt-1 block w-full
                   bg-white dark:bg-gray-900
                   text-gray-900 dark:text-gray-100
                   border-gray-300 dark:border-gray-600
                   placeholder-gray-400 dark:placeholder-gray-500
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
            value="{{ old('email') }}" required />
        <x-input-error :messages="$errors->get('email')" class="mt-1 text-red-600 dark:text-red-400" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="password" value="Senha" class="text-gray-700 dark:text-gray-300" />
            <x-text-input id="password" name="password" type="password"
                class="mt-1 block w-full
                       bg-white dark:bg-gray-900
                       text-gray-900 dark:text-gray-100
                       border-gray-300 dark:border-gray-600
                       placeholder-gray-400 dark:placeholder-gray-500
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
                required />
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-red-600 dark:text-red-400" />
        </div>
        <div>
            <x-input-label for="password_confirmation" value="Confirmar Senha" class="text-gray-700 dark:text-gray-300" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                class="mt-1 block w-full
                       bg-white dark:bg-gray-900
                       text-gray-900 dark:text-gray-100
                       border-gray-300 dark:border-gray-600
                       placeholder-gray-400 dark:placeholder-gray-500
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
                required />
        </div>
    </div>

    <div>
        {{-- mostre a seção só se o ator puder atribuir papéis em geral --}}
        @can('users.assign-roles')
            <x-input-label value="Papéis (opcional)" class="text-gray-700 dark:text-gray-300" />
            <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-2">

                @foreach ($roles as $role)
                    {{-- aqui o alvo é um usuário NOVO, então passe só o nome (alvo = null) --}}
                    @can('assign-role', $role->name)
                        <label class="inline-flex items-center gap-2 text-gray-700 dark:text-gray-200">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                @checked(in_array($role->id, old('roles', [])))
                                class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-400 bg-white dark:bg-gray-900">
                            <span>{{ $role->name }}</span>
                        </label>
                    @endcan
                @endforeach

            </div>
        @endcan
    </div>

    <div class="flex justify-end gap-2">
        <x-secondary-button type="button" x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
        <x-primary-button type="submit">Salvar</x-primary-button>
    </div>
</form>
