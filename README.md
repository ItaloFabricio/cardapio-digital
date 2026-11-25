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

**Fases 0 a 6** ✅ concluídas — a API JSON está no ar · **Fase 7 — Segurança** 🔜 próxima

## 📝 Diário de desenvolvimento

| Fase | O que foi entregue |
|---|---|
| 0 | Estrutura do projeto, Composer/PSR-4, Router HTTP próprio, `ConnectionFactory` (PDO) |
| Docs | Requisitos funcionais/não-funcionais, casos de uso, diagrama ER |
| 1 | Value Object `Money` (imutável) e Entity `Product` (mutável), com exceções de domínio próprias |
| 2 | `ProductRepositoryInterface`, migrations (`categories`, `products`), `PdoProductRepository` validado de ponta a ponta |
| 3 | Value Object `OrderItem`, Entity `Order`, `CreateOrderService` (Application), `OrderRepositoryInterface` + `PdoOrderRepository` com transação, criação de pedido completa e persistida |
| 4 | **Cupons e descontos:** Strategy Pattern (desconto percentual e fixo), Enum `DiscountType`, Entity `Coupon`, Specifications de elegibilidade (validade e valor mínimo), `CouponRepositoryInterface` + migration + `PdoCouponRepository`, `DiscountStrategyFactory` e `ApplyCouponService` |
| 5 | **Máquina de estado do pedido:** Enum `OrderStatus` com tabela de transições, `Order::changeStatus()` (com regra de pedido vazio), persistência do status no `PdoOrderRepository` e `ChangeOrderStatusService` |
| 6 | **Camada HTTP:** `JsonResponse`/`HttpStatus`/`ResponseEmitter`, `HttpRequest`, `HttpKernel`, `ExceptionHandler` (um só ponto de tradução de erros), `ServiceContainer` com montagem preguiçosa, validação estrita de entrada, fuso horário configurável, e os endpoints de produtos, pedidos e simulação de cupom |

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

O caminho de uma requisição:

```
index.php → HttpKernel → Router → Controller → Request (validação) → Service
                                                                        ↓
                          JsonResponse ← Controller ← Domain + Repository (PDO/MySQL)
```

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
# ajuste as credenciais do banco no .env (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# crie o banco "cardapio_digital" no seu MySQL antes de continuar

php bin/test-connection.php          # confirma que a conexão com o banco funciona
php bin/migrate.php                  # cria as tabelas
php bin/test-product-repository.php  # cadastra um produto de exemplo
php bin/create-demo-coupon.php       # cadastra o cupom PROMO10

php -S localhost:8000 -t public      # sobe o servidor HTTP
```

## 🌐 API

| Método | Rota | Descrição | Sucesso |
|---|---|---|---|
| GET | `/` | Informações da API | `200` |
| GET | `/health` | Health check | `200` |
| GET | `/products` | Lista os produtos disponíveis | `200` |
| POST | `/orders` | Cria um pedido (corpo: `items` com `productId` e `quantity`) | `201` |
| POST | `/coupons/simulate` | Simula o desconto de um cupom (corpo: `code` e `orderTotalCents`) | `200` |

Erros seguem sempre o formato `{"error": "mensagem"}`; erros de validação trazem
também `details` com o campo e o motivo.

| Status | Quando |
|---|---|
| `400` | Corpo da requisição não é um JSON válido |
| `404` | Rota inexistente |
| `422` | Dados inválidos ou regra de negócio violada |
| `500` | Erro inesperado (o detalhe vai para o log, nunca para a resposta) |

Exemplo (com o servidor ligado):

```bash
curl.exe -i -X POST http://localhost:8000/orders \
  -H "Content-Type: application/json" \
  -d "@docs/exemplos/pedido-valido.json"
