<template>
  <form @submit.prevent="submitPurchaseOrder">
    <div class="mb-4">
      <label class="block text-gray-700 text-sm font-bold mb-2" for="supplier">
        Supplier
      </label>
      <SupplierSelection @supplier-selected="onSupplierSelected" />
    </div>

    <AddressSelector
      v-model="selectedBillToLocation"
      :locations="locationsList"
      type="billTo"
      label="Bill To Location"
      :selected-address="addresses.billTo"
      @address-selected="onAddressSelected('billTo', $event)"
    />

    <AddressSelector
      v-model="selectedShipToLocation"
      :locations="locationsList"
      type="shipTo"
      label="Ship To Location"
      :selected-address="addresses.shipTo"
      @address-selected="onAddressSelected('shipTo', $event)"
    />

    <div class="mb-4">
      <label class="block text-gray-700 text-sm font-bold mb-2" for="parts">
        Parts
      </label>
      <PartSelector :supplier="supplier" @parts-selected="onPartsSelected" />
    </div>

    <div class="mb-4">
      <h3 class="text-lg font-semibold mb-2">Selected Parts</h3>
      <DataTable :value="purchaseOrderParts" responsiveLayout="scroll">
        <Column field="part.part_number" header="Part Number"></Column>
        <Column field="part.description" header="Description"></Column>
        <Column field="quantity_ordered" header="Quantity">
          <template #body="slotProps">
            <InputNumber v-model="slotProps.data.quantity_ordered" @input="updatePartTotalCost(slotProps.data)" />
          </template>
        </Column>
        <Column field="unit_cost" header="Unit Cost"></Column>
        <Column field="total_cost" header="Total Cost"></Column>
      </DataTable>
    </div>

    <div class="mb-4">
      <h3 class="text-lg font-semibold mb-2">Total</h3>
      <p>Subtotal: ${{ subtotal.toFixed(2) }}</p>
      <p>Tax ({{ (taxRate * 100).toFixed(2) }}%): ${{ taxAmount.toFixed(2) }}</p>
      <p class="font-bold">Total: ${{ total.toFixed(2) }}</p>
    </div>

    <div class="flex items-center justify-between">
      <Button label="Create Purchase Order" type="submit" />
    </div>
  </form>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { usePurchaseOrderStore } from '../../Stores/purchaseOrderStore';
import { storeToRefs } from 'pinia';
import { PurchaseOrder } from '../../Models/PurchaseOrder';
import { PurchaseOrderPart } from '../../Models/PurchaseOrderPart';
import { Supplier } from '../../Models/Supplier';
import { Location } from '../../Models/Location';
import { AddressDTO } from '../../Interfaces/AddressDTO';
import { Part } from '../../Models/Part';
import SupplierSelection from './SupplierSelection.vue';
import PartSelector from './PartSelector.vue';
import AddressSelector from './AddressSelector.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';

const purchaseOrderStore = usePurchaseOrderStore();
const {
  purchaseOrder,
  purchaseOrderParts,
  supplier,
  locations,
  addresses,
} = storeToRefs(purchaseOrderStore);

const selectedBillToLocation = ref<Location | null>(null);
const selectedShipToLocation = ref<Location | null>(null);
const taxRate = ref(0.08);

const subtotal = computed(() => purchaseOrderStore.subtotal);
const taxAmount = computed(() => purchaseOrderStore.taxAmount);
const total = computed(() => purchaseOrderStore.total);

const locationsList = computed(() => {
  return Object.values(locations.value).filter((location): location is Location => location !== null);
});

const onSupplierSelected = async (selectedSupplier: Supplier) => {
  purchaseOrderStore.setSupplier(selectedSupplier);
  const supplierLocations = await selectedSupplier.locations().get();
  purchaseOrderStore.setLocation('billTo', supplierLocations[0] || null);
  purchaseOrderStore.setLocation('shipFrom', supplierLocations[1] || null);
  purchaseOrderStore.setLocation('shipTo', supplierLocations[2] || null);
};

const onAddressSelected = (addressType: 'billTo' | 'shipTo', address: AddressDTO) => {
  purchaseOrderStore.setAddress(addressType, address);
};

const onPartsSelected = (selectedParts: Part[]) => {
  purchaseOrderStore.updatePurchaseOrderParts(selectedParts);
};

const updatePartTotalCost = (part: PurchaseOrderPart) => {
  purchaseOrderStore.updatePurchaseOrderPart(part);
  purchaseOrderStore.calculateTotals();
};

const submitPurchaseOrder = async () => {
  await purchaseOrderStore.submitPurchaseOrder();
  purchaseOrderStore.resetStore();
};

onMounted(async () => {
  if (!purchaseOrder.value) {
    purchaseOrderStore.purchaseOrder = new PurchaseOrder();
  }
});
</script>
