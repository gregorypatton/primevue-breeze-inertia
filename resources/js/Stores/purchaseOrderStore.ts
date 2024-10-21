import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { Supplier } from '../Models/Supplier';
import { Part } from '../Models/Part';
import customHttpClient from '../utils/customHttpClient';

interface OrionResponse<T> {
  data: T[];
}

export const usePurchaseOrderStore = defineStore('purchaseOrder', () => {
  const suppliers = ref<Supplier[]>([]);
  const selectedSupplier = ref<Supplier | null>(null);
  const parts = ref<Part[]>([]);
  const loading = ref(false);
  const loadingParts = ref(false);
  const error = ref<string | null>(null);

  const supplierCount = computed(() => suppliers.value.length);
  const partsCount = computed(() => parts.value.length);

  async function fetchSuppliers(params = {}) {
    loading.value = true;
    error.value = null;
    try {
      console.log('Fetching suppliers...');
      const url = '/suppliers';
      console.log('Full URL:', customHttpClient.getUri({ url }));
      const response = await customHttpClient.get<OrionResponse<Supplier>>(url, {
        params: {
          ...params,
          include: 'parts',
          sort: '-created_at',
        },
      });
      console.log('Full API response:', response);
      if (response.data && Array.isArray(response.data.data)) {
        suppliers.value = response.data.data.map((item: any) => new Supplier(item));
        console.log('Parsed suppliers:', suppliers.value);
      } else {
        console.error('Unexpected API response structure:', response.data);
        error.value = 'Unexpected API response structure';
      }
      if (suppliers.value.length === 0) {
        console.log('No suppliers found in the response');
        error.value = 'No suppliers found';
      }
    } catch (err: any) {
      console.error('Failed to fetch suppliers:', err);
      error.value = err.message || 'Failed to fetch suppliers';
    } finally {
      loading.value = false;
    }
  }

  async function selectSupplier(supplierId: number) {
    const supplier = suppliers.value.find(s => s.$attributes.id === supplierId);
    if (supplier) {
      selectedSupplier.value = supplier;
      await fetchSupplierParts(supplierId);
    } else {
      error.value = `Selected supplier not found. ID: ${supplierId}. Available suppliers: ${suppliers.value.map(s => s.$attributes.id).join(', ')}`;
    }
  }

  async function fetchSupplierParts(supplierId: number) {
    loadingParts.value = true;
    error.value = null;
    try {
      const response = await customHttpClient.get<OrionResponse<Part>>(`/suppliers/${supplierId}/parts`, {
        params: {
          include: 'bill_of_material',
        },
      });
      parts.value = response.data.data.map((item: any) => new Part(item));
    } catch (err: any) {
      console.error('Failed to fetch supplier parts:', err);
      error.value = err.message || 'Failed to fetch supplier parts';
    } finally {
      loadingParts.value = false;
    }
  }

  function updatePartQuantity(partId: number, quantity: number) {
    const part = parts.value.find(p => p.$attributes.id === partId);
    if (part) {
      part.$attributes.quantity = quantity;
    }
  }

  function resetSelection() {
    selectedSupplier.value = null;
    parts.value = [];
  }

  async function createPurchaseOrder() {
    // Implement the logic to create a purchase order
    // This is a placeholder and should be implemented based on your backend API
    console.log('Creating purchase order...');
  }

  return {
    suppliers,
    selectedSupplier,
    parts,
    loading,
    loadingParts,
    error,
    supplierCount,
    partsCount,
    fetchSuppliers,
    selectSupplier,
    updatePartQuantity,
    resetSelection,
    createPurchaseOrder,
  };
});
