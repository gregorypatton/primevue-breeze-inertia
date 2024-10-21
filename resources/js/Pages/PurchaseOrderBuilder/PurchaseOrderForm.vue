<template>
  <div class="purchase-order-form">
    <h1 class="text-2xl font-semibold mb-4">Create Purchase Order</h1>

    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <div v-if="loading" class="mb-4">Loading suppliers...</div>

    <div v-if="!loading && suppliers.length === 0" class="mb-4">No suppliers found.</div>

    <div v-if="suppliers.length > 0" class="supplier-selection mb-6">
      <label for="supplier-select" class="block text-sm font-medium text-gray-700 mb-2">Select Supplier:</label>
      <Select
        id="supplier-select"
        v-model="selectedSupplierId"
        :options="suppliers"
        optionLabel="$attributes.name"
        optionValue="$attributes.id"
        placeholder="Select a supplier"
        :loading="loading"
        @change="onSupplierSelect"
        class="w-full"
      />
    </div>

    <div v-if="selectedSupplier" class="supplier-details mb-6">
      <h2 class="text-xl font-semibold mb-2">Supplier Details</h2>
      <div><strong>Name:</strong> {{ selectedSupplier.$attributes.name }}</div>
      <div><strong>ID:</strong> {{ selectedSupplier.$attributes.id }}</div>
      <div><strong>Account Number:</strong> {{ selectedSupplier.$attributes.account_number }}</div>
      <div><strong>Payment Terms:</strong> {{ selectedSupplier.$attributes.payment_terms }}</div>
    </div>

    <div v-if="selectedSupplier" class="parts-table mb-6">
      <h2 class="text-xl font-semibold mb-2">Parts for {{ selectedSupplier.$attributes.name }}</h2>
      <div v-if="loadingParts">Loading parts...</div>
      <DataTable v-else :value="parts" responsive-layout="scroll" class="p-datatable-sm">
        <Column field="$attributes.part_number" header="Part Number"></Column>
        <Column field="$attributes.description" header="Description"></Column>
        <Column field="$attributes.quantity" header="Quantity">
          <template #body="slotProps">
            <InputNumber v-model="slotProps.data.$attributes.quantity" @input="(event: number | null) => updatePartQuantity(slotProps.data.$attributes.id, event)" />
          </template>
        </Column>
        <Column field="$attributes.uom" header="UOM"></Column>
        <Column field="cost" header="Cost">
          <template #body="slotProps">
            {{ formatCurrency(getCostPerPart(slotProps.data)) }}
          </template>
        </Column>
        <Column field="total_cost" header="Total Cost">
          <template #body="slotProps">
            {{ formatCurrency(calculateTotalCost(slotProps.data)) }}
          </template>
        </Column>
      </DataTable>
    </div>

    <Button label="Create Purchase Order" @click="createPurchaseOrder" :disabled="!selectedSupplier || parts.length === 0" class="w-full" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { usePurchaseOrderStore } from '../../Stores/purchaseOrderStore';
import { storeToRefs } from 'pinia';
import Select from 'primevue/select';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import { Supplier } from '../../Models/Supplier';
import { Part } from '../../Models/Part';

const purchaseOrderStore = usePurchaseOrderStore();
const { suppliers, selectedSupplier, parts, loading, loadingParts, error } = storeToRefs(purchaseOrderStore);

const selectedSupplierId = ref<number | null>(null);

onMounted(async () => {
  console.log('Component mounted, fetching suppliers...');
  try {
    await purchaseOrderStore.fetchSuppliers();
    console.log('Suppliers fetched:', safeStringify(suppliers.value));
  } catch (err) {
    console.error('Error fetching suppliers:', err);
  }
});

watch(suppliers, (newSuppliers) => {
  console.log('Suppliers updated:', safeStringify(newSuppliers));
});

const onSupplierSelect = async (event: { value: number }) => {
  if (event.value) {
    console.log('Supplier selected:', event.value);
    await purchaseOrderStore.selectSupplier(event.value);
    if (error.value) {
      console.error('Error selecting supplier:', error.value);
    }
  }
};

const updatePartQuantity = (partId: number, quantity: number | null) => {
  if (quantity !== null) {
    purchaseOrderStore.updatePartQuantity(partId, quantity);
  }
};

const createPurchaseOrder = () => {
  purchaseOrderStore.createPurchaseOrder();
};

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};

const getCostPerPart = (part: Part) => {
  return part.getUnitCost();
};

const calculateTotalCost = (part: Part) => {
  return part.calculateTotalCost();
};

// Custom JSON stringifier to handle circular references and limit depth
function safeStringify(obj: any, maxDepth = 3): string {
  const seen = new WeakSet();
  return JSON.stringify(obj, (key, value) => {
    if (typeof value === 'object' && value !== null) {
      if (seen.has(value)) {
        return '[Circular]';
      }
      seen.add(value);
      if (maxDepth === 0) {
        return '[Object]';
      }
      return Object.fromEntries(
        Object.entries(value).map(([k, v]) => [k, safeStringify(v, maxDepth - 1)])
      );
    }
    return value;
  });
}
</script>

<style scoped>
.purchase-order-form {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}
</style>