```

### Scripts de teste manual (`bin/`)

Enquanto os testes automatizados (PHPUnit) não chegam na Fase 9, cada etapa tem um
script que demonstra o comportamento no terminal:

| Script | O que demonstra | Precisa de banco? |
|---|---|---|
| `test-product-repository.php` | Salvar e buscar um produto | Sim |
| `test-coupon-specifications.php` | Regras de validade e valor mínimo do cupom | Não |
| `test-discount-factory.php` | Escolha da estratégia de desconto e cálculo | Não |
| `test-coupon-repository.php` | Salvar e buscar um cupom (busca sem diferenciar maiúsculas) | Sim |
| `test-apply-coupon-service.php` | Aplicar cupom: 1 cenário de sucesso e 4 de recusa | Sim |
| `test-order-status.php` | Mapa de transições de status e verificações | Não |
| `test-order-status-flow.php` | Mudar o status de um pedido, gravar e reler do banco | Sim |
| `test-change-order-status-service.php` | Mudar status pelo Service: avanços válidos, recusas e pedido inexistente | Sim |
| `test-http-request.php` | Leitura de método, caminho e corpo JSON | Não |
| `test-bootstrap.php` | Carregamento do `.env` e fuso horário | Não |
| `test-http-kernel.php` | Respostas 200, 404, 400 e 500 do Kernel | Não |
| `test-exception-handler.php` | Tradução de cada tipo de exceção em resposta | Não |
| `test-product-controller.php` | Listagem de produtos com repositório falso | Não |
| `test-order-controller.php` | `POST /orders` com 7 cenários e repositórios falsos | Não |
| `test-coupon-controller.php` | `POST /coupons/simulate` com 7 cenários e repositório falso | Não |
| `create-demo-coupon.php` | Cria o cupom de demonstração `PROMO10` | Sim |

> Para rodar os scripts que precisam de produto cadastrado, execute antes
> `php bin/test-product-repository.php`.

## 📂 Estrutura de pastas

```
bin/                      # Scripts de linha de comando (migrations, testes manuais)
docs/
├── notes/                # Notas de estudo, uma por etapa
└── exemplos/             # Corpos de requisição de exemplo (usados com curl)
public/
└── index.php             # Front controller: configurar, atender, entregar
src/
├── Domain/
│   ├── Entity/           # Product, Order, Coupon (identidade própria)
│   ├── ValueObject/      # Money, OrderItem (imutáveis, sem identidade)
│   ├── Enum/             # DiscountType, OrderStatus
│   ├── Specification/    # Regras de elegibilidade do cupom
│   ├── Repository/       # Interfaces de persistência (contratos)
│   └── Exception/        # Exceções de regras de negócio
├── Application/
│   ├── DTO/              # Objetos de entrada e saída dos casos de uso
│   ├── Service/          # Casos de uso
│   └── Strategy/         # Estratégias de desconto e a Factory que as escolhe
├── Infrastructure/
│   ├── Config/           # EnvironmentLoader, TimezoneConfigurator
│   ├── Container/        # ServiceContainer (montagem das peças)
│   ├── Persistence/
│   │   ├── Connection/   # ConnectionFactory (PDO)
│   │   └── Pdo/          # Implementações concretas dos Repositories
│   ├── Migration/        # Scripts .sql versionados
│   └── Http/             # Router próprio
└── Presentation/
    ├── Controller/       # Product, Order e Coupon
    ├── Request/          # HttpRequest e validação de entrada
    ├── Response/         # JsonResponse, HttpStatus, ResponseEmitter, MoneyPresenter
    ├── ApiRoutes.php     # Lista de rotas
    ├── HttpKernel.php    # Requisição entra, resposta sai
    └── ExceptionHandler.php  # Exceção entra, resposta sai
```

## 🗺️ Roadmap

| Fase | Conteúdo | Status |
|---|---|---|
| 0 | Fundação: Composer, PSR-4, Router próprio, conexão PDO | ✅ Concluída |
| 1 | Domínio: Entidades e Value Objects | ✅ Concluída |
| 2 | Banco de dados + Repository Pattern | ✅ Concluída |
| 3 | Application Layer: criação de pedido | ✅ Concluída |
| 4 | Regras de negócio: cupom e desconto (Strategy, Specification, Factory) | ✅ Concluída |
| 5 | Máquina de estado do pedido (Enum + transições + Service) | ✅ Concluída |
| 6 | Camada HTTP: API JSON, validação de entrada e tratamento de erros | ✅ Concluída |
| 7 | Segurança | 🔜 Próxima |
| 8 | Painel administrativo | 🔜 |
| 9 | Testes automatizados (PHPUnit) | 🔜 |

Detalhes de cada fase em [`docs/ARQUITETURA-E-ROADMAP.md`](docs/ARQUITETURA-E-ROADMAP.md).

## 📜 Licença

MIT
