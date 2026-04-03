<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useProducts } from './composables/useProducts';

// 1. Lógica do Composable
const { products, search, currentPage, lastPage, isFetching, fetchProducts } = useProducts();

// 2. Estados de Autenticação
const token = ref(localStorage.getItem('token') || '');
const email = ref('');
const password = ref('');
const isLoading = ref(false);

// 3. Estados do Modal de Cadastro
const isModalOpen = ref(false);
const productForm = ref({
    name: '',
    price: '',
    description: '',
    image: null as File | null,
    imagePreview: ''
});

// 4. Axios Interceptor
axios.interceptors.request.use((config) => {
  const activeToken = localStorage.getItem('token');
  if (activeToken) {
    config.headers.Authorization = `Bearer ${activeToken}`;
  }
  return config;
});

// 5. Ações de Autenticação
const handleLogin = async () => {
    isLoading.value = true;
    try {
        const { data } = await axios.post('http://localhost:8000/api/login', { 
            email: email.value, 
            password: password.value 
        });
        token.value = data.access_token;
        localStorage.setItem('token', data.access_token);
        await fetchProducts(1);
    } catch (e) {
        alert('Falha na autenticação. Verifique suas credenciais.');
    } finally {
        isLoading.value = false;
    }
};

const handleLogout = () => {
    token.value = '';
    localStorage.removeItem('token');
};

// 6. Funções do Modal
const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    productForm.value = { name: '', price: '', description: '', image: null, imagePreview: '' };
};

const handleImageUpload = (event: any) => {
    const file = event.target.files[0];
    if (file) {
        productForm.value.image = file;
        productForm.value.imagePreview = URL.createObjectURL(file);
    }
};

const submitForm = async () => {
    if (!productForm.value.name || !productForm.value.price) {
        alert("Por favor, preencha os campos essenciais.");
        return;
    }

    isLoading.value = true; // Ativa o loading no botão
    
    try {
        const formData = new FormData();
        formData.append('name', productForm.value.name);
        formData.append('price', productForm.value.price);
        formData.append('description', productForm.value.description);
        
        if (productForm.value.image) {
            formData.append('image', productForm.value.image);
        }

        // No seu App.vue, garanta que o POST aponte para o endereço completo do Backend
        await axios.post('http://localhost:8000/api/products', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        // 1. Feedback Visual de Sucesso
        alert("✅ Registro de Ativo Concluído com Sucesso!");

        // 2. Atualiza a lista em tempo real (Página 1 para ver o novo item no topo)
        await fetchProducts(1);
        
        // 3. Fecha o modal e limpa tudo
        closeModal();

    } catch (e) {
        console.error(e);
        alert('❌ Erro na sincronização. Verifique a conexão com o Buffer.');
    } finally {
        isLoading.value = false;
    }
};

const formatCurrency = (value: any) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
};

onMounted(() => { 
    if (token.value) fetchProducts(1); 
});
</script>

