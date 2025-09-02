# 🚗 Sistema CheckList

## 📌 Resumo
O sistema tem como objetivo gerenciar **entradas e saídas de veículos** (carros e motos), permitindo o registro detalhado de informações, fotos, itens e status.  
Conta também com **painel de controle**, listagem, filtros e permissões diferenciadas para usuários e administradores.  

---

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

---

## 📝 Estrutura dos Registros
Cada veículo terá os seguintes campos:  
- **Tipo:** Carro ou Moto.  
- **Status:** No pátio ou Saiu.  
- **Marca** (selecionável) / **Modelo** (texto livre).  
- **Fotos** do veículo.  
- **Itens do veículo** (checkbox/multiselect).  
- **Observação** adicional.  
- **Reboque**:
  - Nome do condutor.  
  - Placa do reboque.  
  - Assinatura do responsável.  

---

## 📊 Painel
- Exibe o **total de veículos no pátio**.  
- Mostra **entradas e saídas por mês** com filtro de período.  

---

## 👥 Usuários e Permissões
### 🔹 Usuário
- Cadastro de veículos (com fotos).  
- Assinatura automática (associada ao usuário logado).  
- Pode **editar** e **excluir** registros (soft delete).  

### 🔹 Admin
- Registro e gerenciamento de **usuários** dentro do sistema.  
- Visualização de **histórico de edição**.  
- Permissão para **excluir definitivamente** registros em soft delete.  

---

## 🛠️ Observação
Este documento poderá ser atualizado conforme novas necessidades surgirem.  
A documentação também deve incluir **orientações de uso para cada tipo de usuário**.  








------------------
Para não esquecer enquanto estou fazendo o projeto

storage link para uso de fotos;
laravel log activies
breeze para roles
darkmode do brezze [OK]
laravel lang
laravel permissions

