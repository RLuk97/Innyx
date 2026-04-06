<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useProducts } from './composables/useProducts';

const { products, search, currentPage, lastPage, isFetching, fetchProducts } = useProducts();
const token = ref(localStorage.getItem('token') || '');
const userRole = ref(localStorage.getItem('user_role') || 'admin'); 
const email = ref('');
const password = ref('');
const isLoading = ref(false);
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingProductId = ref<number | null>(null);
const isViewModalOpen = ref(false);
const selectedProduct = ref<any>(null);

const productForm = ref({
    name: '', price: '', description: '', expiration_date: '', 
    category_id: '', image: null as File | null, imagePreview: ''
});

const categories = ref([
    { id: 1, name: 'Eletrônicos' }, { id: 2, name: 'Mobiliário' },
    { id: 3, name: 'Software/Licenças' }, { id: 4, name: 'Suprimentos' }
]);

axios.interceptors.request.use((config) => {
  const activeToken = localStorage.getItem('token');
  if (activeToken) config.headers.Authorization = `Bearer ${activeToken}`;
  return config;
});

const handleLogin = async () => {
    isLoading.value = true;
    try {
        const { data } = await axios.post('http://localhost:8000/api/login', { email: email.value, password: password.value });
        token.value = data.access_token;
        userRole.value = data.user.role; 
        localStorage.setItem('token', data.access_token);
        localStorage.setItem('user_role', data.user.role);
        await fetchProducts(1);
    } catch (e) { alert('Falha na autenticação.'); } finally { isLoading.value = false; }
};

const handleLogout = () => { token.value = ''; localStorage.clear(); window.location.reload(); };

const openModal = () => { 
    isEditing.value = false; 
    productForm.value = { name: '', price: '', description: '', expiration_date: '', category_id: '', image: null, imagePreview: '' };
    isModalOpen.value = true; 
};

const closeModal = () => { isModalOpen.value = false; };

const openViewModal = (product: any) => {
    selectedProduct.value = { ...product }; 
    isViewModalOpen.value = true;
};

const openEditModal = (product: any) => {
    isEditing.value = true;
    editingProductId.value = product.id;
    productForm.value = {
        name: product.name, price: product.price, description: product.description || '',
        expiration_date: product.expiration_date || '', 
        category_id: product.category_id || (product.category ? product.category.id : ''), 
        image: null, imagePreview: product.image 
    };
    isModalOpen.value = true;
};

const deleteProduct = async (id: number) => {
    if (!confirm("⚠️ Deseja excluir este registro?")) return;
    try {
        await axios.delete(`http://localhost:8000/api/products/${id}`);
        await fetchProducts(currentPage.value);
    } catch (e) { alert("Erro ao excluir."); }
};

const submitForm = async () => {
    if (!productForm.value.name || !productForm.value.price) return alert("Preencha os campos obrigatórios.");
    
    isLoading.value = true;
    try {
        const formData = new FormData();
        formData.append('name', productForm.value.name);
        formData.append('price', String(productForm.value.price));
        formData.append('description', productForm.value.description || ''); // Garante que a descrição vá
        formData.append('expiration_date', productForm.value.expiration_date);
        formData.append('category_id', String(productForm.value.category_id));
        
        if (productForm.value.image) formData.append('image', productForm.value.image);

        if (isEditing.value) {
            formData.append('_method', 'PUT');
            await axios.post(`http://localhost:8000/api/products/${editingProductId.value}`, formData);
        } else {
            await axios.post('http://localhost:8000/api/products', formData);
        }
        await fetchProducts(1);
        closeModal();
    } catch (e) { alert('Erro ao salvar.'); } finally { isLoading.value = false; }
};

const handleImageUpload = (event: any) => {
    const file = event.target.files[0];
    if (file) {
        productForm.value.image = file;
        productForm.value.imagePreview = URL.createObjectURL(file);
    }
};

const formatCurrency = (v: any) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v || 0);

onMounted(() => { if (token.value) fetchProducts(1); });
</script>

