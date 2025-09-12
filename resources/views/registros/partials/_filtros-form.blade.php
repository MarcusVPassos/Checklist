@props(['modo' => 'desktop', 'marcas', 'itens', 'usuarios', 'anos', 'mesesPorAno'])

<form method="GET"
      x-data="filtrosCompactos(@js($mesesPorAno))"
      class="{{ $modo === 'desktop'
                ? 'grid gap-3 md:grid-cols-6 lg:grid-cols-8'
                : 'grid gap-3 grid-cols-1' }}">

    {{-- Placa --}}
    <div class="{{ $modo === 'desktop' ? 'md:col-span-2' : '' }}">
        <x-input-label for="placa" value="Placa" class="text-xs" />
        <x-text-input id="placa" name="placa" type="text"
            class="mt-1 block w-full text-sm h-10 uppercase"
            value="{{ request('placa') }}" />
    </div>

    {{-- Modelo --}}
    <div class="{{ $modo === 'desktop' ? 'md:col-span-2' : '' }}">
        <x-input-label for="modelo" value="Modelo (contém)" class="text-xs" />
        <x-text-input id="modelo" name="modelo" type="text"
            class="mt-1 block w-full text-sm h-10"
            value="{{ request('modelo') }}" />
    </div>

    {{-- Marca --}}
    <div>
        <x-input-label for="marca_id" value="Marca" class="text-xs" />
        <select id="marca_id" name="marca_id"
            class="mt-1 block w-full text-sm h-10 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            <option value="">Todas</option>
            @foreach($marcas as $m)
                <option value="{{ $m->id }}" @selected(request('marca_id') == $m->id)>{{ $m->nome }}</option>
            @endforeach
        </select>
    </div>

    {{-- Item --}}
    <div>
        <x-input-label for="item_id" value="Item" class="text-xs" />
        <select id="item_id" name="item_id"
            class="mt-1 block w-full text-sm h-10 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            <option value="">Todos</option>
            @foreach($itens as $i)
                <option value="{{ $i->id }}" @selected(request('item_id') == $i->id)>{{ $i->nome }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tipo --}}
    <div>
        <x-input-label for="tipo" value="Tipo" class="text-xs" />
        <select id="tipo" name="tipo"
            class="mt-1 block w-full text-sm h-10 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            <option value="">Todos</option>
            <option value="carro" @selected(request('tipo')==='carro')>Carro</option>
            <option value="moto"  @selected(request('tipo')==='moto')>Moto</option>
        </select>
    </div>

    {{-- Status --}}
    <div>
        <x-input-label for="status_patio" value="Status (Pátio)" class="text-xs" />
        <select id="status_patio" name="status_patio"
            class="mt-1 block w-full text-sm h-10 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            <option value="">Todos</option>
            <option value="no_patio" @selected(request('status_patio')==='no_patio')>No pátio</option>
            <option value="saiu"     @selected(request('status_patio')==='saiu')>Saiu</option>
        </select>
    </div>

    {{-- Usuário --}}
    <div class="{{ $modo === 'desktop' ? 'lg:col-span-2' : '' }}">
        <x-input-label for="user_id" value="Usuário" class="text-xs" />
        <select id="user_id" name="user_id"
            class="mt-1 block w-full text-sm h-10 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            <option value="">Todos</option>
            @foreach($usuarios as $u)
                <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- De / Até --}}
    <div>
        <x-input-label for="from" value="De" class="text-xs" />
        <x-text-input id="from" name="from" type="date"
            class="mt-1 block w-full text-sm h-10"
            value="{{ request('from') }}" />
    </div>
    <div>
        <x-input-label for="to" value="Até" class="text-xs" />
        <x-text-input id="to" name="to" type="date"
            class="mt-1 block w-full text-sm h-10"
            value="{{ request('to') }}" />
    </div>

    {{-- Ano --}}
    <div>
        <x-input-label for="ano" value="Ano" class="text-xs" />
        <select id="ano" name="ano" x-model="ano"
            class="mt-1 block w-full text-sm h-10 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            <option value="">Todos</option>
            @foreach($anos as $a)
                <option value="{{ $a->ano }}" @selected((string)request('ano')===(string)$a->ano)>{{ $a->ano }}</option>
            @endforeach
        </select>
    </div>

    {{-- Mês (nomes; depende do ano) --}}
    <div>
        <x-input-label for="mes" value="Mês" class="text-xs" />
        <select id="mes" name="mes" x-model="mes"
            class="mt-1 block w-full text-sm h-10 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            <option value="">Todos</option>

            {{-- quando há ano, renderize somente os meses existentes no ano --}}
            <template x-if="ano">
                <template x-for="m in mesesDoAno()" :key="'a'+m.mes">
                    <option :value="m.mes" :selected="String(m.mes) === '{{ request('mes') }}'">
                        <span x-text="m.nome"></span>
                    </option>
                </template>
            </template>

            {{-- sem ano: meses globais (únicos) --}}
            <template x-if="!ano">
                <template x-for="m in mesesGlobais()" :key="'g'+m.mes">
                    <option :value="m.mes" :selected="String(m.mes) === '{{ request('mes') }}'">
                        <span x-text="m.nome"></span>
                    </option>
                </template>
            </template>
        </select>
    </div>

    {{-- Ações --}}
    <div class="{{ $modo === 'desktop' ? 'md:col-span-6 lg:col-span-8' : '' }} flex gap-2">
        <x-primary-button class="h-10 px-3 text-sm w-full md:w-auto">Filtrar</x-primary-button>
        <x-secondary-button class="h-10 px-3 text-sm w-full md:w-auto"
            type="button" onclick="window.location='{{ route('registros.index') }}'">
            Limpar
        </x-secondary-button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
  // Este x-data fica disponível sempre que o partial for usado
  Alpine.data('filtrosCompactos', (mesesPorAno) => ({
      ano: '{{ request('ano') }}' || '',
      mes: '{{ request('mes') }}' || '',
      mesesPorAno,
      mesesDoAno() {
          if (!this.ano) return [];
          return this.mesesPorAno[this.ano] ?? [];
      },
      mesesGlobais() {
          const seen = new Set(), out = [];
          Object.values(this.mesesPorAno).forEach(arr => {
              arr.forEach(m => { if (!seen.has(m.mes)) { seen.add(m.mes); out.push(m); } });
          });
          out.sort((a,b) => a.mes - b.mes);
          return out;
      },
  }));
});
</script>
@endpush
