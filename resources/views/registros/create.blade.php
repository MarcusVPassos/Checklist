<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">Novo Registro</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow">
                <form method="POST"
                      action="{{ route('registros.store') }}"
                      enctype="multipart/form-data"
                      class="space-y-6">
                    @csrf

                    {{-- Linha 1: Placa, Tipo, Marca --}}
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div>
                            <x-input-label for="placa" value="Placa" />
                            <x-text-input id="placa" name="placa" type="text" class="mt-1 block w-full"
                                          value="{{ old('placa') }}" required />
                            <x-input-error :messages="$errors->get('placa')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tipo" value="Tipo" />
                            <select id="tipo" name="tipo" class="mt-1 block w-full rounded-md border-gray-300"
                                    required>
                                <option value="carro" @selected(old('tipo')==='carro')>Carro</option>
                                <option value="moto"  @selected(old('tipo')==='moto')>Moto</option>
                            </select>
                            <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="marca_id" value="Marca" />
                            <select id="marca_id" name="marca_id"
                                    class="mt-1 block w-full rounded-md border-gray-300" required>
                                <option value="" disabled {{ old('marca_id') ? '' : 'selected' }}>Selecione...</option>
                                @foreach($marcas as $marca)
                                    <option value="{{ $marca->id }}" @selected(old('marca_id')==$marca->id)>
                                        {{ $marca->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('marca_id')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Linha 2: Modelo, No pátio --}}
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <x-input-label for="modelo" value="Modelo" />
                            <x-text-input id="modelo" name="modelo" type="text"
                                          class="mt-1 block w-full"
                                          value="{{ old('modelo') }}" required />
                            <x-input-error :messages="$errors->get('modelo')" class="mt-2" />
                        </div>

                        <div class="flex items-end">
                            {{-- truque para checkbox sempre enviar algo --}}
                            <input type="hidden" name="no_patio" value="0">
                            <label class="inline-flex items-center gap-2">
                                <input id="no_patio" type="checkbox" name="no_patio" value="1"
                                       @checked(old('no_patio', 1))
                                       class="rounded border-gray-300">
                                <span>No pátio</span>
                            </label>
                            <x-input-error :messages="$errors->get('no_patio')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Observação --}}
                    <div>
                        <x-input-label for="observacao" value="Observação" />
                        <textarea id="observacao" name="observacao"
                                  class="mt-1 block w-full rounded-md border-gray-300"
                                  rows="3">{{ old('observacao') }}</textarea>
                        <x-input-error :messages="$errors->get('observacao')" class="mt-2" />
                    </div>

                    {{-- Reboque (OBRIGATÓRIO) --}}
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <x-input-label for="reboque_condutor" value="Condutor do Reboque" />
                            <x-text-input id="reboque_condutor" name="reboque_condutor" type="text"
                                          class="mt-1 block w-full"
                                          value="{{ old('reboque_condutor') }}" required />
                            <x-input-error :messages="$errors->get('reboque_condutor')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="reboque_placa" value="Placa do Reboque" />
                            <x-text-input id="reboque_placa" name="reboque_placa" type="text"
                                          class="mt-1 block w-full"
                                          value="{{ old('reboque_placa') }}" required />
                            <x-input-error :messages="$errors->get('reboque_placa')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Itens (N:N) --}}
                    <div>
                        <x-input-label value="Itens" />
                        <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            @foreach($itens as $item)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="itens[]" value="{{ $item->id }}"
                                           @checked(collect(old('itens', []))->contains($item->id))
                                           class="rounded border-gray-300">
                                    <span>{{ $item->nome }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('itens')" class="mt-2" />
                        <x-input-error :messages="$errors->get('itens.*')" class="mt-2" />
                    </div>

                    {{-- Assinatura (obrigatória) --}}
                    <div>
                        <x-input-label for="assinatura" value="Assinatura (obrigatória)" />
                        <input id="assinatura" name="assinatura" type="file"
                               class="mt-1 block w-full text-sm text-gray-900 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-white hover:file:bg-indigo-700"
                               required accept=".png,.jpg,.jpeg">
                        <x-input-error :messages="$errors->get('assinatura')" class="mt-2" />
                    </div>

                    {{-- Fotos múltiplas --}}
                    <div>
                        <x-input-label for="fotos" value="Fotos (múltiplas)" />
                        <input id="fotos" name="fotos[]" type="file" multiple accept=".jpg,.jpeg,.png,.webp">

                        <x-input-error :messages="$errors->get('fotos')" class="mt-2" />
                        <x-input-error :messages="$errors->get('fotos.*')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>Salvar</x-primary-button>
                        <a href="{{ route('registros.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                    </div>

                    {{-- bloco de erros gerais (fallback) --}}
                    @if ($errors->any())
                        <ul class="mt-4 list-disc pl-5 text-sm text-red-600">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
