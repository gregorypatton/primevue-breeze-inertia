<template>
    <div class="mt-4">
        <h3 class="text-lg font-semibold mb-2">Select Parts</h3>
        <DataTable
            :value="supplierParts"
            v-model:selection="selectedParts"
            :paginator="true"
            :rows="10"
            dataKey="id"
            :filters="filters"
            filterDisplay="menu"
            :loading="store.isLoadingParts"
            :globalFilterFields="['part_number', 'description']"
            @row-select="onRowSelect"
            @row-unselect="onRowUnselect"
        >
            <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
            <Column field="part_number" header="Part Number" :sortable="true"></Column>
            <Column field="description" header="Description" :sortable="true"></Column>
            <Column field="unit_cost" header="Unit Cost" :sortable="true">
                <template #body="slotProps">
                    {{ formatCurrency(slotProps.data.unit_cost) }}
                </template>
            </Column>
            <Column field="quantity" header="Quantity">
                <template #body="slotProps">
                    <InputNumber v-model="slotProps.data.quantity" :min="0" @input="updatePartQuantity(slotProps.data)" showButtons buttonLayout="horizontal" incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus" />
                </template>
            </Column>
            <Column field="total_cost" header="Total Cost">
                <template #body="slotProps">
                    {{ formatCurrency(slotProps.data.total_cost) }}
                </template>
            </Column>
        </DataTable>
        <div class="mt-4 text-right">
            <h4 class="text-lg font-semibold">Total Order Cost: {{ formatCurrency(store.total) }}</h4>
        </div>
        <div v-if="store.debugLog" class="mt-4 p-4 bg-gray-100 rounded">
            <h4 class="font-bold mb-2">Debug Log:</h4>
            <pre class="whitespace-pre-wrap">{{ store.debugLog }}</pre>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { usePurchaseOrderStore } from '@/Stores/purchaseOrderStore';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';

const store = usePurchaseOrderStore();

const filters = ref({
    global: { value: null, matchMode: 'contains' },
    part_number: { value: null, matchMode: 'startsWith' },
    description: { value: null, matchMode: 'contains' },
});

const supplierParts = computed(() => store.supplierParts);
const selectedParts = computed({
    get: () => store.selectedParts,
    set: (value) => store.updateSelectedParts(value)
});

const calculateTotalCost = (part) => {
    return part.unit_cost * part.quantity;
};

const updatePartQuantity = (part) => {
    part.total_cost = calculateTotalCost(part);
    store.log(`Updated part quantity: ${JSON.stringify(part)}`);
    store.updateSelectedParts(selectedParts.value);
};

const onRowSelect = (event) => {
    const selectedPart = event.data;
    store.log(`Part selected: ${JSON.stringify(selectedPart)}`);
    if (selectedPart.quantity === 0) {
        selectedPart.quantity = 1;
        selectedPart.total_cost = calculateTotalCost(selectedPart);
    }
    store.updateSelectedParts(selectedParts.value);
};

const onRowUnselect = (event) => {
    const unselectedPart = event.data;
    store.log(`Part unselected: ${JSON.stringify(unselectedPart)}`);
    unselectedPart.quantity = 0;
    unselectedPart.total_cost = 0;
    store.updateSelectedParts(selectedParts.value);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};

onMounted(() => {
    store.log('PartSelector component mounted');
    console.log('Initial supplier parts:', store.supplierParts);
});

watch(() => store.supplierParts, (newSupplierParts) => {
    console.log('Supplier parts updated in PartSelector:', newSupplierParts);
    store.log(`Supplier parts updated in PartSelector: ${JSON.stringify(newSupplierParts)}`);
}, { deep: true });
</script>
