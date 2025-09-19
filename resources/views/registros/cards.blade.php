@php
    // Tabela de rótulos (pode ser usada para labels de imagens/posições, etc.)
    $rotulos = [
        'frente' => 'Frente',
        'lado_direito' => 'Lado direito',
        'lado_esquerdo' => 'Lado esquerdo',
        'traseira' => 'Traseira',
        'capo_aberto' => 'Capô aberto',
        'numero_do_motor' => 'Número do motor',
        'painel_lado_direito' => 'Painel (lado direito)',
        'painel_lado_esquerdo' => 'Painel (lado esquerdo)',
        'bateria_carro' => 'Bateria (carro)',
        'chave_carro' => 'Chave (carro)',
        'estepe_do_veiculo' => 'Estepe',
        'motor_lado_direito' => 'Motor (lado direito)',
        'motor_lado_esquerdo' => 'Motor (lado esquerdo)',
        'painel_moto' => 'Painel (moto)',
        'chave_moto' => 'Chave (moto)',
        'bateria_moto' => 'Bateria (moto)',
    ];
@endphp

@foreach ($registros as $r)
    @php
        $capa = $r->imagens->firstWhere('posicao', 'frente') ?? $r->imagens->first(); // Escolhe a "capa": tenta 'frente', senão a primeira imagem disponível
        $capaUrl = $capa ? asset('storage/' . $capa->path) : 'https://via.placeholder.com/640x360?text=Sem+Foto'; // asset('storage/...') cria URL pública para arquivos no disco "public"
    @endphp

    <div
        class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700">
        <button type="button" class="block w-full text-left" {{-- Botão que dispara um CustomEvent global para abrir o modal (Alpine escuta) --}}
            @click.prevent="window.dispatchEvent(new CustomEvent('abrir-registro', { detail: { id: {{ $r->id }} } }))">
            <span class="ml-1 text-md font-medium text-gray-800 dark:text-gray-200">
                Criado em {{ $r->created_at?->format('d/m/Y H:i') ?? '—' }} {{-- por {{$r->user_id}} --}}
            </span>
            <img src="{{ $capaUrl }}" class="h-48 w-full object-cover" alt="Capa" loading="lazy">
            {{-- Imagem de capa; loading="lazy" ajuda na performance --}}
            <div class="p-4">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $r->placa }}</h3>
                    {{-- Badge de status (no pátio / saiu) --}}
                    @if ($r->no_patio)
                        <span
                            class="rounded-full bg-green-100 dark:bg-green-900/40 px-2 py-1 text-xs font-medium text-green-800 dark:text-green-200">No
                            pátio</span>
                    @else
                        <span
                            class="rounded-full bg-gray-200 dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-800 dark:text-gray-200">Saiu</span>
                    @endif
                </div>
                {{-- Operador null-safe em $r->marca?->nome evita erro se a relação não existir --}}
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $r->marca?->nome }} — {{ $r->modelo }}</p>
            </div>
        </button>
        <div class="flex gap-2 justify-end mb-2 mr-2">
            @can('registros.update')
            <x-primary-button type="button"
                @click.prevent="window.dispatchEvent(new CustomEvent('editar-registro', { detail: { id: {{ $r->id }} } }))">
                Editar
            </x-primary-button>
            @endcan
            <!-- BOTÃO QUE ABRE O MODAL -->
            @if ($r->no_patio)
                <x-secondary-button type="button"
                    x-on:click="$dispatch('open-modal', 'confirmar-status-{{ $r->id }}')">
                    Marcar como saiu
                </x-secondary-button>
            @else
                <x-primary-button type="button"
                    x-on:click="$dispatch('open-modal', 'confirmar-status-{{ $r->id }}')">
                    Marcar como no pátio
                </x-primary-button>
            @endif

            <!-- MODAL DE CONFIRMAÇÃO de status -->
            <x-modal name="confirmar-status-{{ $r->id }}" :show="false">
                <form method="POST" action="{{ route('registros.togglePatio', $r->id) }}" class="p-4">
                    @csrf
                    @method('PATCH')

                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Tem certeza que deseja alterar o status?
                    </h2>

                    <div class="mt-4 flex justify-end space-x-2">
                        <x-secondary-button type="button" x-on:click="$dispatch('close')">
                            Cancelar
                        </x-secondary-button>

                        <x-primary-button type="submit">
                            Confirmar
                        </x-primary-button>
                    </div>
                </form>
            </x-modal>
            
            @can('registros.delete')
            {{-- EXCLUIR (softDelete) --}}
            <x-danger-button type="button"
                x-on:click="$dispatch('open-modal', 'confirmar-exclusao-{{ $r->id }}')">
                Excluir
            </x-danger-button>
            @endcan

            <!-- MODAL DE CONFIRMAÇÃO de exclusão -->
            <x-modal name="confirmar-exclusao-{{ $r->id }}" :show="false">
                <form method="POST" action="{{ route('registros.destroy', $r->id) }}" class="p-4">
                    @csrf
                    @method('DELETE')

                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Tem certeza que deseja excluir?
                    </h2>

                    <div class="mt-4 flex justify-end space-x-2">
                        <x-secondary-button type="button" x-on:click="$dispatch('close')">
                            Cancelar
                        </x-secondary-button>
                        <x-primary-button type="submit">
                            Confirmar
                        </x-primary-button>
                    </div>
                </form>
            </x-modal>
        </div>
    </div>
@endforeach
