# Sistema de Inventário de Ativos (INNYX)

Este é um sistema **Full Stack** profissional para gerenciamento de ativos e inventário, desenvolvido como parte de um desafio técnico para a **Innyx**. O projeto foi totalmente **containerizado com Docker**, utiliza **Laravel 11** no Backend e **Vue.js 3** com **TypeScript** no Frontend.

## Tecnologias e Diferenciais

### Infraestrutura & DevOps
- **Docker & Docker Compose:** Ambiente isolado para Frontend, Backend e Banco de Dados (MySQL).
- **Persistência de Dados:** Volumes configurados para persistência do banco e storage de imagens.

### Segurança & ACL
- **Níveis de Acesso:** Implementação de permissões distintas entre **Administrador** e **Usuário Comum**.
- **Laravel Sanctum:** Autenticação segura via API Tokens.

### Interface & UX
- **Vue.js 3 (Composition API) + TypeScript.**
- **Tailwind CSS v4:** Design moderno com Glassmorphism, efeitos de blur e responsividade total.
- **Feedback Visual:** Skeleton loaders e estados de carregamento em todas as ações assíncronas.

### Backend & Linguagem
- **PHP 8.2+ (Laravel 11):** O motor do sistema, responsável por toda a lógica de negócios, segurança e processamento de dados dentro do container Docker.
- **Arquitetura REST:** Comunicação eficiente e padronizada entre as camadas do sistema.

## Controle de Acesso (ACL)

O sistema diferencia as funcionalidades baseadas no perfil do usuário logado:

- **Perfil Administrador:** Acesso total ao sistema (CRUD completo). Pode criar, editar, visualizar e excluir qualquer ativo.
- **Perfil Usuário Comum:** Acesso restrito. Pode apenas listar e visualizar os detalhes técnicos/imagens dos ativos (Read-only).

## 🛠️ Como Rodar o Projeto

### 1. Requisitos Prévios
Para executar este projeto em ambiente de desenvolvimento, o único requisito é ter o **Docker** e o **Docker Compose** instalados em sua máquina. 

*(Não é necessário instalar PHP, Node.js ou MySQL localmente, pois o ambiente é totalmente isolado em containers).*

### 2. Passo a Passo com Docker
1. **Clone o repositório:**
   `git clone https://github.com/RLuk97/Innyx.git`
2. **Suba os containers:**
   `docker-compose up -d --build`
3. **Prepare o ambiente (Execute apenas uma vez):**
   `docker exec -it innyx-backend php artisan migrate --seed`
   `docker exec -it innyx-backend php artisan storage:link`
   
### 4. Acessar a aplicação:

- Frontend: http://localhost:5173

- API Backend: http://localhost:8000

## Credenciais de Teste

| Perfil | E-mail | Senha | Permissões |
| :--- | :--- | :--- | :--- |
| Admin | admin@innyx.com | password | Acesso Total (CRUD) |
| User | user@innyx.com | password | Apenas Visualização |

## Estrutura de Pastas Principal
```text
INNYX EDUCAÇÃO/
├── innyx-backend/          # API Laravel 11 (Dockerizado)
│   ├── app/Http/Requests/  # Validações (Preço, Data)
│   ├── database/seeders/   # População de ACL e Categorias
│   └── storage/app/public/ # Fotos dos ativos
├── innyx-frontend/         # SPA Vue.js 3 + TS (Dockerizado)
│   ├── src/composables/    # Lógica de consumo (useProducts)
│   └── src/App.vue         # Interface principal com lógica de ACL
└── docker/                 # Configurações de infra e persistência de dados
```

## Arquitetura do Sistema

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
> **Onde entra o PHP?**
> O **PHP 8.2** é a linguagem que processa toda a lógica contida no diagrama acima dentro do container `innyx-backend`. Ele é responsável por interpretar as requisições enviadas pelo Frontend (Vue.js), aplicar as regras de validação do Laravel, realizar a comunicação com o banco MySQL e gerenciar o armazenamento de imagens no Storage do servidor.

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
### **Infraestrutura - Orquestração com Docker**
Toda a arquitetura acima é encapsulada e orquestrada via **Docker Compose**, garantindo que as camadas de Frontend, Backend e Banco de Dados se comuniquem de forma isolada e segura em uma rede interna, facilitando o deploy e a replicabilidade do ambiente.

