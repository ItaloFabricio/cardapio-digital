# Cardápio Digital Inteligente — Documento de Arquitetura e Roadmap

> Projeto de estudo de PHP 8.3+ com POO, Clean Architecture, SOLID e Design Patterns.
> Objetivo: Colocar em prática os estudos de POO, Banco de dados, padrões de projeto e arquitetura limpa. Além disso, revisar sobre modelagem de dados com uma documentação informativa sobre requisitos e casos de uso. Assim, o intuito do projeto foi aplicar o que foi estudado e adquirir novos conhecimentos.

---

## 1. Por que este domínio (e não um blog)

O domínio de pedidos é rico o suficiente para você justificar **State**, **Strategy**, **Specification**, **Observer** e **Factory** sem forçar a barra — cada um resolve um problema concreto do negócio.

---

## 2. Visão Geral da Arquitetura

Vamos usar **Clean Architecture em 4 camadas**, adaptada para um projeto PHP de porte médio (sem exagero de DDD tático completo, que seria overengineering aqui):

```
┌─────────────────────────────────────────┐
│           Presentation (HTTP)             │  Controllers, Requests, Views/JSON
├─────────────────────────────────────────┤
│           Application (Use Cases)         │  Services, DTOs, Commands
├─────────────────────────────────────────┤
│              Domain (Core)                │  Entities, Value Objects, Interfaces
├─────────────────────────────────────────┤
│         Infrastructure (Framework)         │  PDO Repositories, Mailer, Migrations
└─────────────────────────────────────────┘
```

**Regra de dependência**: as camadas de fora dependem das de dentro, nunca o contrário. O `Domain` não conhece PDO, não conhece HTTP, não conhece nada de infraestrutura — ele só define **interfaces** (contratos). Isso é **Inversão de Dependência (DIP)**, o "D" do SOLID, e é o que torna o projeto testável e trocável (você poderia trocar MySQL por outro banco sem tocar no domínio).

---

## 3. Estrutura de Pastas

```
cardapio-digital/
├── composer.json
├── public/
│   └── index.php                 # Front controller único
├── src/
│   ├── Domain/
│   │   ├── Entity/
│   │   │   ├── Produto.php
│   │   │   ├── Pedido.php
│   │   │   ├── Cliente.php
│   │   │   └── Cupom.php
│   │   ├── ValueObject/
│   │   │   ├── Dinheiro.php
│   │   │   ├── Email.php
│   │   │   └── HorarioFuncionamento.php
│   │   ├── Enum/
│   │   │   └── StatusPedido.php
│   │   ├── Repository/           # Interfaces (contratos)
│   │   │   ├── ProdutoRepositoryInterface.php
│   │   │   └── PedidoRepositoryInterface.php
│   │   └── Exception/
│   │       ├── ProdutoIndisponivelException.php
│   │       └── CupomInvalidoException.php
│   ├── Application/
│   │   ├── Service/
│   │   │   ├── CriarPedidoService.php
│   │   │   └── AplicarCupomService.php
│   │   ├── DTO/
│   │   │   └── CriarPedidoDTO.php
│   │   └── Strategy/
│   │       ├── DescontoStrategyInterface.php
│   │       ├── DescontoPercentualStrategy.php
│   │       └── DescontoFixoStrategy.php
│   ├── Infrastructure/
│   │   ├── Persistence/
│   │   │   ├── Pdo/
│   │   │   │   ├── PdoProdutoRepository.php
│   │   │   │   └── PdoPedidoRepository.php
│   │   │   └── Connection/
│   │   │       └── ConnectionFactory.php
│   │   └── Migration/
│   │       └── 001_create_produtos_table.sql
│   └── Presentation/
│       ├── Controller/
│       │   └── PedidoController.php
│       ├── Request/
│       │   └── CriarPedidoRequest.php
│       └── Response/
│           └── JsonResponse.php
├── tests/
├── docs/
│   ├── diagrama-er.png
│   └── fluxo-requisicao.md
└── README.md
```

**Por que isso e não "MVC simples com pastas Model/View/Controller"?**
Porque MVC puro tende a colocar regra de negócio dentro do Controller ou do Model "gordo" (fat model). Separando `Domain` de `Application` de `Presentation`, cada classe tem **uma única razão para mudar** — isso é literalmente a definição do **Single Responsibility Principle**.

---

## 4. Modelagem de Dados (visão inicial)

### Entidades principais
- **Cliente** (id, nome, email, senha_hash, criado_em)
- **Categoria** (id, nome, ordem)
- **Produto** (id, categoria_id, nome, descricao, preco, disponivel)
- **Cupom** (id, codigo, tipo_desconto, valor, validade, valor_minimo_pedido)
- **Pedido** (id, cliente_id, cupom_id, status, criado_em)
- **ItemPedido** (id, pedido_id, produto_id, quantidade, preco_unitario)

### Relacionamentos
- Categoria **1—N** Produto
- Cliente **1—N** Pedido
- Pedido **1—N** ItemPedido (associação de agregação: ItemPedido não existe sem Pedido → composição)
- Pedido **N—1** Cupom (opcional, nullable)

### Constraints e índices que vamos aplicar
- `FOREIGN KEY` com `ON DELETE RESTRICT` em relações críticas (não deletar produto com pedidos)
- `UNIQUE` em `cupom.codigo` e `cliente.email`
- Índice em `pedido.status` (consultas de painel administrativo filtram por status constantemente)
- `CHECK` (via aplicação, MySQL 8 suporta CHECK constraint) para `preco >= 0`

