<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# API Assinaturas - Laravel 13

Uma API de gerenciamento de assinaturas desenvolvida com **Laravel 13**, focada em **escalabilidade**, **integridade dos dados** e **separação de responsabilidades**.

O projeto implementa processamento assíncrono para cobranças recorrentes utilizando filas, permitindo que a aplicação mantenha alta performance mesmo com um grande volume de assinaturas.

---

# Funcionalidades

- Gerenciamento de assinaturas
- Geração automática de faturas
- Processamento assíncrono de cobranças
- Cobranças recorrentes
- Agendamento automático de tarefas
- Transações de banco de dados para garantir consistência
- Arquitetura baseada em boas práticas do SOLID

---

# Arquitetura

O projeto foi desenvolvido evitando o padrão **Fat Controller**, mantendo a regra de negócio desacoplada da camada HTTP.

## Action Classes

Toda a lógica de negócio é encapsulada em **Action Classes**, proporcionando:

- Maior reutilização de código
- Facilidade para testes unitários
- Controllers enxutos
- Melhor organização do projeto

## Queue-Based Processing

As cobranças são processadas em segundo plano através de **Jobs**.

Vantagens:

- Não bloqueia a resposta da API
- Escalabilidade horizontal
- Processamento resiliente
- Melhor experiência para o usuário

## Enums (PHP 8.5)

Utilização de **Enums** para representar estados da aplicação, eliminando o uso de strings "mágicas" e aumentando a segurança do código.

Exemplos:

- Status da assinatura
- Status da cobrança
- Status da fatura

## Database Transactions

As operações críticas utilizam **Database Transactions** para garantir atomicidade.

Exemplo:

- Uma assinatura somente é criada caso sua fatura inicial também seja criada com sucesso.

---

# Stack Tecnológica

| Tecnologia | Versão |
|------------|---------|
| PHP | 8.5+ |
| Laravel | 13 |
| Banco de Dados | Sqlite |
| Filas | Laravel Queue |
| Scheduler | Laravel Task Scheduling |

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

## Como Rodar e Testar a API

### 1. Configuração Inicial

```
git clone https://github.com/CiceroLucas/api-assinaturas
cd api-assinaturas
composer install
```

* Configure o ambiente e o banco de dados (SQLite):

```
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### 2. Executando os Serviços

* Inicia o servidor da API
  ```
  php artisan serve
  ```

* Inicia o Worker que processa as filas de cobrança
  ```
  php artisan queue:work  
  ```

### 3. Endpoints

* Criar um Plano
```
POST http://127.0.0.1:8000/api/plans

{
    "name": "Plano Premium",
    "price": 49.90,
    "billing_cycle_in_days": 30
}

```

* Criar um Aluno
```
POST http://127.0.0.1:8000/api/students

{
    "name": "Lucas",
    "email": "lucas@example.com"
}

```

* Assinar o Plano
```
POST http://127.0.0.1:8000/api/subscriptions

{
    "student_id": 1,
    "plan_id": 1
}

```

### 4. Simulando a Cobrança Recorrente (Cron Job)

```
php artisan billing:process
```

O comando identificará assinaturas pendentes e as enviará para a Fila. Imediatamente, você verá no Terminal 2 (Worker) o log de processamento do Job atuando em segundo plano.

---

# Objetivos do Projeto

Este projeto foi desenvolvido para demonstrar conhecimentos em:

- Laravel 13
- Arquitetura de software
- Processamento assíncrono
- Filas
- Jobs
- Scheduler
- Boas práticas de desenvolvimento
- Escalabilidade


