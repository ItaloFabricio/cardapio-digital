# 🍽️ Cardápio Digital Inteligente

![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)
![Status](https://img.shields.io/badge/status-em%20desenvolvimento-yellow)
![Arquitetura](https://img.shields.io/badge/arquitetura-Clean%20Architecture-blue)
![Licença](https://img.shields.io/badge/licença-MIT-green)

> Um sistema de pedidos para cardápio digital, construído do zero em **PHP puro**,
> para demonstrar na prática Programação Orientada a Objetos, Clean Architecture,
> SOLID e Design Patterns aplicados a um domínio de negócio real — não um CRUD
> de exemplo.

## 🎯 Sobre o projeto

Este não é "mais um CRUD para portfólio". O domínio de pedidos de um cardápio
digital tem regras de negócio genuínas: um pedido não pode pular etapas de
status, um cupom tem regras de validade e valor mínimo, o estabelecimento tem
horário de funcionamento. Cada uma dessas regras é resolvida com um padrão de
projeto **justificado** — nunca aplicado só para constar.

O projeto é construído **em fases públicas**. Cada fase adiciona uma camada de
responsabilidade ao sistema e é documentada aqui e em posts técnicos, expondo
o processo de decisão — não apenas o resultado final.

📄 Documentação completa:
[Arquitetura e Roadmap](docs/ARQUITETURA-E-ROADMAP.md) ·
[Requisitos e Casos de Uso](docs/REQUISITOS-E-CASOS-DE-USO.md) ·
[Diagrama ER](docs/diagrama-er.mermaid)

---

## 🧭 Status atual

**Fase 0 — Fundação** ✅ concluída · **Fase 1 — Entidades e Value Objects** 🚧 em andamento

## 📝 Diário de desenvolvimento

| Data | Fase | O que foi entregue |
|---|---|---|
| 2026-08 | Fase 0 | Estrutura do projeto, Composer/PSR-4, Router HTTP próprio, ConnectionFactory (PDO) |
| 2026-08 | Docs | Requisitos funcionais/não-funcionais, casos de uso, diagrama ER |

> Esta tabela é atualizada a cada fase concluída — é o histórico de evolução do projeto.

---

## 🏗️ Arquitetura

```
Presentation (HTTP) → Application (Casos de Uso) → Domain (Regras de Negócio)
                                                          ↑
                                              Infrastructure (PDO, Router)
```

Regra de dependência: as camadas externas dependem das internas, nunca o
contrário. O `Domain` não conhece PDO nem HTTP — apenas define interfaces
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
| 1 | Domínio: Entidades e Value Objects | 🚧 Em andamento |
| 2 | Banco de dados + Repository Pattern | 🔜 |
| 3 | Application Layer: criação de pedido | 🔜 |
| 4 | Regras de negócio: cupom e desconto (Strategy) | 🔜 |
| 5 | Máquina de estado do pedido | 🔜 |
| 6 | Camada HTTP completa | 🔜 |
| 7 | Segurança | 🔜 |
| 8 | Painel administrativo | 🔜 |
| 9 | Testes automatizados (PHPUnit) | 🔜 |

Detalhes de cada fase em [`docs/ARQUITETURA-E-ROADMAP.md`](docs/ARQUITETURA-E-ROADMAP.md).

## 📜 Licença

MIT
