# Desafio Técnico - Sistema de Gerenciamento de Produtos (INNYX)

Este é um sistema Full Stack para gerenciamento de produtos e categorias, desenvolvido como parte de um desafio técnico, realizado pela Innyx. O projeto utiliza **Laravel 10+** no Backend e **Vue.js 3** com **TypeScript** no Frontend.

## 🚀 Tecnologias Utilizadas

### Backend
- **Framework:** Laravel 10
- **Linguagem:** PHP 8.2
- **Banco de Dados:** MySQL 8.0
- **Autenticação:** Laravel Sanctum (Tokens API)
- **Padronização:** RESTful API com retornos em JSON

### Frontend
- **Framework:** Vue.js 3 (Composition API)
- **Linguagem:** TypeScript
- **Estilização:** Tailwind CSS v4 (Responsivo)
- **Requisições:** Axios

---

## 🛠️ Como Rodar o Projeto

### 1. Requisitos Prévios
- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL (XAMPP/Laragon)

### 2. Configurando o Backend
1. Entre na pasta: `cd innyx-backend`
2. Instale as dependências: `composer install`
3. Configure o arquivo `.env` (use o `.env.example` como base):
   - Ajuste as credenciais do banco `DB_DATABASE=innyx_teste_tecnico`
4. Gere a chave da aplicação: `php artisan key:generate`
5. Rode as migrations: `php artisan migrate`
6. Crie o link simbólico para as imagens: `php artisan storage:link`
7. Inicie o servidor: `php artisan serve`

### 3. Configurando o Frontend
1. Entre na pasta: `cd innyx-frontend`
2. Instale as dependências: `npm install`
3. Inicie o projeto: `npm run dev`

---

## 📌 Funcionalidades Implementadas
- [x] Autenticação por Token (Login/Logout).
- [x] CRUD completo de Produtos (Listar, Criar, Editar, Deletar).
- [x] Upload de imagem com nome único.
- [x] Validação de preço positivo e data de validade futura.
- [x] Filtro de busca por nome/descrição e paginação.
- [x] Interface totalmente responsiva.
- [x] Feedbacks visuais (Loading Spinners e mensagens de erro).

---

## 👤 Autor
**Ryan Lucas** - DEV FullStack Jr