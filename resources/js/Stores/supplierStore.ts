import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { Supplier } from '../Models/Supplier';

export const useSupplierStore = defineStore('supplier', () => {
  const suppliers = ref<Supplier[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const supplierCount = computed(() => suppliers.value.length);

  async function fetchSuppliers(params: Record<string, any> = {}) {
    loading.value = true;
    error.value = null;
    try {
      const response = await Supplier.$query().with('parts').get(params);
      suppliers.value = response.data;
      console.log('Fetched suppliers:', suppliers.value);
    } catch (err: any) {
      console.error('Failed to fetch suppliers:', err);
      error.value = err.message || 'Failed to fetch suppliers';
    } finally {
      loading.value = false;
    }
  }

  return {
    suppliers,
    loading,
    error,
    supplierCount,
    fetchSuppliers,
  };
});
