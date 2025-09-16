<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Novo Usuário</h2>
            <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:underline">Voltar</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="name" value="Nome" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                      value="{{ old('name') }}" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                      value="{{ old('email') }}" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="password" value="Senha" />
                            <x-text-input id="password" name="password" type="password"
                                          class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('password')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="password_confirmation" value="Confirmar Senha" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                                          class="mt-1 block w-full" required />
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Papéis (opcional)" />
                        <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-2">
                            @foreach($roles as $role)
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <x-secondary-button type="button" onclick="history.back()">Cancelar</x-secondary-button>
                        <x-primary-button type="submit">Salvar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
