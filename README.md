
# Task Manager API

Bem-vindo à documentação da API Task Manager. Esta API permite que você gerencie usuários e tarefas de forma programática.

**URL Base da API**: `http://localhost:8000/api`

-----

## Autenticação

A autenticação é baseada em tokens. Para acessar as rotas protegidas, você precisará enviar um token de acesso no cabeçalho `Authorization` de cada requisição.

**Exemplo de Cabeçalho de Autenticação:**

```
Authorization: Bearer <seu_token_de_acesso>
```

### 1\. Registrar Usuário

* **Endpoint:** `/register`
* **Método:** `POST`
* **Requer Autenticação:** Não

#### Parâmetros do Corpo (Body)

```json
{
    "name": "string", // Obrigatório. O nome completo do usuário.
    "email": "string", // Obrigatório. O endereço de e-mail (deve ser único).
    "password": "string", // Obrigatório. Mínimo de 8 caracteres.
}
```

#### Resposta de Sucesso (Código `201 Created`)

```json
{
    "user": {
        "id": 1,
        "name": "Seu Nome",
        "email": "email@exemplo.com",
        "created_at": "2025-10-27T18:00:00.000000Z",
        "updated_at": "2025-10-27T18:00:00.000000Z"
    },
    "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ0123456789"
}
```

### 2\. Autenticar Usuário (Login)

* **Endpoint:** `/login`
* **Método:** `POST`
* **Requer Autenticação:** Não

#### Parâmetros do Corpo (Body)

```json
{
    "email": "string", // Obrigatório. O e-mail cadastrado.
    "password": "string" // Obrigatório. A senha.
}
```

#### Resposta de Sucesso (Código `200 OK`)

```json
{
    "user": {
        "id": 1,
        "name": "Seu Nome",
        "email": "email@exemplo.com",
        "created_at": "2025-10-27T18:00:00.000000Z",
        "updated_at": "2025-10-27T18:00:00.000000Z"
    },
    "token": "2|bCdEfGhIjKlMnOpQrStUvWxYz1234567890"
}
```

### 3\. Fazer Logout

* **Endpoint:** `/logout`
* **Método:** `POST`
* **Requer Autenticação:** Sim

#### Resposta de Sucesso (Código `200 OK`)

```json
{
    "message": "Logout realizado com sucesso."
}
```

-----

## Gerenciamento de Tarefas (Tasks)

### 4\. Listar todas as tarefas

* **Endpoint:** `/tasks`
* **Método:** `GET`
* **Requer Autenticação:** Sim

#### Resposta de Sucesso (Código `200 OK`)

```json
{
    "data": [
        {
            "id": 1,
            "title": "Finalizar a documentação da API",
            "description": "Detalhar os endpoints de tarefas.",
            "status": "pending",
            "user_id": 1,
            "created_at": "2025-10-27T19:00:00.000000Z",
            "updated_at": "2025-10-27T19:00:00.000000Z"
        }
    ]
}
```

### 5\. Criar uma nova tarefa

* **Endpoint:** `/tasks`
* **Método:** `POST`
* **Requer Autenticação:** Sim

#### Parâmetros do Corpo (Body)

```json
{
    "title": "string", // Obrigatório. O título da tarefa.
    "description": "string", // Opcional. Uma descrição detalhada da tarefa.
}
```

#### Resposta de Sucesso (Código `201 Created`)

```json
{
    "data": {
        "id": 2,
        "title": "Minha Nova Tarefa",
        "description": "Uma breve descrição do que precisa ser feito.",
        "status": "pending",
        "user_id": 1,
        "created_at": "2025-10-27T19:05:00.000000Z",
        "updated_at": "2025-10-27T19:05:00.000000Z"
    }
}
```

### 6\. Exibir uma tarefa específica

* **Endpoint:** `/tasks/{id}`
* **Método:** `GET`
* **Requer Autenticação:** Sim

#### Resposta de Sucesso (Código `200 OK`)

```json
{
    "data": {
        "id": 1,
        "title": "Finalizar a documentação da API",
        "description": "Detalhar os endpoints de tarefas.",
        "status": "pending",
        "user_id": 1,
        "created_at": "2025-10-27T19:00:00.000000Z",
        "updated_at": "2025-10-27T19:00:00.000000Z"
    }
}
```

### 7\. Atualizar uma tarefa existente

* **Endpoint:** `/tasks/{id}`
* **Método:** `PUT`
* **Requer Autenticação:** Sim

#### Parâmetros do Corpo (Body)

*Envie apenas os campos que deseja atualizar. Todos são opcionais.*

```json
{
    "title": "string",
    "description": "string",
    "status": "string" // ex: "completed" ou "pending"
}
```

#### Resposta de Sucesso (Código `200 OK`)

Retorna o objeto da tarefa atualizada, dentro da chave `"data"`.

### 8\. Excluir uma tarefa

* **Endpoint:** `/tasks/{id}`
* **Método:** `DELETE`
* **Requer Autenticação:** Sim

#### Resposta de Sucesso (Código `204 No Content`)

Retorna uma resposta vazia.

-----

## Gerenciamento de Usuário

### 9\. Obter dados do usuário autenticado

* **Endpoint:** `/user`
* **Método:** `GET`
* **Requer Autenticação:** Sim

#### Resposta de Sucesso (Código `200 OK`)

```json
{
    "data": {
        "id": 1,
        "name": "Seu Nome",
        "email": "email@exemplo.com",
        "created_at": "2025-10-27T18:00:00.000000Z",
        "updated_at": "2025-10-27T18:00:00.000000Z"
    }
}
```

-----

## Estrutura do Banco de Dados

### Tabela `users`

Armazena as informações dos usuários da aplicação.

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `id` | BIGINT (PK) | Identificador único do usuário. |
| `name` | VARCHAR | Nome do usuário. |
| `email` | VARCHAR | E-mail do usuário (deve ser único). |
| `password` | VARCHAR | Senha do usuário (armazenada com hash). |
| `created_at` | TIMESTAMP | Data e hora de criação do registro. |
| `updated_at` | TIMESTAMP | Data e hora da última atualização do registro. |

### Tabela `tasks`

Armazena as tarefas criadas pelos usuários.

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `id` | BIGINT (PK) | Identificador único da tarefa. |
| `user_id` | BIGINT (FK) | Chave estrangeira que referencia `users.id`. |
| `title` | VARCHAR | Título da tarefa. |
| `description` | TEXT | Descrição detalhada da tarefa (opcional). |
| `status` | VARCHAR | Status atual da tarefa (ex: "pending", "completed"). |
| `created_at` | TIMESTAMP | Data e hora de criação do registro. |
| `updated_at` | TIMESTAMP | Data e hora da última atualização do registro. |