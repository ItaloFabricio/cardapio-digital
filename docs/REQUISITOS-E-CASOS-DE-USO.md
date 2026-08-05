# Requisitos e Casos de Uso — Cardápio Digital Inteligente

## 1. Atores

| Ator | Descrição |
|---|---|
| **Cliente** | Usuário final que navega no cardápio e realiza pedidos |
| **Administrador** | Responsável por gerenciar produtos, categorias, cupons e status dos pedidos |
| **Sistema** | Executa regras automáticas (ex.: bloqueio de pedido fora do horário) |

---

## 2. Requisitos Funcionais (RF)

| ID | Descrição | Ator |
|---|---|---|
| RF01 | O sistema deve permitir o cadastro de cliente (nome, email, senha) | Cliente |
| RF02 | O sistema deve autenticar o cliente via email e senha | Cliente |
| RF03 | O sistema deve listar as categorias de produtos disponíveis | Cliente |
| RF04 | O sistema deve listar os produtos de uma categoria | Cliente |
| RF05 | O sistema deve exibir os detalhes de um produto | Cliente |
| RF06 | O sistema deve permitir adicionar um produto ao pedido em construção | Cliente |
| RF07 | O sistema deve permitir remover um item do pedido antes da confirmação | Cliente |
| RF08 | O sistema deve permitir aplicar um cupom de desconto ao pedido | Cliente |
| RF09 | O sistema deve validar as regras do cupom (validade, valor mínimo, uso único) | Sistema |
| RF10 | O sistema deve calcular o valor total do pedido, considerando descontos | Sistema |
| RF11 | O sistema deve permitir a confirmação/criação do pedido | Cliente |
| RF12 | O sistema deve permitir consultar o status de um pedido | Cliente |
| RF13 | O sistema deve permitir a transição de status do pedido | Administrador |
| RF14 | O sistema deve impedir transições de status inválidas (ex.: Pendente → Entregue direto) | Sistema |
| RF15 | O administrador deve poder cadastrar, editar e inativar produtos | Administrador |
| RF16 | O administrador deve poder cadastrar e editar cupons | Administrador |
| RF17 | O sistema deve bloquear novos pedidos fora do horário de funcionamento | Sistema |
| RF18 | O administrador deve poder visualizar pedidos filtrados por status | Administrador |
| RF19 | O sistema deve autenticar o administrador separadamente do cliente | Administrador |

## 3. Requisitos Não-Funcionais (RNF)

| ID | Descrição | Categoria |
|---|---|---|
| RNF01 | Toda comunicação com o banco de dados deve usar PDO com prepared statements | Segurança |
| RNF02 | Senhas devem ser armazenadas com hash (`password_hash`, algoritmo padrão do PHP) | Segurança |
| RNF03 | A API deve responder em formato JSON | Interoperabilidade |
| RNF04 | O código deve seguir PSR-1 e PSR-12 | Manutenibilidade |
| RNF05 | Exceções não tratadas não devem expor detalhes internos (stack trace, query, credenciais) ao cliente | Segurança |
| RNF06 | O domínio não deve depender diretamente de nenhuma tecnologia de infraestrutura (DIP) | Escalabilidade |
| RNF07 | O sistema deve possuir testes unitários nas camadas Domain e Application | Qualidade |
| RNF08 | Toda entrada de usuário deve ser validada antes de alcançar a camada de domínio | Segurança |
| RNF09 | Formulários administrativos devem possuir proteção CSRF | Segurança |

---

## 4. Casos de Uso Detalhados

### UC01 — Criar Pedido

- **Ator principal:** Cliente
- **Pré-condições:** Cliente autenticado; ao menos um produto disponível
- **Fluxo principal:**
  1. Cliente seleciona produtos e define quantidades
  2. Sistema calcula subtotal por item e total do pedido
  3. Cliente confirma o pedido
  4. Sistema persiste o pedido com status `Pendente`
- **Fluxos alternativos:**
  - 2a. Produto está indisponível → sistema lança `ProdutoIndisponivelException` e remove o item da lista antes de recalcular
  - 4a. Estabelecimento fora do horário de funcionamento → sistema rejeita a criação (RF17)
- **Pós-condição:** Pedido criado com status inicial `Pendente`

### UC02 — Aplicar Cupom ao Pedido

- **Ator principal:** Cliente
- **Pré-condições:** Pedido em construção (ainda não confirmado)
- **Fluxo principal:**
  1. Cliente informa o código do cupom
  2. Sistema verifica existência, validade e valor mínimo do pedido (Specification)
  3. Sistema aplica o desconto conforme a estratégia do cupom (percentual ou fixo)
  4. Sistema recalcula o total do pedido
- **Fluxos alternativos:**
  - 2a. Cupom expirado ou inexistente → sistema lança `CupomInvalidoException`
  - 2b. Valor do pedido abaixo do mínimo exigido → sistema informa o valor mínimo necessário
- **Pós-condição:** Total do pedido reflete o desconto aplicado

### UC03 — Transicionar Status do Pedido

- **Ator principal:** Administrador
- **Pré-condições:** Pedido existente
- **Fluxo principal:**
  1. Administrador seleciona o novo status
  2. Sistema valida se a transição é permitida a partir do status atual
  3. Sistema atualiza o status do pedido
- **Fluxos alternativos:**
  - 2a. Transição inválida (ex.: `Entregue` → `Pendente`) → sistema rejeita e mantém o status atual
- **Pós-condição:** Pedido reflete o novo status, respeitando a máquina de estados definida na Fase 5

### UC04 — Gerenciar Produtos (resumo)

- **Ator principal:** Administrador
- Cadastrar, editar e inativar produtos (RF15). Produto inativo não aparece na listagem do cliente (RF04), mas é preservado no histórico de pedidos já existentes (integridade referencial).

---

## 5. Diagrama Entidade-Relacionamento

Ver [`diagrama-er.mermaid`](diagrama-er.mermaid) — renderizado automaticamente no GitHub.

---

## 6. Rastreabilidade (Requisito → Fase do Roadmap)

| Requisito | Fase onde é implementado |
|---|---|
| RF01, RF02, RF19 | Fase 8 (Autenticação) |
| RF03, RF04, RF05 | Fase 6 (Presentation Layer) |
| RF06, RF07, RF10, RF11 | Fase 3 (Application: criar pedido) |
| RF08, RF09 | Fase 4 (Cupom e desconto) |
| RF12, RF13, RF14 | Fase 5 (Máquina de estado) |
| RF15, RF16, RF18 | Fase 8 (Painel administrativo) |
| RF17 | Fase 1 (Value Object `HorarioFuncionamento`) |
| RNF01, RNF02, RNF05, RNF08, RNF09 | Fase 7 (Segurança) |
| RNF06 | Transversal, desde a Fase 0 |
| RNF07 | Fase 9 (Testes) |

Esta tabela existe para evitar o problema clássico de "documentação que não bate com o código": cada requisito tem uma fase de entrega rastreável.
