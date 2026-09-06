# 🍽️ Cardápio Digital Inteligente

![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)
![Status](https://img.shields.io/badge/status-em%20desenvolvimento-yellow)
![Arquitetura](https://img.shields.io/badge/arquitetura-Clean%20Architecture-blue)
![Licença](https://img.shields.io/badge/licença-MIT-green)

> Um sistema de pedidos para cardápio digital, construído do zero em **PHP puro**,
> para demonstrar na prática Programação Orientada a Objetos, Clean Architecture,
> SOLID e Design Patterns aplicados a um domínio de negócio real.

## 🎯 Sobre o projeto
Este é um projeto de estudo desenvolvido com o propósito de colocar em prática conceitos de Programação Orientada a Objetos (POO), Clean Architecture, SOLID, Clean Code e Design Patterns.
O projeto consiste em um cardápio digital com gerenciamento de pedidos, desenvolvido a partir de regras de negócio reais e com foco em uma arquitetura organizada, escalável e de fácil manutenção. Cada uma dessas regras é resolvida com um padrão de
projeto **justificado**, nunca aplicado só para constar.

O domínio possui diferentes regras de negócio, como:

- Um pedido não pode avançar ou retroceder livremente entre os status;
- Cupons possuem regras de validade e valor mínimo para utilização;
- O estabelecimento possui horários de funcionamento;
- Produtos podem possuir diferentes categorias e disponibilidades;
- O fluxo de um pedido deve respeitar determinadas regras do domínio.

O principal objetivo deste projeto não é apenas construir uma aplicação funcional, mas **entender como os conceitos de Engenharia de Software e Programação Orientada a Objetos podem ser aplicados na resolução de problemas reais**.

O projeto é construído **em fases públicas**. Cada fase adiciona uma camada de
responsabilidade ao sistema e é documentada aqui, expondo
o processo de decisão.

📄 Documentação completa:
[Arquitetura e Roadmap](docs/ARQUITETURA-E-ROADMAP.md) ·
[Requisitos e Casos de Uso](docs/REQUISITOS-E-CASOS-DE-USO.md) ·
[Diagrama ER](docs/diagrama-er.mermaid)

---

## 🧭 Status atual

**Fases 0, 1, 2 e 3** ✅ concluídas · **Fase 4 — Cupom e Desconto (Strategy Pattern)** 🔜 próxima
**Fase 0 — Fundação** ✅ concluída · 
**Fase 1 — Entidades e Value Objects** ✅ concluída · 
**Fase 2 — Banco de Dados e Repository Pattern** ✅ concluída · 
**Fase 3 — Application Layer** 🚧 em andamento

## 📝 Diário de desenvolvimento

| Fase | O que foi entregue |
|---|---|
| 0 | Estrutura do projeto, Composer/PSR-4, Router HTTP próprio, `ConnectionFactory` (PDO) |
| Docs | Requisitos funcionais/não-funcionais, casos de uso, diagrama ER |
| 1 | Value Object `Money` (imutável) e Entity `Product` (mutável), com exceções de domínio próprias |
| 2 | `ProductRepositoryInterface`, migrations (`categories`, `products`), `PdoProductRepository` validado de ponta a ponta |
| 3 | Value Object `OrderItem`, Entity `Order`, `CreateOrderService` (Application), `OrderRepositoryInterface` + `PdoOrderRepository` com transação, criação de pedido completa e persistida |

> Esta tabela é atualizada a cada fase concluída, é o histórico de evolução do projeto.

---

## 🏗️ Arquitetura

```
Presentation (HTTP) → Application (Casos de Uso) → Domain (Regras de Negócio)
                                                          ↑
                                              Infrastructure (PDO, Router)
```

Regra de dependência: as camadas externas dependem das internas, nunca o
contrário. O `Domain` não conhece PDO nem HTTP, apenas define interfaces
(Inversão de Dependência).

## ⚙️ Stack

- PHP 8.3+
- MySQL + PDO (prepared statements)
- Composer com autoload PSR-4
- PSR-1 / PSR-12
- Zero frameworks — arquitetura construída manualmente para fins de estudo

## ▶️ Como rodar localmente

```bash
composer install
cp .env.example .env
# ajuste as credenciais do banco no .env

php -S localhost:8000 -t public
```

Endpoints disponíveis nesta fase:

| Método | Rota | Descrição |
|---|---|---|
| GET | `/` | Informações do projeto |
| GET | `/health` | Health check |

## 📂 Estrutura de pastas

```
src/
├── Domain/          # Entidades, Value Objects, interfaces de repositório
├── Application/     # Casos de uso, Services, DTOs, Strategies
├── Infrastructure/  # PDO, Router HTTP, Migrations
└── Presentation/     # Controllers, Requests, Responses
```

## 🗺️ Roadmap

| Fase | Conteúdo | Status |
|---|---|---|
| 0 | Fundação: Composer, PSR-4, Router próprio, conexão PDO | ✅ Concluída |
| 1 | Domínio: Entidades e Value Objects | ✅ Concluída |
| 2 | Banco de dados + Repository Pattern | ✅ Concluída |
| 3 | Application Layer: criação de pedido | ✅ Concluída |
| 4 | Regras de negócio: cupom e desconto (Strategy) | 🚧 Em andamento |
| 5 | Máquina de estado do pedido | 🔜 |
| 6 | Camada HTTP completa | 🔜 |
| 7 | Segurança | 🔜 |
| 8 | Painel administrativo | 🔜 |
| 9 | Testes automatizados (PHPUnit) | 🔜 |

Detalhes de cada fase em [`docs/ARQUITETURA-E-ROADMAP.md`](docs/ARQUITETURA-E-ROADMAP.md).

## 📜 Licença

MIT