## Requisitos do Sistema
### 1. Requisitos Funcionais (RF)
- **Autenticação:** O sistema permite que apenas usuários autenticados acessem o inventário.
- **Controle de Acesso (ACL):** Diferenciação de permissões entre Administradores (CRUD total) e Usuários (Visualização).
- **Gerenciamento de Ativos:** O sistema permite Criar, Ler, Atualizar e Excluir (CRUD) produtos.
- **Categorização:** Todo produto é obrigatoriamente vinculado a uma categoria pré-definida.
- **Upload de Mídia:** O sistema permite o upload de uma imagem representativa para cada produto.
- **Busca e Filtros:** O usuário consegue filtrar produtos por nome ou descrição em tempo real.
- **Paginação:** A listagem de produtos é paginada para garantir a performance da interface.
- **Visualização Detalhada:** É possível visualizar todos os dados de um ativo em um modal exclusivo.

### 2. Requisitos Não Funcionais (RNF)
- **Dockerização:** Ambiente orquestrado para garantir replicabilidade em qualquer máquina.
- **Segurança:** Senhas criptografadas e comunicação protegida por tokens (Sanctum).
- **Responsividade:** Interface adaptável para dispositivos móveis e desktops (Mobile-first).
- **Validação de Dados:** O sistema não permite preços negativos ou datas de validade retroativas.
- **Performance:** As requisições de busca são otimizadas para evitar sobrecarga no servidor.
- **Usabilidade:** O sistema fornece feedbacks visuais (Spinners) durante operações assíncronas.

## Endpoints da API

A comunicação entre o Frontend (Vue) e o Backend (Laravel) é feita via **REST API** com retornos padronizados em **JSON**.

### Autenticação
| Método | Endpoint | Descrição | Autenticação |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/login` | Login do usuário e geração de token | Não |
| `POST` | `/api/logout` | Revogação do token de acesso | Sim |

### Produtos
| Método | Endpoint | Descrição | Permissão |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/products` | Lista produtos (paginado) | `products.view` |
| `POST` | `/api/products` | Cria um novo produto | `products.create` |
| `GET` | `/api/products/{id}` | Detalhes de um produto específico | `products.view` |
| `PUT` | `/api/products/{id}` | Atualiza produto (JSON) | `products.edit` |
| `POST` | `/api/products/{id}` | Atualiza produto (FormData/Imagem) | `products.edit` |
| `DELETE` | `/api/products/{id}` | Exclui um produto do sistema | `products.delete` |

### Categorias
| Método | Endpoint | Descrição | Autenticação |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/categories` | Lista todas as categorias | Não |

> **Nota:** Todos os endpoints de produtos e categorias exigem o header `Authorization: Bearer {token}` para garantir a segurança dos dados, exceto o endpoint de login.

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
| **users** | `id` | BigInt (PK) | Identificador único do usuário. |
| **users** | `name` | String | Nome completo do usuário. |
| **users** | `email` | String (Unique) | E-mail usado para login e identificação. |
| **users** | `password` | String | Hash criptografado da senha (Bcrypt). |
| **users** | `role` | Enum ('admin', 'user') | Nível de acesso que define as permissões no sistema. |

## Funcionalidades Implementadas
- [x] **Autenticação por Token:** Sistema de Login/Logout seguro via Sanctum.
- [x] **CRUD Completo:** Listar, Criar, Editar e Deletar ativos.
- [x] **Visualização Detalhada:** Modal exclusivo para ver detalhes e imagem ampliada.
- [x] **Upload de Imagem:** Gerenciamento de arquivos com nomes únicos e preview em tempo real.
- [x] **Regras de Negócio:** Validação de preço positivo e bloqueio de datas de validade retroativas.
- [x] **Filtros e Performance:** Busca dinâmica por nome/descrição e paginação otimizada.
- [x] **Interface Premium:** Design limpo, responsivo e com feedbacks visuais (Loading Spinners).
- [x] **Laravel 11 & PHP 8.2:** Utilização das últimas versões estáveis para garantir performance e as melhores práticas de segurança de backend.

## Autor
**Desenvolvido por Ryan Lucas**
