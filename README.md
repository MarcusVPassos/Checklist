## ----------------------------------------------------------------------------------------------- ##
# Spatie Permissions
Blade: esconder botões/áreas por permissão (UI segura) || @can/@cannot/@canany (Laravel) e diretivas do Spatie para roles existem, mas a boa prática é basear o UI em permissões.
  a) Mostrar “Cadastrar usuário” só pra quem pode criar usuários
    @can('users.create')
      <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Cadastrar Usuário</a>
    @endcan

  b) Botão “Conceder permissões” só pra quem pode atribuir
  @can('users.assign-roles')
    <a href="{{ route('admin.users.roles-perms', $user) }}" class="btn">Conceder Permissões</a>
  @endcan

  c) Exemplos no seu contexto (Registros)
  @can('registros.delete')
    <form method="POST" action="{{ route('registros.destroy', $r->id) }}">
        @csrf @method('DELETE')
        <x-danger-button>Excluir</x-danger-button>
    </form>
  @endcan

Proteger Rotas/Controladores com middleware:
  Ex.: somente quem tem a permissão pode acessar as rotas de usuários 
  - Route::middleware(['auth','permission:users.view'])->get('/admin/users', ...);
## -------------------------------------------------------------------------------------------------- ##

## -------------------------------------------------------------------------------------------------- ##
# Spatie Activity Log
No Model usar *use LogActivity*
  precisar por a função getActivityOptions
  -   public function getActivitylogOptions(): LogOptions
      {
          return LogOptions::defaults()
              ->logOnly(['xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx']);
      }    
## -------------------------------------------------------------------------------------------------- ##

# 🚗 Sistema CheckList
## 📌 Resumo
O sistema tem como objetivo gerenciar **entradas e saídas de veículos** (carros e motos), permitindo o registro detalhado de informações, fotos, itens e status.  
Conta também com **painel de controle**, listagem, filtros e permissões diferenciadas para usuários e administradores.  
* -----------------------------------------------------------------------------------------------------------------------------------*

## ⚙️ Funcionalidades Principais
- Registro de **Entrada** e **Saída** de veículos.  
- Tipos de entrada: **Carro** ou **Moto** (com pedidos de fotos diferentes).  
- **Listas personalizáveis**:
  - Marcas de carro (ex.: Fiat, Volkswagen, Ford etc.).  
  - Itens do veículo (ex.: Macaco, Farol de Milha, Triângulo, Câmbio etc.).  
- **Painel de status**:
  - Quantidade total de veículos no pátio.  
  - Estatísticas de entrada e saída por mês (com filtro).  
- **Pesquisa inteligente**:
  - Busca por **Modelo**, **Placa** ou **Status**.  

* -----------------------------------------------------------------------------------------------------------------------------------*
## 📝 Estrutura dos Registros
Cada veículo terá os seguintes campos:  
- **Tipo:** Carro ou Moto.  
- **Status:** No pátio ou Saiu.  
- **Marca** (selecionável) / **Modelo** (texto livre).  
- **Fotos** do veículo.  [path:/public]
- **Itens do veículo** (checkbox/multiselect).  
- **Observação** adicional.  
- **Reboque**:
  - Nome do condutor.  
  - Placa do reboque.  
  - Assinatura do responsável.  [path:/assinatura_path]

* -----------------------------------------------------------------------------------------------------------------------------------*

## 📊 Painel
- Exibe o **total de veículos no pátio**.  
- Mostra **entradas e saídas por mês** com filtro de período.  

* -----------------------------------------------------------------------------------------------------------------------------------*

## 👥 Usuários e Permissões
### 🔹 Usuário
- Cadastro de veículos (com fotos).  
- Assinatura automática (associada ao usuário logado).  
- Pode **editar** e **excluir** registros (soft delete).  

### 🔹 Admin
- Registro e gerenciamento de **usuários** dentro do sistema.  
- Visualização de **histórico de edição**.  
- Permissão para **excluir definitivamente** registros em soft delete.  

* -----------------------------------------------------------------------------------------------------------------------------------*

## 🛠️ Observação
Este documento poderá ser atualizado conforme novas necessidades surgirem.  
A documentação também deve incluir **orientações de uso para cada tipo de usuário**.  

* -----------------------------------------------------------------------------------------------------------------------------------*






------------------
Para não esquecer enquanto estou fazendo o projeto

storage link para uso de fotos;  -> criação de novo path em filesystem para assinatura. [OK]
laravel log activies
breeze para roles [OK]
darkmode do brezze [OK]
laravel lang [OK]
laravel permissions [OK]

