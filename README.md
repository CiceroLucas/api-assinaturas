<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# API Assinaturas - Laravel 13

API de gerenciamento de assinaturas desenvolvida com **Laravel 13**, focada em **integridade dos dados**, **separação de responsabilidades** e **processamento assíncrono** de cobranças recorrentes.

O projeto usa Actions para isolar regras de negócio, Jobs para processar cobranças em fila e Scheduler para executar a rotina diária de faturamento e expiração de assinaturas canceladas.

---

# Funcionalidades

- Cadastro de planos
- Cadastro de estudantes
- Criação de assinaturas
- Geração automática da fatura inicial
- Processamento assíncrono de cobranças recorrentes
- Cancelamento de assinatura ao final do período vigente
- Encerramento automático de assinaturas canceladas via rotina diária
- Transações de banco de dados para garantir consistência
- Arquitetura baseada em boas práticas de separação de responsabilidades

---

# Stack Tecnológica

| Tecnologia | Versão |
|------------|--------|
| PHP | 8.3+ |
| Laravel | 13 |
| Banco de dados | SQLite |
| Filas | Laravel Queue |
| Scheduler | Laravel Task Scheduling |
| Testes | Pest |

---

# Arquitetura

O projeto evita o padrão **Fat Controller**. Os controllers recebem a requisição HTTP, validam os dados com Form Requests e delegam a regra de negócio para Actions.

## Action Classes

As regras principais ficam encapsuladas em Actions:

- `CreateSubscriptionAction`: cria a assinatura e a fatura inicial dentro de uma transação.
- `CancelSubscriptionAction`: agenda o cancelamento da assinatura para o fim do ciclo atual.

Benefícios:

- Controllers mais enxutos
- Regras de negócio reutilizáveis
- Melhor organização do código
- Testes mais simples

## Queue-Based Processing

As cobranças recorrentes são processadas em segundo plano por Jobs.

Vantagens:

- Não bloqueia a resposta da API
- Permite escalar o processamento de cobranças
- Mantém a rotina de faturamento desacoplada da criação da assinatura

## Enums

O projeto usa Enums para representar estados da aplicação e evitar strings mágicas.

Exemplos:

- `SubscriptionStatus`
- `InvoiceStatus`

## Database Transactions

Operações críticas usam transações para garantir atomicidade.

Exemplo: uma assinatura só é criada se a fatura inicial também for criada com sucesso.

---

# Endpoints

## Criar plano

```http
POST /api/plans
```

Campos esperados:

```json
{
  "name": "Mensal",
  "price": 49.90,
  "billing_cycle_in_days": 30
}
```

## Criar estudante

```http
POST /api/students
```

Campos esperados:

```json
{
  "name": "Lucas",
  "email": "lucas@example.com"
}
```

## Criar assinatura

```http
POST /api/subscriptions
```

Campos esperados:

```json
{
  "student_id": 1,
  "plan_id": 1
}
```

Resposta de sucesso:

```json
{
  "message": "Assinatura criada com sucesso.",
  "data": {
    "id": 1,
    "student_id": 1,
    "plan_id": 1,
    "status": "active",
    "next_billing_date": "2026-07-28",
    "invoices": []
  }
}
```

## Cancelar assinatura

```http
POST /api/subscriptions/{subscription}/cancel
```

O cancelamento não encerra a assinatura imediatamente. Ele preenche `cancelled_at`, mantém o status como `active` e deixa a assinatura válida até `next_billing_date`.

Resposta de sucesso:

```json
{
  "message": "Sua assinatura foi cancelada com sucesso.",
  "status": "active_until_end_of_period",
  "valid_until": "2026-07-28"
}
```

Caso a assinatura já tenha sido cancelada anteriormente, a API retorna `422 Unprocessable Content`:

```json
{
  "message": "Esta assinatura ja foi cancelada anteriormente.",
  "errors": {
    "subscription": [
      "Esta assinatura ja foi cancelada anteriormente."
    ]
  }
}
```

---

# Fluxo de Cancelamento

```text
Cliente
    |
    v
POST /api/subscriptions/{id}/cancel
    |
    v
CancelSubscriptionRequest
    |
    v
SubscriptionController
    |
    v
CancelSubscriptionAction
    |
    +-- Se ja existe cancelled_at ou status canceled:
    |       retorna 422
    |
    +-- Se ainda nao foi cancelada:
            preenche cancelled_at
            mantem status active
            assinatura continua valida ate next_billing_date
```

No dia em que `next_billing_date` chegar, a rotina diária altera o status da assinatura para `canceled`.

---

# Rotina Diária

O comando responsável pelo faturamento e encerramento de assinaturas é:

```bash
php artisan billing:process
```

Ele executa duas etapas:

1. Busca assinaturas ativas com cobrança vencida e sem cancelamento agendado.
2. Busca assinaturas ativas com `cancelled_at` preenchido e `next_billing_date` vencido para marcar como `canceled`.

O agendamento está configurado em `routes/console.php`:

```php
Schedule::command('billing:process')->dailyAt('00:01');
```

---

# Testes

Para rodar a suíte:

```bash
php artisan test
```

---

# Princípios Utilizados

- SOLID
- SRP (Single Responsibility Principle)
- Separation of Concerns
- Queue-driven Architecture
- Domain Actions
- Database Transactions
- Clean Code

---

# Fluxo da Aplicação

```text
Cliente
    │
    ▼
Controller
    │
    ▼
Action
    │
    ├── Cria Assinatura
    ├── Cria Fatura
    └── Dispara Job
            │
            ▼
        Queue
            │
            ▼
      Processamento
            │
            ▼
 Atualização da Cobrança
```

---

# Objetivos do Projeto

Este projeto foi desenvolvido para demonstrar conhecimentos em:

- Laravel 13
- Arquitetura de software
- Form Requests
- Actions
- Filas e Jobs
- Scheduler
- Processamento assíncrono
- Testes automatizados
- Boas práticas de desenvolvimento
- Escalabilidade
