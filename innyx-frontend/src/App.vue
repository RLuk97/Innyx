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
    expiration_date: '', 
    category_id: '',     
    image: null as File | null,
    imagePreview: ''
});

// Mock de Categorias para o Select
const categories = ref([
    { id: 1, name: 'Hardware' },
    { id: 2, name: 'Software' },
    { id: 3, name: 'Serviços' }
]);

// 4. Axios Interceptor para Token JWT
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
        alert('Falha na autenticação. Verifique seu e-mail e senha.');
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
        expiration_date: product.expiration_date || '', 
        category_id: product.category_id || '',
        image: null, 
        imagePreview: product.image 
    };
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    isEditing.value = false;
    editingProductId.value = null;
    productForm.value = { name: '', price: '', description: '', expiration_date: '', category_id: '', image: null, imagePreview: '' };
};

const deleteProduct = async (id: number) => {
    if (!confirm("⚠️ Atenção: Deseja realmente excluir este registro?")) return;
    isLoading.value = true;
    try {
        await axios.delete(`http://localhost:8000/api/products/${id}`);
        alert("✅ Registro removido com sucesso!");
        await fetchProducts(currentPage.value);
    } catch (e) {
        alert("❌ Erro ao excluir o produto.");
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
    if (!productForm.value.name || !productForm.value.price || !productForm.value.expiration_date || !productForm.value.category_id) {
        return alert("⚠️ Por favor, preencha todos os campos obrigatórios.");
    }

    const today = new Date().toISOString().split('T')[0];
    if (productForm.value.expiration_date < today) {
        return alert("❌ A data de validade não pode ser anterior a hoje.");
    }

    if (Number(productForm.value.price) <= 0) {
        return alert("❌ O preço deve ser um valor positivo.");
    }

    isLoading.value = true;
    try {
        const formData = new FormData();
        formData.append('name', productForm.value.name.substring(0, 50)); 
        formData.append('description', productForm.value.description.substring(0, 200)); 
        formData.append('price', String(productForm.value.price));
        formData.append('expiration_date', productForm.value.expiration_date);
        formData.append('category_id', String(productForm.value.category_id));
        
        if (productForm.value.image) formData.append('image', productForm.value.image);

        if (isEditing.value && editingProductId.value) {
            formData.append('_method', 'PUT');
            await axios.post(`http://localhost:8000/api/products/${editingProductId.value}`, formData);
            alert("✅ Alterações salvas com sucesso!");
        } else {
            await axios.post('http://localhost:8000/api/products', formData);
            alert("✅ Produto cadastrado com sucesso!");
        }
        await fetchProducts(1);
        closeModal();
    } catch (e) {
        console.error(e);
        alert('❌ Erro de conexão com o servidor. Verifique os dados.');
    } finally {
        isLoading.value = false;
    }
};

const formatCurrency = (v: any) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v || 0);

onMounted(() => { if (token.value) fetchProducts(1); });
</script>

