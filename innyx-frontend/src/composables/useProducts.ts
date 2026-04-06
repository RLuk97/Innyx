import { ref } from 'vue';
import axios from 'axios';

interface Product {
    id: number;
    name: string;
    price: number;
    description: string;
    image: string | null;
}

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
    const fetchProducts = async (page: number = 1) => {
        isFetching.value = true;
        
        try {
            const token = localStorage.getItem('token');
            
            const response = await axios.get<LaravelPagination>(`http://localhost:8000/api/products`, {
                params: { 
                    page, 
                    search: search.value 
                },
                headers: token ? { Authorization: `Bearer ${token}` } : {}
            });
            products.value = response.data.data;
            currentPage.value = response.data.current_page;
            lastPage.value = response.data.last_page;

        } catch (error) {
            console.error("Innyx Core - Fetch Error:", error);
        } finally {
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
