<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useProducts } from './composables/useProducts';

// 1. Lógica do Composable
const { products, search, currentPage, lastPage, isFetching, fetchProducts } = useProducts();

// 2. Estados de Autenticação e Sistema
const token = ref(localStorage.getItem('token') || '');
const email = ref('');
const password = ref('');
const isLoading = ref(false);

// 3. Estados do Modal (Cadastro + Edição)
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingProductId = ref<number | null>(null);

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
  if (activeToken) config.headers.Authorization = `Bearer ${activeToken}`;
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
        alert('E-mail ou senha incorretos.');
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
    isEditing.value = false;
    editingProductId.value = null;
    isModalOpen.value = true;
};

const openEditModal = (product: any) => {
    isEditing.value = true;
    editingProductId.value = product.id;
    productForm.value = {
        name: product.name,
        price: product.price,
        description: product.description,
        image: null, 
        imagePreview: product.image 
    };
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    isEditing.value = false;
    editingProductId.value = null;
    productForm.value = { name: '', price: '', description: '', image: null, imagePreview: '' };
};

const deleteProduct = async (id: number) => {
    if (!confirm("⚠️ Atenção: Deseja realmente excluir este registro permanentemente?")) {
        return;
    }

    isLoading.value = true;
    try {
        await axios.delete(`http://localhost:8000/api/products/${id}`);
        alert("✅ Registro excluído com sucesso!");
        await fetchProducts(currentPage.value);
    } catch (e) {
        console.error(e);
        alert("❌ Erro ao excluir.");
    } finally {
        isLoading.value = false;
    }
};

const handleImageUpload = (event: any) => {
    const file = event.target.files[0];
    if (file) {
        productForm.value.image = file;
        productForm.value.imagePreview = URL.createObjectURL(file);
    }
};

const submitForm = async () => {
    if (!productForm.value.name || !productForm.value.price) return alert("Preencha os campos obrigatórios.");
    isLoading.value = true;
    try {
        const formData = new FormData();
        formData.append('name', productForm.value.name);
        formData.append('price', String(productForm.value.price));
        formData.append('description', productForm.value.description);
        if (productForm.value.image) formData.append('image', productForm.value.image);

        if (isEditing.value && editingProductId.value) {
            formData.append('_method', 'PUT'); 
            await axios.post(`http://localhost:8000/api/products/${editingProductId.value}`, formData);
            alert("✅ Alterações salvas!");
        } else {
            await axios.post('http://localhost:8000/api/products', formData);
            alert("✅ Cadastrado!");
        }
        await fetchProducts(1);
        closeModal();
    } catch (e) {
        console.error(e);
        alert('❌ Erro de conexão.');
    } finally {
        isLoading.value = false;
    }
};

const formatCurrency = (value: any) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
};

onMounted(() => { if (token.value) fetchProducts(1); });
</script>

