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

    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700">
        <button type="button" class="block w-full text-left" {{-- Botão que dispara um CustomEvent global para abrir o modal (Alpine escuta) --}}
            @click.prevent="window.dispatchEvent(new CustomEvent('abrir-registro', { detail: { id: {{ $r->id }} } }))">
            <img src="{{ $capaUrl }}" class="h-48 w-full object-cover" alt="Capa" loading="lazy"> {{-- Imagem de capa; loading="lazy" ajuda na performance --}}
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
                <div class="mt-4">
                    <span class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">Ver detalhes</span>
                </div>
            </div>
        </button>
    </div>
@endforeach
