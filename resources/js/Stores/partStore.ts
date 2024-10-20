import { defineStore } from 'pinia';
import { ref } from 'vue';
import { Part } from '../Models/Part';
import axios, { AxiosError } from 'axios';

export const usePartStore = defineStore('part', () => {
  const parts = ref<Part[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  async function fetchPartsBySupplier(supplierId: number) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/parts', {
        params: { filter: { supplier_id: supplierId } }
      });
      parts.value = response.data.data.map((item: any) => new Part(item));
      if (parts.value.length === 0) {
        error.value = 'No parts found for this supplier';
      }
    } catch (err) {
      console.error('Failed to fetch parts:', err);
      if (axios.isAxiosError(err)) {
        const axiosError = err as AxiosError;
        if (axiosError.response) {
          error.value = `Failed to fetch parts: ${axiosError.response.status} ${axiosError.response.statusText}`;
        } else if (axiosError.request) {
          error.value = 'Failed to fetch parts: No response received from the server';
        } else {
          error.value = `Failed to fetch parts: ${axiosError.message}`;
        }
      } else {
        error.value = 'Failed to fetch parts: An unexpected error occurred';
      }
    } finally {
      loading.value = false;
    }
  }

  async function createPart(partData: Partial<Part['$attributes']>) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/parts', partData);
      const newPart = new Part(response.data.data);
      parts.value.push(newPart);
      return newPart;
    } catch (err) {
      console.error('Failed to create part:', err);
      error.value = 'Failed to create part';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function updatePart(id: number, partData: Partial<Part['$attributes']>) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.patch(`/api/parts/${id}`, partData);
      const updatedPart = new Part(response.data.data);
      const index = parts.value.findIndex(p => (p.$attributes as any).id === id);
      if (index !== -1) {
        parts.value[index] = updatedPart;
      }
      return updatedPart;
    } catch (err) {
      console.error('Failed to update part:', err);
      error.value = 'Failed to update part';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function deletePart(id: number) {
    loading.value = true;
    error.value = null;
    try {
      await axios.delete(`/api/parts/${id}`);
      parts.value = parts.value.filter(p => (p.$attributes as any).id !== id);
    } catch (err) {
      console.error('Failed to delete part:', err);
      error.value = 'Failed to delete part';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  return {
    parts,
    loading,
    error,
    fetchPartsBySupplier,
    createPart,
    updatePart,
    deletePart,
  };
});