<template>
  <div class="min-h-screen bg-[#f1f5f9] font-sans text-slate-600 selection:bg-[#7c3aed]/10 overflow-x-hidden relative">
    
    <div v-if="!token" class="flex min-h-screen items-center justify-center p-6 bg-slate-100">
      <div class="w-full max-w-md bg-white p-10 rounded-[2.5rem] shadow-xl shadow-slate-200 border border-white text-center">
        <img src="https://innyx.com/wp-content/uploads/2025/06/logo-innyx_branco.webp" class="h-8 mx-auto mb-10 invert">
        <h2 class="text-xs font-black uppercase tracking-[0.3em] text-[#7c3aed] mb-8">Portal de Gestão</h2>
        <form @submit.prevent="handleLogin" class="space-y-4">
          <input v-model="email" type="email" placeholder="E-mail" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 outline-none focus:border-[#7c3aed] transition-all">
          <input v-model="password" type="password" placeholder="Senha" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 outline-none focus:border-[#7c3aed] transition-all">
          <button :disabled="isLoading" class="w-full bg-[#7c3aed] text-white font-bold py-4 rounded-2xl hover:bg-[#6d28d9] transition-all shadow-lg shadow-[#7c3aed]/20 uppercase text-[11px] tracking-widest cursor-pointer">
            {{ isLoading ? 'Entrando...' : 'Acessar Sistema' }}
          </button>
        </form>
      </div>
    </div>

    <div v-else>
      <header class="fixed top-0 inset-x-0 z-50 bg-white/70 backdrop-blur-xl border-b border-slate-200 h-20 flex items-center px-8 justify-between">
          <div class="flex items-center gap-6">
              <img src="https://innyx.com/wp-content/uploads/2025/06/logo-innyx_branco.webp" class="h-10 w-auto invert">
          </div>
          <div class="flex items-center gap-6">
            <div class="relative">
              <input v-model="search" @input="fetchProducts(1)" type="text" placeholder="Pesquisar..." class="bg-slate-100/50 border border-slate-200 rounded-full px-10 py-2 text-xs w-64 focus:bg-white outline-none transition-all">
              <span class="absolute left-4 top-2.5 opacity-30">🔍</span>
            </div>
            <button @click="handleLogout" class="text-xs font-bold text-red-500 hover:bg-red-50 px-4 py-2 rounded-lg transition-all cursor-pointer">Sair</button>
          </div>
      </header>

      <main class="pt-32 pb-20 px-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-10">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.4em] text-[#7c3aed]">Inventário</p>
                <h2 class="text-3xl font-bold text-slate-800 tracking-tight italic">Ativos Registrados</h2>
            </div>
            <button @click="openModal" class="bg-[#7c3aed] text-white px-8 py-3 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-[#6d28d9] transition-all shadow-lg shadow-[#7c3aed]/20 cursor-pointer">
                + Adicionar Item
            </button>
        </div>

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
          <table class="w-full text-left">
            <thead>
              <tr class="text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 bg-slate-50/50">
                <th class="px-10 py-5">Especificação</th>
                <th class="px-10 py-5 text-center">Valor Estimado</th>
                <th class="px-10 py-5 text-center">Ações</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="product in products" :key="product.id" class="group hover:bg-slate-50/80 transition-all">
                <td class="px-10 py-5">
                  <div class="flex items-center gap-5">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex-none overflow-hidden group-hover:scale-105 transition-all">
                        <img :src="product.image && product.image.startsWith('http') ? product.image : `http://localhost:8000/storage/${product.image}`" class="w-full h-full object-cover">
                    </div>
                    <div>
                      <div class="font-bold text-slate-800 text-sm tracking-tight">{{ product.name }}</div>
                      <div class="text-[11px] text-slate-400 font-medium line-clamp-1 italic max-w-sm">{{ product.description }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-10 py-5 text-center font-bold text-slate-700">{{ formatCurrency(product.price) }}</td>
                <td class="px-10 py-5 text-center">
                  <div class="flex justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                    <button @click="openEditModal(product)" class="p-2 text-slate-400 hover:text-[#7c3aed] transition-all cursor-pointer"><span class="text-[10px] font-bold uppercase">Editar</span></button>
                    <button @click="deleteProduct(product.id)" class="p-2 text-slate-400 hover:text-red-500 transition-all cursor-pointer"><span class="text-[10px] font-bold uppercase">Excluir</span></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <footer class="p-8 flex flex-col items-center justify-center gap-6 border-t border-slate-100 bg-slate-50/30">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">
                Exibindo Página {{ currentPage }} de {{ lastPage }}
            </div>
            <div class="flex items-center gap-3">
                <button @click="fetchProducts(currentPage - 1)" :disabled="currentPage === 1" class="w-10 h-10 rounded-xl border border-slate-200 bg-white flex items-center justify-center disabled:opacity-30 hover:bg-slate-50 transition-all shadow-sm cursor-pointer">←</button>
                <div class="flex gap-2">
                    <button v-for="page in lastPage" :key="page" @click="fetchProducts(page)" :class="['w-10 h-10 rounded-xl font-bold text-xs transition-all border cursor-pointer', currentPage === page ? 'bg-[#7c3aed] border-[#7c3aed] text-white shadow-lg shadow-[#7c3aed]/20 scale-105' : 'bg-white border-slate-200 text-slate-500 hover:border-[#7c3aed] hover:text-[#7c3aed]']">{{ page }}</button>
                </div>
                <button @click="fetchProducts(currentPage + 1)" :disabled="currentPage === lastPage" class="w-10 h-10 rounded-xl border border-slate-200 bg-white flex items-center justify-center disabled:opacity-30 hover:bg-slate-50 transition-all shadow-sm cursor-pointer">→</button>
            </div>
          </footer>
        </div>
      </main>

      <Transition name="fade">
        <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/10 backdrop-blur-sm">
          <div @click="closeModal" class="absolute inset-0"></div>
          <div class="relative w-full max-w-xl bg-white rounded-[2.5rem] shadow-2xl border border-white overflow-hidden">
            <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
              <h3 class="text-xl font-bold text-slate-800 tracking-tight italic">{{ isEditing ? 'Editar Registro' : 'Cadastrar Registro' }}</h3>
              <button @click="closeModal" class="text-2xl text-slate-300 hover:text-red-500 transition-all cursor-pointer">×</button>
            </div>
            <form @submit.prevent="submitForm" class="p-8 space-y-6">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                  <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Nome</label>
                  <input v-model="productForm.name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-[#7c3aed] transition-all">
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Preço (R$)</label>
                  <input v-model="productForm.price" type="number" step="0.01" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-[#7c3aed] transition-all">
                </div>
              </div>
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Descrição</label>
                <textarea v-model="productForm.description" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-[#7c3aed] transition-all resize-none"></textarea>
              </div>

              <div class="relative group/upload">
                <label 
                    for="fileUpload" 
                    class="flex items-center gap-6 p-6 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 hover:border-[#7c3aed] hover:bg-[#7c3aed]/5 transition-all cursor-pointer group"
                >
                    <div class="w-20 h-20 rounded-xl bg-white flex-none overflow-hidden border border-slate-200 shadow-inner flex items-center justify-center relative">
                        <img v-if="productForm.imagePreview" :src="productForm.imagePreview" class="w-full h-full object-cover">
                        <div v-else class="flex flex-col items-center opacity-20">
                            <span class="text-2xl">📷</span>
                        </div>
                        <div v-if="productForm.imagePreview" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="text-white text-[10px] font-bold">TROCAR</span>
                        </div>
                    </div>

                    <div class="flex-1 space-y-1 text-left">
                        <p class="text-sm font-bold text-slate-700 group-hover:text-[#7c3aed] transition-colors italic">
                            {{ productForm.image ? 'Foto Selecionada' : 'Adicionar Foto' }}
                        </p>
                        <p class="text-[10px] text-slate-400 font-medium">
                            {{ productForm.image ? productForm.image.name : 'Clique para selecionar JPG ou PNG (Máx 2MB)' }}
                        </p>
                    </div>

                    <div class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-black text-slate-500 group-hover:bg-[#7c3aed] group-hover:text-white group-hover:border-[#7c3aed] transition-all uppercase tracking-widest shadow-sm">
                        {{ productForm.image ? 'Alterar' : 'Escolher' }}
                    </div>
                </label>
                <input type="file" @change="handleImageUpload" class="hidden" id="fileUpload">
              </div>

              <button class="w-full bg-[#7c3aed] text-white font-bold py-4 rounded-2xl hover:bg-[#6d28d9] transition-all shadow-lg shadow-[#7c3aed]/20 uppercase text-[11px] tracking-widest cursor-pointer">
                  {{ isEditing ? 'SALVAR ALTERAÇÕES' : 'FINALIZAR CADASTRO' }}
              </button>
            </form>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<style>
body { background-color: #f1f5f9; margin: 0; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>