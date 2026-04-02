import { ref } from 'vue';
import axios from 'axios';

export function useProducts() {
    const products = ref([]);
    const search = ref('');
    const currentPage = ref(1);
    const lastPage = ref(1);
    const isFetching = ref(false);

    const fetchProducts = async (page = 1) => {
        isFetching.value = true;
        try {
            const response = await axios.get(`http://localhost:8000/api/products`, {
                params: { page, search: search.value }
            });
            products.value = response.data.data;
            currentPage.value = response.data.current_page;
            lastPage.value = response.data.last_page;
        } catch (error) {
            console.error("Fetch error:", error);
        } finally {
            isFetching.value = false;
        }
    };

    return { products, search, currentPage, lastPage, isFetching, fetchProducts };
}