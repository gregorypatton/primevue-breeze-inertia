<template>
  <div class="bg-surface-0 dark:bg-surface-950 px-6 py-8 md:px-12 lg:px-20 mt-4">
    <div class="flex items-start flex-col lg:justify-between lg:flex-row">
      <div>
        <div class="font-medium text-3xl text-surface-900 dark:text-surface-0">Supplier Parts</div>
        <div class="flex items-center text-surface-700 dark:text-surface-100 flex-wrap">
          <div class="mr-8 flex items-center mt-4">
            <i class="pi pi-box mr-2" />
            <span>{{ purchaseOrderStore.purchaseOrderParts.length }} Associated Parts</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <h3 class="text-lg font-semibold mb-2">Select Parts</h3>
    <DataTable
      :value="purchaseOrderStore.purchaseOrderParts"
      :paginator="true"
      :rows="10"
      dataKey="id"
      :filters="filters"
      filterDisplay="menu"
      :loading="loading"
      :globalFilterFields="['part.part_number', 'part.description']"
      :rowClass="rowClass"
    >
      <Column field="part.part_number" header="Part Number" :sortable="true"></Column>
      <Column field="part.description" header="Description" :sortable="true"></Column>
      <Column field="unit_cost" header="Unit Cost" :sortable="true">
        <template #body="slotProps">
          {{ formatCurrency(slotProps.data.unit_cost) }}
        </template>
      </Column>
      <Column field="min_order_qty" header="Min Order Qty" :sortable="true">
        <template #body="slotProps">
          {{ getMinOrderQuantity(slotProps.data.part) }}
        </template>
      </Column>
      <Column field="quantity" header="Quantity">
        <template #body="slotProps">
          <InputNumber
            v-model="slotProps.data.quantity"
            :min="0"
            @input="updatePartQuantity(slotProps.data)"
            @blur="onBlur(slotProps.data)"
            showButtons
            buttonLayout="horizontal"
            incrementButtonIcon="pi pi-plus"
            decrementButtonIcon="pi pi-minus"
          />
        </template>
      </Column>
      <Column field="total_cost" header="Line Cost" :sortable="true">
        <template #body="slotProps">
          {{ formatCurrency(calculateTotalCost(slotProps.data)) }}
        </template>
      </Column>
      <template #empty>
        No parts found.
      </template>
    </DataTable>
    <div class="mt-4 text-right">
      <h4 class="text-lg font-semibold">Total Order Cost: {{ formatCurrency(totalOrderCost) }}</h4>
    </div>
    <div v-if="error" class="mt-4 p-4 bg-red-100 text-red-700 rounded">
      {{ error }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { usePartStore } from '../../Stores/partStore';
import { usePurchaseOrderStore } from '../../Stores/purchaseOrderStore';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputNumber from 'primevue/inputnumber';

const partStore = usePartStore();
const purchaseOrderStore = usePurchaseOrderStore();

const filters = ref({
  global: { value: null, matchMode: 'contains' },
  'part.part_number': { value: null, matchMode: 'startsWith' },
  'part.description': { value: null, matchMode: 'contains' },
});

const loading = computed(() => partStore.loading);
const error = computed(() => partStore.error);

const updatePartQuantity = (purchaseOrderPart: any): void => {
  purchaseOrderPart.unit_cost = getUnitCost(purchaseOrderPart.part);
  purchaseOrderStore.updatePurchaseOrderPart(purchaseOrderPart);
};

const onBlur = (purchaseOrderPart: any): void => {
  purchaseOrderStore.sortPurchaseOrderParts();
};

const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};

const getUnitCost = (part: any): number => {
  const replenishmentData = part.replenishment_data;
  if (replenishmentData && replenishmentData.purchaseTerms && replenishmentData.purchaseTerms.length > 0) {
    return parseFloat(replenishmentData.purchaseTerms[0].cost_per_part);
  }
  return 0;
};

const getMinOrderQuantity = (part: any): number => {
  const replenishmentData = part.replenishment_data;
  if (replenishmentData && replenishmentData.purchaseTerms && replenishmentData.purchaseTerms.length > 0) {
    return replenishmentData.purchaseTerms[0].minimum_quantity || 0;
  }
  return 0;
};

const calculateTotalCost = (purchaseOrderPart: any): number => {
  return purchaseOrderPart.quantity * purchaseOrderPart.unit_cost;
};

const totalOrderCost = computed((): number => {
  return purchaseOrderStore.purchaseOrderParts.reduce((total, pop) => total + calculateTotalCost(pop), 0);
});

const maxTotalCost = computed(() => {
  return Math.max(...purchaseOrderStore.purchaseOrderParts.map(pop => calculateTotalCost(pop)));
});

const rowClass = (data: any) => {
  const totalCost = calculateTotalCost(data);
  if (totalCost === 0) return 'bg-green-50';
  const ratio = totalCost / maxTotalCost.value;
  if (ratio <= 0.8) return 'bg-green-50';
  if (ratio <= 0.95) return 'bg-yellow-50';
  return 'bg-orange-50';
};

onMounted(() => {
  console.log('PartSelector component mounted');
});

watch(() => partStore.parts, (newParts) => {
  console.log('Parts updated in PartSelector:', newParts);
  purchaseOrderStore.updatePurchaseOrderPartsFromParts(newParts);
}, { deep: true });
</script>
