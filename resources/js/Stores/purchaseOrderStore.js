import { defineStore } from 'pinia';
import { Supplier } from '@/Models/Supplier';
import { Location } from '@/Models/Location';
import { Part } from '@/Models/Part';

export const usePurchaseOrderStore = defineStore('purchaseOrder', {
  state: () => ({
    supplier: null,
    supplierParts: [],
    selectedParts: [],
    billToLocation: null,
    shipToLocation: null,
    shipFromLocation: null,
    returnToLocation: null,
    shipFromAddressIndex: 0,
    taxRate: 0.08,
    debugLog: '',
    isLoadingParts: false,
    currentPage: 1,
    totalPages: 1,
  }),

  getters: {
    subtotal: (state) => {
      return state.selectedParts.reduce((total, part) => {
        return total + (part.quantity * part.unit_cost);
      }, 0);
    },
    taxAmount: (state) => state.subtotal * state.taxRate,
    total: (state) => state.subtotal + state.taxAmount,
    areSupplierPartsLoaded: (state) => state.supplierParts.length > 0 && !state.isLoadingParts,
  },

  actions: {
    log(message) {
      console.log(message);
      this.debugLog += message + '\n';
    },

    async setSupplier(supplierData) {
      this.log(`Setting supplier: ${JSON.stringify(supplierData)}`);
      this.supplier = supplierData.supplier;
      this.shipFromAddressIndex = supplierData.shipFromAddressIndex;
      await this.fetchSupplierParts();
    },

    setSupplierParts(parts) {
      this.log(`Setting supplier parts: ${parts.length} parts`);
      this.supplierParts = parts.map(part => ({
        ...part,
        quantity: 0,
        total_cost: 0,
      }));
    },

    async fetchSupplierParts(page = 1) {
      if (!this.supplier) {
        this.log('No supplier set, cannot fetch parts');
        return;
      }

      this.isLoadingParts = true;
      try {
        this.log(`Fetching parts for supplier ID: ${this.supplier.id}, page: ${page}`);
        const response = await Supplier.$relation('parts')
          .for(this.supplier.id)
          .paginate(page, 50);

        this.log(`Supplier parts response: ${JSON.stringify(response)}`);

        if (response.data && response.data.length > 0) {
          if (page === 1) {
            this.setSupplierParts(response.data);
          } else {
            this.supplierParts = [...this.supplierParts, ...response.data.map(part => ({
              ...part,
              quantity: 0,
              total_cost: 0,
            }))];
          }
          this.currentPage = response.meta.current_page;
          this.totalPages = response.meta.last_page;
        } else {
          this.log('No parts found for this supplier');
          if (page === 1) {
            this.supplierParts = [];
          }
        }
      } catch (error) {
        this.log(`Error fetching supplier parts: ${error.message}`);
        console.error('Error fetching supplier parts:', error);
        throw error;
      } finally {
        this.isLoadingParts = false;
      }
    },

    async loadMoreParts() {
      if (this.currentPage < this.totalPages) {
        await this.fetchSupplierParts(this.currentPage + 1);
      }
    },

    updateSelectedParts(parts) {
      this.log(`Updating selected parts: ${JSON.stringify(parts)}`);
      this.selectedParts = parts;
    },

    reset() {
      this.log('Resetting store');
      this.supplier = null;
      this.supplierParts = [];
      this.selectedParts = [];
      this.shipFromLocation = null;
      this.returnToLocation = null;
      this.debugLog = '';
      this.isLoadingParts = false;
      this.currentPage = 1;
      this.totalPages = 1;
    },

    // ... (keep other existing methods)
  },
});
