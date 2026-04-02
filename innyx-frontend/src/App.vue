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
  <div class="min-h-screen bg-gray-100 p-4 sm:p-8 font-sans text-gray-900">
    
    <div v-if="!token && !showList" class="max-w-md mx-auto mt-20 bg-white rounded-2xl shadow-2xl p-8 border border-gray-200 text-center">
      <h1 class="text-4xl font-black text-blue-600 italic mb-2">INNYX</h1>
      <p class="text-gray-500 mb-8 font-medium">Acesse o sistema de estoque</p>
      
      <form @submit.prevent="handleLogin" class="space-y-4 text-left">
        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1">E-mail</label>
          <input v-model="email" type="email" required class="w-full px-4 py-3 rounded-xl bg-gray-100 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1">Senha</label>
          <input v-model="password" type="password" required class="w-full px-4 py-3 rounded-xl bg-gray-100 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>
        <p v-if="errorMessage" class="text-red-500 text-sm font-bold text-center">{{ errorMessage }}</p>
        <button type="submit" :disabled="isLoading" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg transition-all flex justify-center items-center gap-2">
          <span v-if="isLoading" class="animate-spin border-2 border-white border-t-transparent rounded-full h-5 w-5"></span>
          {{ isLoading ? 'AUTENTICANDO...' : 'ENTRAR' }}
        </button>
      </form>
    </div>

    <div v-else-if="token && !showList" class="max-w-lg mx-auto mt-20 text-center bg-white p-12 rounded-3xl shadow-xl border border-gray-100">
      <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
      </div>
      <h2 class="text-3xl font-bold mb-2">Bem-vindo, Ryan!</h2>
      <p class="text-gray-500 mb-8 italic">Seu acesso está garantido.</p>
      <div class="flex gap-4 justify-center">
        <button @click="handleLogout" class="px-6 py-3 rounded-xl font-bold text-red-500 uppercase text-xs tracking-widest">Sair</button>
        <button @click="fetchProducts(1)" class="bg-blue-600 hover:bg-blue-700 px-8 py-3 rounded-xl font-bold text-white shadow-lg uppercase text-xs tracking-widest transition-all">
          Ir para Produtos
        </button>
      </div>
    </div>

    <div v-else-if="showList" class="w-full max-w-6xl mx-auto space-y-6">
      
      <div class="flex flex-col sm:flex-row justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-200 gap-4">
        <div>
          <h1 class="text-2xl font-black text-gray-800 uppercase tracking-tighter italic">Estoque</h1>
        </div>
        
        <div class="relative w-full sm:w-96">
          <input v-model="search" @input="fetchProducts(1)" type="text" placeholder="Buscar produto..." 
            class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50">
          <span class="absolute left-3 top-2.5 opacity-30">🔍</span>
        </div>
        
        <button @click="showList = false" class="text-gray-400 text-xs font-bold uppercase hover:text-gray-600">Voltar</button>
      </div>

      <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200 relative">
        <div v-if="isFetching" class="absolute inset-0 bg-white/70 z-10 flex items-center justify-center backdrop-blur-sm">
          <div class="animate-spin border-4 border-blue-600 border-t-transparent rounded-full h-12 w-12"></div>
        </div>

        <table class="w-full text-left">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Produto</th>
              <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Preço</th>
              <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4">
                <div class="font-bold text-gray-800">{{ product.name }}</div>
                <div class="text-xs text-gray-500 truncate max-w-xs">{{ product.description }}</div>
              </td>
              <td class="px-6 py-4 font-mono font-bold text-green-600">
                R$ {{ product.price }}
              </td>
              <td class="px-6 py-4 flex gap-2">
                <button class="text-blue-500 hover:bg-blue-50 p-2 rounded-lg">✏️</button>
                <button class="text-red-500 hover:bg-red-50 p-2 rounded-lg">🗑️</button>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-center items-center gap-4">
          <button @click="fetchProducts(currentPage - 1)" :disabled="currentPage === 1" 
            class="px-4 py-2 rounded-lg bg-white border disabled:opacity-30 font-bold text-xs uppercase shadow-sm">Anterior</button>
          <span class="text-sm font-medium text-gray-600">Página {{ currentPage }} de {{ lastPage }}</span>
          <button @click="fetchProducts(currentPage + 1)" :disabled="currentPage === lastPage"
            class="px-4 py-2 rounded-lg bg-white border disabled:opacity-30 font-bold text-xs uppercase shadow-sm">Próxima</button>
        </div>
      </div>
    </div>

  </div>
</template>