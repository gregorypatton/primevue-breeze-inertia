import { defineStore } from 'pinia';
import { PurchaseOrder } from '../Models/PurchaseOrder';
import { PurchaseOrderPart } from '../Models/PurchaseOrderPart';
import { Supplier } from '../Models/Supplier';
import { Location } from '../Models/Location';
import { AddressDTO } from '../Interfaces/AddressDTO';
import { Part } from '../Models/Part';
import api from '../api';

export const usePurchaseOrderStore = defineStore('purchaseOrder', {
  state: () => ({
    purchaseOrder: null as PurchaseOrder | null,
    purchaseOrderParts: [] as PurchaseOrderPart[],
    supplier: null as Supplier | null,
    locations: {
      billTo: null as Location | null,
      shipFrom: null as Location | null,
      shipTo: null as Location | null,
    },
    addresses: {
      billTo: null as AddressDTO | null,
      shipFrom: null as AddressDTO | null,
      shipTo: null as AddressDTO | null,
    },
    loading: false,
    error: null as string | null,
  }),

  getters: {
    subtotal: (state) => {
      return state.purchaseOrderParts.reduce((total, part) => total + (part.$attributes.total_cost || 0), 0);
    },
    taxAmount: (state) => {
      // Assuming a fixed tax rate of 10% for this example
      const taxRate = 0.1;
      return state.purchaseOrderParts.reduce((total, part) => total + (part.$attributes.total_cost || 0), 0) * taxRate;
    },
    total: (state) => {
      const subtotal = state.purchaseOrderParts.reduce((total, part) => total + (part.$attributes.total_cost || 0), 0);
      const taxRate = 0.1;
      return subtotal + (subtotal * taxRate);
    },
  },

  actions: {
    setSupplier(supplier: Supplier) {
      this.supplier = supplier;
    },

    setLocation(locationType: 'billTo' | 'shipFrom' | 'shipTo', location: Location) {
      this.locations[locationType] = location;
    },

    setAddress(addressType: 'billTo' | 'shipFrom' | 'shipTo', address: AddressDTO) {
      this.addresses[addressType] = address;
    },

    updatePurchaseOrderParts(parts: Part[]) {
      this.purchaseOrderParts = parts.map(part => new PurchaseOrderPart({
        purchase_order_id: this.purchaseOrder?.$attributes.id,
        part_id: part.$attributes.id,
        quantity_ordered: 0,
        unit_cost: 0,
        total_cost: 0,
        quantity_invoiced: 0,
        quantity_received: 0,
        status: null,
        notes: null,
        part: part,
      }));
    },

    updatePurchaseOrderPart(updatedPart: PurchaseOrderPart) {
      const index = this.purchaseOrderParts.findIndex(p => p.$attributes.id === updatedPart.$attributes.id);
      if (index !== -1) {
        this.purchaseOrderParts[index] = updatedPart;
      }
    },

    calculateTotals() {
      this.purchaseOrderParts.forEach(part => {
        part.$attributes.total_cost = part.$attributes.quantity_ordered * (part.$attributes.unit_cost || 0);
      });
    },

    async submitPurchaseOrder() {
      this.loading = true;
      this.error = null;
      try {
        if (!this.purchaseOrder) {
          throw new Error('Purchase order is not initialized');
        }

        const orderData = {
          ...this.purchaseOrder.$attributes,
          supplier_id: this.supplier?.$attributes.id,
          bill_to_location_id: this.locations.billTo?.$attributes.id,
          ship_from_location_id: this.locations.shipFrom?.$attributes.id,
          ship_to_location_id: this.locations.shipTo?.$attributes.id,
          bill_to_address: this.addresses.billTo,
          ship_from_address: this.addresses.shipFrom,
          ship_to_address: this.addresses.shipTo,
          total_cost: this.total,
          status: 'submitted',
          purchase_order_parts: this.purchaseOrderParts.map(part => part.$attributes),
        };

        const response = await api.post('/purchase-orders', orderData);
        const newOrder = new PurchaseOrder(response.data);
        this.purchaseOrder = newOrder;
        return newOrder;
      } catch (error: any) {
        console.error('Error submitting purchase order:', error);
        this.error = error.message || 'An error occurred while submitting the purchase order';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    resetStore() {
      this.purchaseOrder = null;
      this.purchaseOrderParts = [];
      this.supplier = null;
      this.locations = {
        billTo: null,
        shipFrom: null,
        shipTo: null,
      };
      this.addresses = {
        billTo: null,
        shipFrom: null,
        shipTo: null,
      };
      this.loading = false;
      this.error = null;
    },
  },
});