O diagrama ER visual eu recomendo gerar quando tivermos o schema fechado na Fase 2 (posso te ajudar a montar via dbdiagram.io ou Mermaid).

---

## 5. Design Patterns — o que, onde e por quê

| Padrão | Onde é usado | Problema que resolve |
|---|---|---|
| **Repository** | `ProdutoRepositoryInterface` + `PdoProdutoRepository` | Domínio não deve saber que existe SQL. Troca de banco sem tocar regra de negócio. |
| **Strategy** | Cálculo de desconto de cupom (percentual vs. fixo) | Evita `if/else`/`match` gigante no service; cada regra de desconto é uma classe substituível (Open/Closed Principle). |
| **State** (via Enum + validação de transição) | `StatusPedido` (Pendente → Confirmado → Em Preparo → Pronto → Entregue/Cancelado) | Impede que um pedido "pule" de Pendente direto para Entregue. Regra de transição fica centralizada. |
| **Factory** | Criação de `Pedido` a partir de `CriarPedidoDTO` | Encapsula lógica de montagem complexa de objeto, mantendo o construtor da entidade limpo. |
| **Specification** | Validação de regras de cupom (`valor_minimo_pedido`, `validade`) | Regras de negócio combináveis e testáveis isoladamente, sem inchar a entidade. |
| **Dependency Injection** | Services recebem repositórios via construtor (constructor promotion) | Testabilidade (mock de repositório em teste unitário) e baixo acoplamento. |
| **Facade** (opcional, fase avançada) | Camada de integração com gateway de pagamento fictício | Esconde complexidade de múltiplas chamadas externas atrás de uma interface simples. |

Note que **não** vou usar Singleton em nenhum ponto do domínio — o único candidato legítimo seria a conexão PDO, e mesmo assim vamos preferir **injeção via Factory/Container** a Singleton clássico, porque Singleton dificulta testes (estado global escondido). Isso também é uma decisão que vale um post: "por que evitei Singleton".

---

## 6. SOLID aplicado (resumo, cada fase vai aprofundar)

- **S**RP → cada classe (Controller, Service, Repository, Entity) tem um motivo único de mudança.
- **O**CP → `DescontoStrategyInterface` permite adicionar novo tipo de desconto sem alterar código existente.
- **L**SP → qualquer implementação de `ProdutoRepositoryInterface` deve poder substituir outra sem quebrar o Service.
- **I**SP → interfaces pequenas e específicas (`PedidoRepositoryInterface` não força métodos de Produto).
- **D**IP → `Application` depende de interfaces do `Domain`, nunca de implementações concretas do `Infrastructure`.

---

## 7. Roadmap de Fases (cada uma = 1 conjunto de posts)

### Fase 0 — Fundação
- Composer, PSR-4, `.gitignore`, estrutura de pastas, README inicial, conexão PDO isolada em `ConnectionFactory`.
- Conceitos: Autoloading, Namespaces, PSR-4, PSR-12.
- Commit: `chore: estrutura inicial do projeto com Composer e PSR-4`

### Fase 1 — Domínio: Entidades e Value Objects
- `Produto`, `Cliente`, `Cupom` como entidades ricas (não anemic model).
- `Dinheiro` como Value Object (evita bug clássico de float para valores monetários).
- Conceitos: Encapsulamento, readonly properties, Value Objects vs Entities.

### Fase 2 — Banco de Dados e Repository Pattern
- Migrations SQL, `ProdutoRepositoryInterface`, `PdoProdutoRepository`.
- Conceitos: Repository Pattern, DIP, Prepared Statements.

### Fase 3 — Application Layer: Criar Pedido
- `CriarPedidoService`, `CriarPedidoDTO`, Factory de Pedido.
- Conceitos: DTO, Factory, Service Layer, Constructor Promotion.

### Fase 4 — Regras de Negócio: Cupom e Desconto
- Strategy de desconto, Specification de validade de cupom, Exceptions customizadas.
- Conceitos: Strategy, Specification, Exceptions de domínio.

### Fase 5 — Máquina de Estado do Pedido
- Enum `StatusPedido`, validação de transição de estado.
- Conceitos: Enums nativos do PHP 8.1+, State pattern simplificado.

### Fase 6 — Presentation Layer (HTTP)
- Front controller, Router simples, Controllers, Request validation, JSON Response.
- Conceitos: Separation of Concerns, validação de entrada, sanitização.

### Fase 7 — Segurança
- Hash de senha (`password_hash`), proteção CSRF, tratamento de erros sem vazar detalhes internos.

### Fase 8 — Painel Administrativo + Autenticação
- Login, sessão, middleware de autorização.

### Fase 9 (opcional/avançada) — Testes automatizados
- PHPUnit, testes unitários de Service e Strategy com mocks de Repository.

---

## 8. Checklist de Segurança (aplicado desde a Fase 2)
- [ ] PDO com prepared statements em 100% das queries
- [ ] `password_hash()` / `password_verify()` para senha
- [ ] Validação de entrada na camada `Request`, nunca direto no Controller
- [ ] Escapar saída (XSS) se houver views HTML
- [ ] Token CSRF em formulários administrativos
- [ ] Mensagens de erro genéricas para o usuário, log detalhado internamente

---
