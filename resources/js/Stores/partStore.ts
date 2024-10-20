import { defineStore } from 'pinia';
import { Part } from '@/Models/Part';
import api from '../api';

export const usePartStore = defineStore('part', {
  state: () => ({
    parts: [] as Part[],
    loading: false,
    error: null as string | null,
  }),
  getters: {
    getParts: (state) => state.parts,
    getLoading: (state) => state.loading,
    getError: (state) => state.error,
  },
  actions: {
    async fetchPartsBySupplier(supplierId: number) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/parts`, {
          params: { filter: { supplier_id: supplierId } }
        });
        this.parts = response.data.data.map((item: any) => new Part(item));
      } catch (error: any) {
        console.error('Error fetching parts:', error);
        this.error = error.message || 'An error occurred while fetching parts';
      } finally {
        this.loading = false;
      }
    },
    async fetchParts() {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get('/parts');
        this.parts = response.data.data.map((item: any) => new Part(item));
      } catch (error: any) {
        console.error('Error fetching parts:', error);
        this.error = error.message || 'An error occurred while fetching parts';
      } finally {
        this.loading = false;
      }
    },
    async createPart(partData: Partial<Part>) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/parts', partData);
        const newPart = new Part(response.data.data);
        this.parts.push(newPart);
        return newPart;
      } catch (error: any) {
        console.error('Error creating part:', error);
        this.error = error.message || 'An error occurred while creating the part';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async updatePart(partId: number, partData: Partial<Part>) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.put(`/parts/${partId}`, partData);
        const updatedPart = new Part(response.data.data);
        const index = this.parts.findIndex(p => p.id === partId);
        if (index !== -1) {
          this.parts[index] = updatedPart;
        }
        return updatedPart;
      } catch (error: any) {
        console.error('Error updating part:', error);
        this.error = error.message || 'An error occurred while updating the part';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async deletePart(partId: number) {
      this.loading = true;
      this.error = null;
      try {
        await api.delete(`/parts/${partId}`);
        this.parts = this.parts.filter(p => p.id !== partId);
      } catch (error: any) {
        console.error('Error deleting part:', error);
        this.error = error.message || 'An error occurred while deleting the part';
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});
