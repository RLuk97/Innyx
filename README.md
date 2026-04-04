# Sistema de Gerenciamento de Produtos (INNYX)

Este é um sistema **Full Stack** profissional para gerenciamento de produtos e categorias, desenvolvido como parte de um desafio técnico para a **Innyx**. O projeto utiliza **Laravel 11** no Backend e **Vue.js 3** com **TypeScript** no Frontend, focando em performance e experiência do usuário.

---

## 🚀 Tecnologias Utilizadas

### Backend
- **Framework:** Laravel 11
- **Linguagem:** PHP 8.2+
- **Banco de Dados:** MySQL 8.0
- **Autenticação:** Laravel Sanctum (Stateless API Tokens)
- **Padronização:** RESTful API com retornos estruturados em JSON

### Frontend
- **Framework:** Vue.js 3 (Composition API)
- **Linguagem:** TypeScript (Strict Mode)
- **Estilização:** Tailwind CSS v4 (Design Responsivo & Glassmorphism)
- **Requisições:** Axios (com Interceptors para JWT)

---

## 🏗️ Arquitetura do Sistema

O projeto foi estruturado seguindo padrões de separação de responsabilidades (SoC), garantindo que a lógica de negócio, persistência de dados e interface do usuário não fiquem acopladas.

### **Backend - Arquitetura em Camadas**
```text
┌─────────────────────────────────────────────────────────┐
│                    API Layer (Routes)                   │
│  Controllers → Middleware (Auth) → Validation (Request) │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│                  Business Logic Layer                   │
│      Eloquent Models → Relationships → Query Scopes     │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│                   Data Access Layer                     │
│         MySQL Database → Migrations → Storage           │
└─────────────────────────────────────────────────────────┘
```
### **Frontend - Arquitetura Reativa**
```text
┌─────────────────────────────────────────────────────────┐
│                 Presentation Layer                      │
│      Vue Components (SFC) → Tailwind CSS (UI)           │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│                  Logic & State Layer                    │
│      Composables (useProducts) → Reactive State         │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│                 Service Layer (HTTP)                    │
│        Axios Client → Interceptors → Backend API        │
└─────────────────────────────────────────────────────────┘
```

---

## 📡 Endpoints da API

A comunicação entre o Frontend (Vue) e o Backend (Laravel) é feita via **REST API** com retornos padronizados em **JSON**.

### 🔐 Autenticação
| Método | Endpoint | Descrição | Autenticação |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/login` | Login do usuário e geração de token | Não |
| `POST` | `/api/logout` | Revogação do token de acesso | Sim |

---

### 🛍️ Produtos
| Método | Endpoint | Descrição | Permissão |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/products` | Lista produtos (paginado) | `products.view` |
| `POST` | `/api/products` | Cria um novo produto | `products.create` |
| `GET` | `/api/products/{id}` | Detalhes de um produto específico | `products.view` |
| `PUT` | `/api/products/{id}` | Atualiza produto (JSON) | `products.edit` |
| `POST` | `/api/products/{id}` | Atualiza produto (FormData/Imagem) | `products.edit` |
| `DELETE` | `/api/products/{id}` | Exclui um produto do sistema | `products.delete` |

---

### 📂 Categorias
| Método | Endpoint | Descrição | Autenticação |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/categories` | Lista todas as categorias | Não |

---
## 🗄️ Modelagem de Dados (Banco de Dados)

O sistema utiliza um banco de dados relacional MySQL com foco em integridade e performance. Abaixo, a estrutura das principais tabelas e seus relacionamentos:

Relacionamentos
- Produtos ↔ Categorias: Um produto pertence a uma categoria (belongsTo), e uma categoria pode ter vários produtos (hasMany).

### **Dicionário de Dados Principal**

| Tabela | Coluna | Tipo | Descrição |
| :--- | :--- | :--- | :--- |
| **products** | `id` | BigInt (PK) | Identificador único do produto. |
| **products** | `category_id` | BigInt (FK) | Vínculo com a tabela de categorias. |
| **products** | `name` | String (50) | Nome do ativo (Limite de 50 caracteres). |
| **products** | `description` | String (200) | Descrição curta (Limite de 200 caracteres). |
| **products** | `price` | Decimal (10,2) | Valor estimado (Validado como positivo). |
| **products** | `expiration_date` | Date | Data de validade (Bloqueio de data retroativa). |
| **products** | `image` | String | Caminho do arquivo de imagem no storage. |
| **categories** | `id` | BigInt (PK) | Identificador único da categoria. |
| **categories** | `name` | String | Nome (Eletrônicos, Mobiliário, etc.). |


## 🛠️ Como Rodar o Projeto

### 1. Requisitos Prévios
- **PHP** >= 8.2
- **Composer**
- **Node.js** & **NPM**
- **MySQL** (XAMPP / Laragon / Docker)

### 2. Configurando o Backend
1. Entre na pasta: `cd innyx-backend`
2. Instale as dependências: `composer install`
3. Configure o arquivo `.env` (baseado no `.env.example`):
   - Ajuste as credenciais: `DB_DATABASE=innyx_teste_tecnico`
4. Gere a chave da aplicação: `php artisan key:generate`
5. **Migrate & Seed (Obrigatório):**
   `php artisan migrate --seed`
   *(Isso criará as tabelas e as categorias: Eletrônicos, Mobiliário, Software, etc.)*
6. Crie o link simbólico para as imagens: `php artisan storage:link`
7. Inicie o servidor: `php artisan serve`

### 3. Configurando o Frontend
1. Entre na pasta: `cd innyx-frontend`
2. Instale as dependências: `npm install`
3. Inicie o projeto: `npm run dev`

---

## 🔑 Credenciais de Teste
Para facilitar a avaliação técnica, utilize os dados de acesso abaixo (gerados automaticamente via Seeder):

- **E-mail:** admin@innyx.com

- **Senha:** 123456

---

## 📌 Funcionalidades Implementadas
- [x] **Autenticação por Token:** Sistema de Login/Logout seguro via Sanctum.
- [x] **CRUD Completo:** Listar, Criar, Editar e Deletar ativos.
- [x] **Visualização Detalhada:** Modal exclusivo para ver detalhes e imagem ampliada.
- [x] **Upload de Imagem:** Gerenciamento de arquivos com nomes únicos e preview em tempo real.
- [x] **Regras de Negócio:** Validação de preço positivo e bloqueio de datas de validade retroativas.
- [x] **Filtros e Performance:** Busca dinâmica por nome/descrição e paginação otimizada.
- [x] **Interface Premium:** Design limpo, responsivo e com feedbacks visuais (Loading Spinners).

---
## 📁 Estrutura de Pastas Principal

```text
INNYX EDUCAÇÃO/
├── innyx-backend/           # API Laravel 11
│   ├── app/Http/Requests/   # Validações de formulário (Preço, Data)
│   ├── app/Models/          # Ativos, Categorias e Usuários
│   ├── database/seeders/    # População inicial de categorias
│   └── storage/app/public/  # Armazenamento de fotos dos ativos
└── innyx-frontend/          # SPA Vue.js 3 + TypeScript
    ├── src/components/      # UI: Modais, Tabelas e Inputs
    ├── src/composables/     # Lógica: Consumo da API (useProducts)
    └── src/assets/          # Estilização: Global e Tailwind
```

## 👨‍💻 Autor
**Desenvolvido por Ryan Lucas**