<template>
  <div class="min-h-screen bg-[#050509] font-sans text-slate-300 selection:bg-[#7c3aed]/20 overflow-x-hidden">
    
    <div v-if="!token" class="relative flex min-h-screen items-center justify-center p-6 bg-[#050509]">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_#7c3aed15_0%,_transparent_50%)]"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,_#6366f110_0%,_transparent_50%)]"></div>

      <Transition name="fade" appear>
        <div class="relative w-full max-w-md bg-white/[0.03] backdrop-blur-3xl p-12 rounded-[3rem] border border-white/10 shadow-[0_50px_100px_-20px_rgba(124,58,237,0.15)] text-center">
          <img src="https://innyx.com/wp-content/uploads/2025/06/logo-innyx_branco.webp" class="h-12 mx-auto mb-10 drop-shadow-2xl">
          <div class="mb-8 text-center">
            <h2 class="text-[10px] font-black uppercase tracking-[0.5em] text-[#7c3aed]">Innyx Protocol</h2>
            <p class="text-slate-600 text-[9px] mt-1 font-bold italic uppercase tracking-widest">Acesso ao Buffer de Gestão</p>
          </div>
          <form @submit.prevent="handleLogin" class="space-y-4">
            <input v-model="email" type="email" placeholder="E-mail Corporativo" class="w-full bg-white/[0.05] border border-white/5 rounded-2xl px-6 py-4 text-white outline-none focus:border-[#7c3aed]/50 focus:bg-white/[0.08] transition-all text-sm">
            <input v-model="password" type="password" placeholder="Chave de Acesso" class="w-full bg-white/[0.05] border border-white/5 rounded-2xl px-6 py-4 text-white outline-none focus:border-[#7c3aed]/50 focus:bg-white/[0.08] transition-all text-sm">
            <button :disabled="isLoading" class="w-full bg-[#7c3aed] text-white font-black py-5 rounded-2xl hover:bg-white hover:text-black transition-all shadow-xl shadow-[#7c3aed]/20 uppercase tracking-[0.3em] text-xs cursor-pointer active:scale-95 disabled:opacity-50 disabled:cursor-wait">
                    {{ isLoading ? 'SINCRONIZANDO COM O BANCO...' : 'Confirmar Registro no Buffer' }}
          </button>
          </form>
        </div>
      </Transition>
    </div>

    <div v-else class="animate-in fade-in duration-700">
      <header class="fixed top-0 inset-x-0 z-50 bg-[#050509]/70 backdrop-blur-xl border-b border-white/5 shadow-2xl">
        <div class="max-w-7xl mx-auto px-8 h-24 flex justify-between items-center">
          <div class="flex items-center gap-10">
             <img src="https://innyx.com/wp-content/uploads/2025/06/logo-innyx_branco.webp" alt="Innyx" class="h-9 w-auto filter drop-shadow-[0_0_8px_rgba(255,255,255,0.2)]">
             <div class="h-8 w-px bg-white/10 hidden sm:block"></div>
             <h1 class="text-[10px] font-black uppercase tracking-[0.5em] text-white/30 hidden sm:block">Gestão <span class="text-[#7c3aed]">Core</span></h1>
          </div>
          <div class="flex items-center gap-10">
            <div class="relative hidden lg:block">
              <input v-model="search" @input="fetchProducts(1)" type="text" placeholder="Filtrar base de dados..." class="bg-white/5 border border-white/10 rounded-full px-12 py-2.5 text-xs focus:ring-1 focus:ring-[#7c3aed] outline-none w-72 text-white transition-all shadow-inner">
              <span class="absolute left-4 top-3 opacity-20 text-[10px]">🔍</span>
            </div>
            <button @click="handleLogout" class="text-[9px] font-black uppercase tracking-[0.2em] text-red-500 hover:text-red-400 cursor-pointer">Sign Out</button>
          </div>
        </div>
      </header>

      <main class="pt-40 pb-20 px-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-8">
            <div class="space-y-1">
                <p class="text-[9px] font-black uppercase tracking-[0.4em] text-[#7c3aed]">Dashboard Administrativo</p>
                <h2 class="text-xl font-bold text-white tracking-tighter italic">Controle de Ativos</h2>
            </div>
            <button @click="openModal" class="bg-[#7c3aed] text-white px-10 py-3 rounded-full font-black text-[10px] uppercase tracking-widest hover:bg-white hover:text-black transition-all shadow-xl shadow-[#7c3aed]/20 cursor-pointer">
                + Novo Registro
            </button>
        </div>

        <div class="bg-white/[0.02] backdrop-blur-3xl rounded-[2.5rem] border border-white/5 shadow-2xl overflow-hidden relative">
          <div v-if="isFetching" class="absolute top-0 inset-x-0 h-0.5 bg-[#7c3aed]/20 overflow-hidden z-10">
             <div class="h-full bg-[#7c3aed] animate-progress origin-left"></div>
          </div>
          <table class="w-full border-collapse">
            <thead>
              <tr class="text-[9px] font-black uppercase tracking-[0.25em] text-slate-600 border-b border-white/5 bg-white/[0.01]">
                <th class="px-10 py-5 text-left italic">Especificação</th>
                <th class="px-10 py-5 text-center text-[#7c3aed] italic">Valuation</th>
                <th class="px-10 py-5 text-center italic">Interação</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="product in products" :key="product.id" class="group hover:bg-white/[0.02] transition-colors duration-500">
                <td class="px-10 py-3.5">
                  <div class="flex items-center gap-6">
                    <div class="w-12 h-12 rounded-2xl bg-black border border-white/5 flex-none overflow-hidden group-hover:border-[#7c3aed]/40 transition-all">
                        <img :src="product.image && product.image.startsWith('http') ? product.image : `http://localhost:8000/storage/${product.image}`" class="w-full h-full object-cover opacity-40 grayscale group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-700">
                    </div>
                    <div>
                      <div class="font-bold text-white text-base group-hover:text-[#7c3aed] transition-colors tracking-tight">{{ product.name }}</div>
                      <div class="text-[10px] text-slate-600 font-medium line-clamp-1 italic max-w-sm">{{ product.description }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-10 py-3.5 text-center font-mono font-black text-white/90 text-lg tracking-tighter">
                   {{ formatCurrency(product.price) }}
                </td>
                <td class="px-10 py-3.5">
                  <div class="flex justify-center gap-3 opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-500">
                    <button class="px-6 py-2 rounded-xl bg-white/5 text-white text-[9px] font-black uppercase tracking-widest hover:bg-[#7c3aed] transition-all border border-white/5 cursor-pointer active:scale-95 min-w-[90px]">Editar</button>
                    <button class="flex items-center justify-center px-6 py-2 rounded-xl bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-all cursor-pointer active:scale-95 min-w-[90px]">
                      <span class="text-[9px] font-black uppercase tracking-widest">Excluir</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <footer class="p-8 flex flex-col items-center justify-center gap-6 border-t border-white/5 bg-white/[0.01]">
            <div class="text-[9px] font-black text-slate-600 uppercase tracking-[0.4em] italic">Sincronizando Buffer: Página {{ currentPage }} de {{ lastPage }}</div>
            <div class="flex items-center gap-4">
                <button @click="fetchProducts(currentPage - 1)" :disabled="currentPage === 1" class="w-10 h-10 rounded-xl border border-white/5 bg-white/5 flex items-center justify-center disabled:opacity-5 hover:bg-white hover:text-black cursor-pointer text-white">←</button>
                <div class="flex gap-2">
                    <button v-for="page in lastPage" :key="page" @click="fetchProducts(page)" :class="['w-10 h-10 rounded-xl font-mono font-black text-xs transition-all border cursor-pointer', currentPage === page ? 'bg-[#7c3aed] border-[#7c3aed] text-white shadow-lg shadow-[#7c3aed]/20 scale-110' : 'bg-white/5 border-white/5 text-slate-400 hover:bg-white/10 hover:text-white']">{{ page }}</button>
                </div>
                <button @click="fetchProducts(currentPage + 1)" :disabled="currentPage === lastPage" class="w-10 h-10 rounded-xl border border-white/5 bg-white/5 flex items-center justify-center disabled:opacity-5 hover:bg-white hover:text-black cursor-pointer text-white">→</button>
            </div>
          </footer>
        </div>
      </main>

      <Transition name="fade">
        <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
          <div @click="closeModal" class="absolute inset-0 bg-[#050509]/80 backdrop-blur-md"></div>
          <div class="relative w-full max-w-2xl bg-[#0f172a]/60 backdrop-blur-3xl rounded-[3rem] border border-white/10 shadow-[0_50px_100px_-20px_rgba(124,58,237,0.2)] overflow-hidden">
            <div class="p-10 border-b border-white/5 flex justify-between items-center">
              <div>
                <p class="text-[9px] font-black uppercase tracking-[0.4em] text-[#7c3aed]">Innyx Systems</p>
                <h3 class="text-2xl font-bold text-white tracking-tighter italic">Novo Registro de Ativo</h3>
              </div>
              <button @click="closeModal" class="w-12 h-12 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-red-500 transition-all cursor-pointer text-xl">×</button>
            </div>
            <form @submit.prevent="submitForm" class="p-10 space-y-6">
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2 text-left">
                  <label class="text-[9px] font-black uppercase tracking-widest text-slate-500 ml-2">Nome do Recurso</label>
                  <input v-model="productForm.name" type="text" placeholder="Ex: Servidor Core i9" class="w-full bg-white/5 border border-white/5 rounded-2xl px-6 py-4 text-white outline-none focus:border-[#7c3aed]/50 transition-all">
                </div>
                <div class="space-y-2 text-left">
                  <label class="text-[9px] font-black uppercase tracking-widest text-slate-500 ml-2">Valuation (BRL)</label>
                  <input v-model="productForm.price" type="number" step="0.01" placeholder="0,00" class="w-full bg-white/5 border border-white/5 rounded-2xl px-6 py-4 text-white outline-none focus:border-[#7c3aed]/50 transition-all">
                </div>
              </div>
              <div class="space-y-2 text-left">
                <label class="text-[9px] font-black uppercase tracking-widest text-slate-500 ml-2">Especificação Detalhada</label>
                <textarea v-model="productForm.description" rows="3" placeholder="Descreva os detalhes técnicos..." class="w-full bg-white/5 border border-white/5 rounded-2xl px-6 py-4 text-white outline-none focus:border-[#7c3aed]/50 transition-all resize-none"></textarea>
              </div>
              <div class="flex items-center gap-6 p-6 bg-white/5 rounded-[2rem] border border-dashed border-white/10">
                <div class="w-24 h-24 rounded-2xl bg-black flex-none overflow-hidden border border-white/5">
                  <img v-if="productForm.imagePreview" :src="productForm.imagePreview" class="w-full h-full object-cover">
                  <div v-else class="w-full h-full flex items-center justify-center text-2xl opacity-20">📷</div>
                </div>
                <div class="space-y-1 text-left">
                  <p class="text-xs font-bold text-white">Documentação Visual</p>
                  <p class="text-[10px] text-slate-500">PNG, JPG ou WEBP (Max. 2MB)</p>
                  <input type="file" @change="handleImageUpload" class="hidden" id="fileUpload">
                  <label for="fileUpload" class="inline-block mt-2 text-[9px] font-black uppercase tracking-widest text-[#7c3aed] cursor-pointer hover:text-white transition-colors">Selecionar Arquivo</label>
                </div>
              </div>
              <button class="w-full bg-[#7c3aed] text-white font-black py-5 rounded-2xl hover:bg-white hover:text-black transition-all shadow-xl shadow-[#7c3aed]/20 uppercase tracking-[0.3em] text-xs cursor-pointer active:scale-95">Confirmar Registro no Buffer</button>
            </form>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<style>
body { background-color: #050509; margin: 0; }
@keyframes progress { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
.animate-progress { animation: progress 2s infinite ease-in-out; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.6s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
</style>