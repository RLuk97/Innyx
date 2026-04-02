<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';

// Estados Reativos (Requisito 29: Feedbacks)
const email = ref('');
const password = ref('');
const token = ref(localStorage.getItem('token') || '');
const isLoading = ref(false);
const errorMessage = ref('');

const handleLogin = async () => {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    // Chamada ao Backend Laravel
    const response = await axios.post('http://localhost:8000/api/login', {
      email: email.value,
      password: password.value
    });

    // Pega o token da resposta (conforme vimos no Thunder Client)
    const accessToken = response.data.access_token;
    
    // Salva no estado e no localStorage para não deslogar ao dar F5
    token.value = accessToken;
    localStorage.setItem('token', accessToken);
    
    alert('Login realizado com sucesso!');
  } catch (error: any) {
    // Trata erros de e-mail/senha ou servidor fora do ar
    errorMessage.value = error.response?.data?.message || 'Falha na conexão com o servidor.';
    console.error('Erro no login:', error);
  } finally {
    isLoading.value = false;
  }
};

const handleLogout = () => {
  token.value = '';
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

    <div v-else class="text-center bg-white p-12 rounded-3xl shadow-xl border border-gray-100">
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
        <button class="bg-blue-600 hover:bg-blue-700 px-8 py-3 rounded-xl font-bold text-white shadow-lg transition-all uppercase text-xs tracking-widest">
          Ir para Produtos
        </button>
      </div>
    </div>

  </div>
</template>