<template>
  <div class="min-h-screen bg-[#f1f5f9] font-sans text-slate-600 selection:bg-[#7c3aed]/10 overflow-x-hidden relative">
    
    <div v-if="!token" class="flex min-h-screen items-center justify-center p-6 bg-slate-100">
      <div class="w-full max-w-md bg-white p-10 rounded-[2.5rem] shadow-xl border border-white text-center">
        <img src="https://innyx.com/wp-content/uploads/2025/06/logo-innyx_branco.webp" class="h-8 mx-auto mb-10 invert">
        <h2 class="text-xs font-black uppercase tracking-[0.3em] text-[#7c3aed] mb-8">Acesso Restrito</h2>
        <form @submit.prevent="handleLogin" class="space-y-4">
          <input v-model="email" type="email" placeholder="E-mail" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 outline-none focus:border-[#7c3aed]">
          <input v-model="password" type="password" placeholder="Senha" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 outline-none focus:border-[#7c3aed]">
          <button class="w-full bg-[#7c3aed] text-white font-bold py-4 rounded-2xl hover:bg-[#6d28d9] transition-all uppercase text-[11px] tracking-widest cursor-pointer">
            {{ isLoading ? 'Entrando...' : 'Acessar Sistema' }}
          </button>
        </form>
      </div>
    </div>

    <div v-else>
      <header class="fixed top-0 inset-x-0 z-50 bg-white/70 backdrop-blur-xl border-b border-slate-200 min-h-[5rem] flex items-center px-4 sm:px-8 justify-between flex-wrap gap-4 py-4 sm:py-0">
          <img src="https://innyx.com/wp-content/uploads/2025/06/logo-innyx_branco.webp" class="h-8 sm:h-10 w-auto invert">
          <div class="flex items-center gap-3 sm:gap-6 w-full sm:w-auto justify-between sm:justify-end">
            <div class="relative flex-1 sm:flex-none">
              <input v-model="search" @input="fetchProducts(1)" type="text" placeholder="Filtrar..." class="bg-slate-100/50 border border-slate-200 rounded-full px-10 py-2 text-xs w-full sm:w-64 outline-none focus:bg-white transition-all">
              <span class="absolute left-4 top-2.5 opacity-30">🔍</span>
            </div>
            <button @click="handleLogout" class="text-xs font-bold text-red-500 cursor-pointer p-2">Sair</button>
          </div>
      </header>

      <main class="pt-32 sm:pt-40 pb-20 px-4 sm:px-8 max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10 gap-6">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.4em] text-[#7c3aed]">Gestão de Produtos</p>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight italic">Inventário de Ativos</h2>
            </div>
            <button @click="openModal" class="w-full sm:w-auto bg-[#7c3aed] text-white px-8 py-3 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-[#6d28d9] cursor-pointer shadow-lg shadow-[#7c3aed]/20 transition-all">
                + Novo Produto
            </button>
        </div>

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left min-w-[700px]">
              <thead>
                <tr class="text-[10px] font-black uppercase tracking-widest text-slate-400 bg-slate-50/50">
                  <th class="px-10 py-5">Especificação</th>
                  <th class="px-10 py-5 text-center">Valor Estimado</th>
                  <th class="px-10 py-5 text-center">Ações</th>
                </tr>
              </thead>
              <tbody class="bg-white">
                <tr v-for="product in products" :key="product.id" class="group hover:bg-slate-50/80 transition-all duration-300">
                  <td class="px-10 py-5">
                    <div class="flex items-center gap-5">
                      <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex-none overflow-hidden shadow-sm">
                          <img 
                            :src="product.image && product.image.startsWith('http') ? product.image : `http://localhost:8000/storage/${product.image}`" 
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-125"
                          >
                      </div>
                      <div>
                        <div class="font-bold text-slate-800 text-sm tracking-tight transition-colors duration-300 group-hover:text-[#7c3aed]">
                            {{ product.name }}
                        </div>
                        <div class="text-[11px] text-slate-400 italic line-clamp-1 max-w-sm">{{ product.description }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-10 py-5 text-center font-bold text-slate-700">{{ formatCurrency(product.price) }}</td>
                  <td class="px-10 py-5 text-center">
                    <div class="flex justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                      <button @click="openEditModal(product)" class="p-2 text-slate-400 hover:text-[#7c3aed] cursor-pointer font-bold text-[10px] uppercase">Editar</button>
                      <button @click="deleteProduct(product.id)" class="p-2 text-slate-400 hover:text-red-500 cursor-pointer font-bold text-[10px] uppercase">Excluir</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <footer class="p-8 flex flex-col items-center justify-center gap-4 bg-slate-50/30">
            <div class="flex items-center gap-3">
                <button @click="fetchProducts(currentPage - 1)" :disabled="currentPage === 1" class="w-8 h-8 rounded-lg border border-slate-200 bg-white flex items-center justify-center disabled:opacity-30 hover:bg-slate-50 transition-all cursor-pointer shadow-sm">
                  <span class="text-slate-600 text-xs">←</span>
                </button>
                <div class="flex gap-2">
                    <button v-for="page in lastPage" :key="page" @click="fetchProducts(page)" :class="['w-8 h-8 rounded-lg font-bold text-xs cursor-pointer transition-all border', currentPage === page ? 'bg-[#7c3aed] border-[#7c3aed] text-white shadow-md' : 'bg-white border-slate-200 text-slate-500 hover:border-[#7c3aed]']">{{ page }}</button>
                </div>
                <button @click="fetchProducts(currentPage + 1)" :disabled="currentPage === lastPage" class="w-8 h-8 rounded-lg border border-slate-200 bg-white flex items-center justify-center disabled:opacity-30 hover:bg-slate-50 transition-all shadow-sm cursor-pointer">
                  <span class="text-slate-600 text-xs">→</span>
                </button>
            </div>
          </footer>
        </div>
      </main>

      <Transition name="fade">
        <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-slate-900/20 backdrop-blur-sm">
          <div @click="closeModal" class="absolute inset-0"></div>
          <div class="relative w-full max-w-xl bg-white rounded-[2.5rem] shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
            <div class="p-6 sm:p-8 border-b flex justify-between items-center bg-slate-50/30 sticky top-0 z-10 backdrop-blur-md">
              <h3 class="text-xl font-bold text-slate-800 italic">{{ isEditing ? 'Editar Registro' : 'Cadastrar Registro' }}</h3>
              <button @click="closeModal" class="text-2xl text-slate-300 hover:text-red-500 cursor-pointer">×</button>
            </div>
            <form @submit.prevent="submitForm" class="p-6 sm:p-8 space-y-5">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                  <label class="text-[10px] font-bold text-slate-400 uppercase">Nome (Máx 50)</label>
                  <input v-model="productForm.name" type="text" maxlength="50" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-[#7c3aed]">
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold text-slate-400 uppercase">Preço (R$)</label>
                  <input v-model="productForm.price" type="number" step="0.01" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-[#7c3aed]">
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                  <label class="text-[10px] font-bold text-slate-400 uppercase">Data de Validade</label>
                  <input v-model="productForm.expiration_date" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-[#7c3aed]">
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold text-slate-400 uppercase">Categoria</label>
                  <select v-model="productForm.category_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-[#7c3aed]">
                    <option value="" disabled>Selecione...</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>
                </div>
              </div>

              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase">Descrição (Máx 200)</label>
                <textarea v-model="productForm.description" maxlength="200" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none resize-none focus:border-[#7c3aed]"></textarea>
              </div>

              <div class="relative group/upload">
                <label for="fileUpload" class="flex items-center gap-6 p-4 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 hover:border-[#7c3aed] transition-all cursor-pointer">
                    <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 flex-none overflow-hidden flex items-center justify-center relative">
                        <img v-if="productForm.imagePreview" :src="productForm.imagePreview" class="w-full h-full object-cover">
                        <span v-else class="text-xl">📷</span>
                    </div>
                    <div class="flex-1 text-left">
                        <p class="text-xs font-bold text-slate-700 italic">Foto do Produto</p>
                        <p class="text-[10px] text-slate-400 truncate">{{ productForm.image ? productForm.image.name : 'Selecione JPG ou PNG' }}</p>
                    </div>
                </label>
                <input type="file" @change="handleImageUpload" class="hidden" id="fileUpload">
              </div>

              <button class="w-full bg-[#7c3aed] text-white font-bold py-4 rounded-2xl hover:bg-[#6d28d9] transition-all uppercase text-[11px] tracking-widest cursor-pointer shadow-lg shadow-[#7c3aed]/20">
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
/* Ajuste fino para scroll lateral suave */
::-webkit-scrollbar { height: 4px; width: 4px; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>