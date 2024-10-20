import { defineStore } from 'pinia';
import { Supplier } from '@/Models/Supplier';
import api from '../api';

export const useSupplierStore = defineStore('supplier', {
  state: () => ({
    suppliers: [] as Supplier[],
    loading: false,
    error: null as string | null,
  }),
  getters: {
    getSuppliers: (state) => state.suppliers,
    getLoading: (state) => state.loading,
    getError: (state) => state.error,
  },
  actions: {
    async fetchSuppliers() {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get('/suppliers');
        this.suppliers = response.data.data.map((item: any) => new Supplier(item));
      } catch (error: any) {
        console.error('Error fetching suppliers:', error);
        this.error = error.message || 'An error occurred while fetching suppliers';
      } finally {
        this.loading = false;
      }
    },
    async createSupplier(supplierData: Partial<Supplier>) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/suppliers', supplierData);
        const newSupplier = new Supplier(response.data.data);
        this.suppliers.push(newSupplier);
        return newSupplier;
      } catch (error: any) {
        console.error('Error creating supplier:', error);
        this.error = error.message || 'An error occurred while creating the supplier';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async updateSupplier(supplierId: number, supplierData: Partial<Supplier>) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.put(`/suppliers/${supplierId}`, supplierData);
        const updatedSupplier = new Supplier(response.data.data);
        const index = this.suppliers.findIndex(s => s.id === supplierId);
        if (index !== -1) {
          this.suppliers[index] = updatedSupplier;
        }
        return updatedSupplier;
      } catch (error: any) {
        console.error('Error updating supplier:', error);
        this.error = error.message || 'An error occurred while updating the supplier';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async deleteSupplier(supplierId: number) {
      this.loading = true;
      this.error = null;
      try {
        await api.delete(`/suppliers/${supplierId}`);
        this.suppliers = this.suppliers.filter(s => s.id !== supplierId);
      } catch (error: any) {
        console.error('Error deleting supplier:', error);
        this.error = error.message || 'An error occurred while deleting the supplier';
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});
