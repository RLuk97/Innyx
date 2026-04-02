<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';

// Estados Reativos (Requisito 29: Feedbacks)
const email = ref('');
const password = ref('');
const token = ref(localStorage.getItem('token') || '');
const isLoading = ref(false);
const errorMessage = ref('');
const products = ref([]); // Onde os produtos ficarão guardados
const search = ref(''); // Para o filtro de busca
const currentPage = ref(1); // Página atual
const lastPage = ref(1); // Última página (vinda do Laravel)
const isFetching = ref(false); // Spinner de carregamento dos produtos
const showList = ref(false); // Controla se mostra o login ou a lista

// Configuração global do Axios para enviar o Token
axios.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

const fetchProducts = async (page = 1) => {
  isFetching.value = true;
  try {
    const response = await axios.get(`http://localhost:8000/api/products`, {
      params: {
        page: page,
        search: search.value
      }
    });

    products.value = response.data.data;
    currentPage.value = response.data.current_page;
    lastPage.value = response.data.last_page;
    
    showList.value = true; // Muda a visão para a listagem
  } catch (error) {
    console.error("Erro ao buscar produtos:", error);
    alert("Sessão expirada ou erro no servidor. Faça login novamente.");
    handleLogout();
  } finally {
    isFetching.value = false;
  }
};

const handleLogin = async () => {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    const response = await axios.post('http://localhost:8000/api/login', {
      email: email.value,
      password: password.value
    });

    token.value = response.data.access_token;
    localStorage.setItem('token', token.value);
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Falha na conexão com o servidor.';
    console.error('Erro no login:', error);
  } finally {
    isLoading.value = false;
  }
};

const handleLogout = () => {
  token.value = '';
  showList.value = false; // Ajuste: Garante que volta para a tela de login
  localStorage.removeItem('token');
};
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4 font-sans text-gray-900">
    
    <div v-if="!token" class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8 border border-gray-200 text-center">
      <h1 class="text-4xl font-black text-blue-600 italic mb-2">INNYX</h1>
      <p class="text-gray-500 mb-8 font-medium">Gerenciador de Estoque Profissional</p>

      <form @submit.prevent="handleLogin" class="space-y-4 text-left">
        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1">E-mail</label>
          <input v-model="email" type="email" required placeholder="admin@innyx.com"
            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none transition-all border-none bg-gray-100">
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1">Senha</label>
          <input v-model="password" type="password" required placeholder="••••••"
            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none transition-all border-none bg-gray-100">
        </div>

        <p v-if="errorMessage" class="text-red-500 text-sm font-bold text-center">
          {{ errorMessage }}
        </p>

        <button type="submit" :disabled="isLoading"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition-all flex justify-center items-center gap-2 shadow-lg disabled:bg-gray-400 uppercase tracking-widest">
          <span v-if="isLoading" class="animate-spin border-2 border-white border-t-transparent rounded-full h-5 w-5"></span>
          {{ isLoading ? 'Processando...' : 'Entrar' }}
        </button>
      </form>
    </div>

    <div v-else-if="token && !showList" class="text-center bg-white p-12 rounded-3xl shadow-xl border border-gray-100">
      <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <h2 class="text-3xl font-bold mb-2">Bem-vindo, Ryan!</h2>
      <p class="text-gray-500 mb-8">Token salvo com sucesso no LocalStorage.</p>
      
      <div class="flex gap-4">
        <button @click="handleLogout" class="px-6 py-3 rounded-xl font-bold text-red-500 hover:bg-red-50 transition-all uppercase text-xs tracking-widest">
          Sair
        </button>
        <button @click="fetchProducts(1)" class="bg-blue-600 hover:bg-blue-700 px-8 py-3 rounded-xl font-bold text-white shadow-lg transition-all uppercase text-xs tracking-widest">
          Ir para Produtos
        </button>
      </div>
    </div>

    <div v-else-if="showList" class="w-full max-w-6xl animate-fade-in">
       <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-200">
          <h2 class="text-2xl font-bold mb-4">Tabela de Produtos aparecerá aqui!</h2>
          <button @click="showList = false" class="text-blue-600 underline">Voltar</button>
       </div>
    </div>

  </div>
</template>