<template>
  <div class="min-h-screen bg-[#f8fafc] font-sans text-slate-600 selection:bg-[#7c3aed]/10 overflow-x-hidden relative">
    
    <div v-if="!token" class="flex min-h-screen items-center justify-center p-6 bg-slate-100">
      <div class="w-full max-w-md bg-white/80 backdrop-blur-xl p-10 rounded-[2.5rem] shadow-2xl border border-white text-center">
        <img src="https://innyx.com/wp-content/uploads/2025/06/logo-innyx_branco.webp" class="h-8 mx-auto mb-10 invert">
        <h2 class="text-xs font-black uppercase tracking-[0.3em] text-[#7c3aed] mb-8">Acesso Restrito</h2>
        <form @submit.prevent="handleLogin" class="space-y-4">
          <input v-model="email" type="email" placeholder="E-mail" class="w-full bg-white/50 border border-slate-200 rounded-2xl px-6 py-4 outline-none focus:border-[#7c3aed] transition-all">
          <input v-model="password" type="password" placeholder="Senha" class="w-full bg-white/50 border border-slate-200 rounded-2xl px-6 py-4 outline-none focus:border-[#7c3aed] transition-all">
          <button class="w-full bg-[#7c3aed] text-white font-bold py-4 rounded-2xl hover:bg-[#6d28d9] transition-all uppercase text-[11px] tracking-widest shadow-lg shadow-[#7c3aed]/20">
            {{ isLoading ? 'Entrando...' : 'Acessar Sistema' }}
          </button>
        </form>
      </div>
    </div>

    <div v-else>
      <header class="fixed top-0 inset-x-0 z-50 bg-white/80 backdrop-blur-2xl border-b border-slate-100 min-h-[5rem] flex items-center px-8 justify-between shadow-sm">
          <img src="https://innyx.com/wp-content/uploads/2025/06/logo-innyx_branco.webp" class="h-8 w-auto invert opacity-80">
          <div class="flex items-center gap-6">
            <div class="relative group">
              <input v-model="search" @input="fetchProducts(1)" type="text" placeholder="Filtrar ativos..." class="bg-slate-100/50 border border-transparent rounded-full px-10 py-2 text-xs w-64 outline-none focus:bg-white focus:border-[#7c3aed]/20 transition-all">
              <span class="absolute left-4 top-2.5 opacity-20 group-focus-within:opacity-50 transition-opacity">🔍</span>
            </div>
            <button @click="handleLogout" class="text-[10px] font-black uppercase tracking-widest text-red-400 hover:text-red-600 transition-colors cursor-pointer">Sair</button>
          </div>
      </header>

      <main class="pt-40 pb-20 px-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-12">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.5em] text-[#7c3aed] mb-2">Inventory Management</p>
                <h2 class="text-4xl font-bold text-slate-800 tracking-tight italic">Inventário de Ativos</h2>
            </div>
            <button v-if="userRole === 'admin'" @click="openModal" class="bg-[#7c3aed] text-white px-10 py-4 rounded-2xl font-bold text-xs uppercase tracking-[0.2em] hover:bg-[#6d28d9] hover:-translate-y-1 active:translate-y-0 cursor-pointer shadow-xl shadow-[#7c3aed]/20 transition-all">
                + Novo Produto
            </button>
        </div>

        <div class="bg-white/70 backdrop-blur-md rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-white overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left min-w-[800px]">
              <thead>
                <tr class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 bg-slate-50/50">
                  <th class="px-12 py-6">Especificação Técnica</th>
                  <th class="px-12 py-6 text-center">Valor Estimado</th>
                  <th class="px-12 py-6 text-center">Ações</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="product in products" :key="product.id" class="group hover:bg-white/80 transition-all duration-500">
                  <td class="px-12 py-6">
                    <div class="flex items-center gap-6">
                      <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-white overflow-hidden shadow-sm group-hover:shadow-md transition-all">
                          <img :src="product.image && product.image.startsWith('http') ? product.image : `http://localhost:8000/storage/${product.image}`" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                      </div>
                      <div>
                        <div class="font-bold text-slate-800 text-sm tracking-tight group-hover:text-[#7c3aed] transition-colors mb-0.5">{{ product.name }}</div>
                        <div class="text-[11px] text-slate-400 italic line-clamp-1 max-w-md opacity-70 group-hover:opacity-100 transition-opacity">{{ product.description }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-12 py-6 text-center font-bold text-slate-700 text-sm">{{ formatCurrency(product.price) }}</td>
                  <td class="px-12 py-6 text-center">
                    <div class="flex justify-center gap-6">
                        <button @click="openViewModal(product)" class="text-emerald-500 font-black text-[10px] uppercase tracking-widest hover:text-emerald-700 transition-colors cursor-pointer">Ver</button>
                        <button v-if="userRole === 'admin'" @click="openEditModal(product)" class="text-blue-500 font-black text-[10px] uppercase tracking-widest hover:text-blue-700 transition-colors cursor-pointer">Editar</button>
                        <button v-if="userRole === 'admin'" @click="deleteProduct(product.id)" class="text-red-400 font-black text-[10px] uppercase tracking-widest hover:text-red-600 transition-colors cursor-pointer">Excluir</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <footer class="p-10 flex justify-center bg-slate-50/20 border-t border-white/50">
            <div class="flex items-center gap-4">
                <button @click="fetchProducts(currentPage - 1)" :disabled="currentPage === 1" class="w-10 h-10 rounded-xl border border-white bg-white/50 flex items-center justify-center disabled:opacity-20 hover:bg-white hover:shadow-md transition-all cursor-pointer shadow-sm">←</button>
                <div class="flex gap-2">
                    <button v-for="page in lastPage" :key="page" @click="fetchProducts(page)" :class="['w-10 h-10 rounded-xl font-bold text-xs cursor-pointer transition-all border', currentPage === page ? 'bg-[#7c3aed] border-[#7c3aed] text-white shadow-lg shadow-[#7c3aed]/30' : 'bg-white/50 border-white text-slate-400 hover:bg-white']">{{ page }}</button>
                </div>
                <button @click="fetchProducts(currentPage + 1)" :disabled="currentPage === lastPage" class="w-10 h-10 rounded-xl border border-white bg-white/50 flex items-center justify-center disabled:opacity-20 hover:bg-white hover:shadow-md transition-all cursor-pointer shadow-sm">→</button>
            </div>
          </footer>
        </div>
      </main>

      <Transition name="fade">
        <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/30 backdrop-blur-md">
          <div @click="closeModal" class="absolute inset-0"></div>
          <div class="relative w-full max-w-2xl bg-white/90 backdrop-blur-2xl rounded-[3rem] shadow-3xl border border-white overflow-hidden max-h-[90vh] overflow-y-auto">
            <div class="p-10 flex justify-between items-center border-b border-slate-50">
              <h3 class="text-2xl font-bold text-slate-800 italic tracking-tight">{{ isEditing ? 'Editar Registro' : 'Novo Ativo' }}</h3>
              <button @click="closeModal" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all cursor-pointer">×</button>
            </div>
            <form @submit.prevent="submitForm" class="p-10 space-y-6">
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-bold text-slate-400 uppercase ml-2">Nome do Produto</label>
                  <input v-model="productForm.name" type="text" placeholder="Ex: MacBook Pro" class="w-full bg-slate-50/50 border border-slate-100 rounded-2xl px-5 py-4 outline-none focus:bg-white focus:border-[#7c3aed] transition-all">
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-bold text-slate-400 uppercase ml-2">Preço (BRL)</label>
                  <input v-model="productForm.price" type="number" step="0.01" placeholder="0.00" class="w-full bg-slate-50/50 border border-slate-100 rounded-2xl px-5 py-4 outline-none focus:bg-white focus:border-[#7c3aed] transition-all">
                </div>
              </div>
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-bold text-slate-400 uppercase ml-2">Data de Validade</label>
                  <input v-model="productForm.expiration_date" type="date" class="w-full bg-slate-50/50 border border-slate-100 rounded-2xl px-5 py-4 outline-none focus:bg-white focus:border-[#7c3aed] transition-all">
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-bold text-slate-400 uppercase ml-2">Categoria de Ativo</label>
                  <select v-model="productForm.category_id" class="w-full bg-slate-50/50 border border-slate-100 rounded-2xl px-5 py-4 outline-none focus:bg-white focus:border-[#7c3aed] transition-all">
                    <option value="" disabled>Selecione...</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>
                </div>
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-2">Descrição Detalhada</label>
                <textarea v-model="productForm.description" rows="3" placeholder="Informações técnicas e observações..." class="w-full bg-slate-50/50 border border-slate-100 rounded-2xl px-5 py-4 outline-none focus:bg-white focus:border-[#7c3aed] transition-all resize-none"></textarea>
              </div>
              <div class="p-6 bg-slate-50/50 rounded-[2rem] border-2 border-dashed border-slate-200 text-center hover:border-[#7c3aed]/30 transition-colors">
                <input type="file" @change="handleImageUpload" class="text-xs text-slate-400 mb-4 block mx-auto">
                <img v-if="productForm.imagePreview" :src="productForm.imagePreview" class="h-32 mx-auto rounded-2xl object-cover shadow-lg border-4 border-white">
                <p v-else class="text-[10px] text-slate-400 uppercase font-bold">Arraste a foto do ativo ou clique aqui</p>
              </div>
              <button type="submit" class="w-full bg-[#7c3aed] text-white font-bold py-5 rounded-[2rem] hover:bg-[#6d28d9] shadow-2xl shadow-[#7c3aed]/30 uppercase text-xs tracking-[0.3em] transition-all">
                {{ isLoading ? 'Gravando Informações...' : 'Finalizar Cadastro' }}
              </button>
            </form>
          </div>
        </div>
      </Transition>

      <Transition name="fade">
        <div v-if="isViewModalOpen && selectedProduct" class="fixed inset-0 z-[110] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-xl">
          <div @click="isViewModalOpen = false" class="absolute inset-0"></div>
          <div class="relative w-full max-w-xl bg-white rounded-[3.5rem] shadow-4xl p-12 animate-in fade-in zoom-in duration-500 border border-white">
            <div class="text-center mb-8">
                <p class="text-[10px] font-black text-[#7c3aed] uppercase tracking-[0.5em] mb-2">Detalhes do Produto</p>
                <h3 class="text-3xl font-bold text-slate-800 italic">{{ selectedProduct.name }}</h3>
            </div>
            
            <div class="w-full h-72 rounded-[2.5rem] bg-slate-50 border border-slate-100 mb-8 overflow-hidden shadow-inner flex items-center justify-center p-4">
                <img :src="selectedProduct.image && selectedProduct.image.startsWith('http') ? selectedProduct.image : `http://localhost:8000/storage/${selectedProduct.image}`" class="max-w-full max-h-full object-contain rounded-xl shadow-sm">
            </div>

            <div class="grid grid-cols-2 gap-6 mb-8">
                <div class="bg-slate-50/50 p-6 rounded-3xl border border-white shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Preço Estimado</p>
                    <p class="text-2xl font-bold text-slate-800">{{ formatCurrency(selectedProduct.price) }}</p>
                </div>
                <div class="bg-slate-50/50 p-6 rounded-3xl border border-white shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Vencimento</p>
                    <p class="text-2xl font-bold text-slate-800">
                        {{ selectedProduct.expiration_date ? new Date(selectedProduct.expiration_date).toLocaleDateString('pt-BR') : '—' }}
                    </p>
                </div>
            </div>

            <div class="bg-slate-50/50 p-8 rounded-[2.5rem] border border-white shadow-sm mb-10">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Informações Técnicas</p>
                <p class="text-sm text-slate-600 leading-relaxed italic">
                  "{{ selectedProduct.description || 'Este ativo não possui uma descrição técnica detalhada cadastrada no sistema.' }}"
                </p>
            </div>

            <button @click="isViewModalOpen = false" class="w-full bg-slate-800 text-white font-black py-5 rounded-[2rem] hover:bg-black transition-all text-[10px] tracking-[0.4em] uppercase shadow-xl">
              Fechar Janela
            </button>
          </div>
        </div>
      </Transition>

    </div>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
body { background-color: #f8fafc; margin: 0; font-family: 'Inter', sans-serif; }
.fade-enter-active, .fade-leave-active { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: scale(0.95); }
::-webkit-scrollbar { height: 6px; width: 6px; }
::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
