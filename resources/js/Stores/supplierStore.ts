import { defineStore } from 'pinia';
import { ref } from 'vue';
import { Supplier } from '@/Models/Supplier';

export const useSupplierStore = defineStore('supplier', () => {
  const suppliers = ref<Supplier[]>([]);
  const currentSupplier = ref<Supplier | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  async function fetchSuppliers() {
    loading.value = true;
    error.value = null;
    try {
      const response = await Supplier.$query().get();
      suppliers.value = response as Supplier[];
    } catch (err) {
      console.error('Failed to fetch suppliers:', err);
      error.value = 'Failed to fetch suppliers';
    } finally {
      loading.value = false;
    }
  }

  function setCurrentSupplier(supplier: Supplier) {
    currentSupplier.value = supplier;
  }

  return {
    suppliers,
    currentSupplier,
    loading,
    error,
    fetchSuppliers,
    setCurrentSupplier,
  };
});
