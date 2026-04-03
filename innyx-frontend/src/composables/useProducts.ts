import { ref } from 'vue';
import axios from 'axios';

// Definindo a interface para o Produto (Clean Code)
interface Product {
    id: number;
    name: string;
    price: number;
    description: string;
    image: string | null;
}

// Interface para a resposta do Laravel (Paginação)
interface LaravelPagination {
    data: Product[];
    current_page: number;
    last_page: number;
    total: number;
}

export function useProducts() {
    const products = ref<Product[]>([]);
    const search = ref('');
    const currentPage = ref(1);
    const lastPage = ref(1);
    const isFetching = ref(false);

    /**
     * Busca os produtos no buffer operacional.
     * @param page Número da página para navegação direta.
     */
    const fetchProducts = async (page: number = 1) => {
        isFetching.value = true;
        
        try {
            // Garantimos que o axios envie o token no header caso necessário
            const token = localStorage.getItem('token');
            
            const response = await axios.get<LaravelPagination>(`http://localhost:8000/api/products`, {
                params: { 
                    page, 
                    search: search.value 
                },
                headers: token ? { Authorization: `Bearer ${token}` } : {}
            });

            // Atualização do estado reativo com os dados do backend
            products.value = response.data.data;
            currentPage.value = response.data.current_page;
            lastPage.value = response.data.last_page;

        } catch (error) {
            console.error("Innyx Core - Fetch Error:", error);
        } finally {
            // Pequeno delay para suavizar a transição do Glassmorphism
            setTimeout(() => {
                isFetching.value = false;
            }, 300);
        }
    };

    return { 
        products, 
        search, 
        currentPage, 
        lastPage, 
        isFetching, 
        fetchProducts 
    };
}