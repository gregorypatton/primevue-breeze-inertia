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
            :loading="loading"
            :globalFilterFields="['part_number', 'description']"
        >
            <template #header>
                <div class="flex justify-content-end">
                    <span class="p-input-icon-left">
                        <i class="pi pi-search" />
                        <InputText v-model="filters['global'].value" placeholder="Keyword Search" />
                    </span>
                </div>
            </template>

            <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
            <Column field="part_number" header="Part Number" :sortable="true">
                <template #body="slotProps">
                    {{ slotProps.data.part_number }}
                </template>
                <template #filter="{ filterModel }">
                    <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Search by Part Number" />
                </template>
            </Column>
            <Column field="description" header="Description" :sortable="true">
                <template #body="slotProps">
                    {{ slotProps.data.description }}
                </template>
                <template #filter="{ filterModel }">
                    <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Search by Description" />
                </template>
            </Column>
            <Column field="unit_cost" header="Unit Cost" :sortable="true">
                <template #body="slotProps">
                    <InputNumber v-model="slotProps.data.unit_cost" mode="currency" currency="USD" locale="en-US" :minFractionDigits="2" readonly />
                </template>
            </Column>
            <Column field="quantity" header="Quantity">
                <template #body="slotProps">
                    <InputNumber v-model="slotProps.data.quantity" :min="0" @input="updateTotalCost(slotProps.data)" showButtons buttonLayout="horizontal" incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus" />
                </template>
            </Column>
            <Column field="total_cost" header="Total Cost">
                <template #body="slotProps">
                    <InputNumber v-model="slotProps.data.total_cost" mode="currency" currency="USD" locale="en-US" :minFractionDigits="2" readonly />
                </template>
            </Column>
        </DataTable>
        <div class="mt-4 text-right">
            <h4 class="text-lg font-semibold">Total Order Cost: {{ formatCurrency(totalOrderCost) }}</h4>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';

// Define FilterMatchMode and FilterOperator manually
const FilterMatchMode = {
    STARTS_WITH: 'startsWith',
    CONTAINS: 'contains',
    EQUALS: 'equals',
};

const FilterOperator = {
    AND: 'and',
    OR: 'or',
    EQ: '=',
};

interface PartData {
    id: number;
    part_number: string;
    description: string;
    unit_cost: number;
    quantity: number;
    total_cost: number;
    replenishment_data: {
        purchaseTerms: Array<{
            cost_per_part: number;
        }>;
    };
}

const props = defineProps({
    supplierId: {
        type: Number,
        required: true
    }
});

const supplierParts = ref<PartData[]>([]);
const selectedParts = ref<PartData[]>([]);
const loading = ref(false);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    part_number: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    description: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

watch(() => props.supplierId, (newSupplierId) => {
    if (newSupplierId) {
        loadParts();
    }
});

const loadParts = async () => {
    loading.value = true;
    try {
        const response = await axios.post('/api/parts/search', {
            filters: [
                { field: 'supplier_id', operator: FilterOperator.EQ, value: props.supplierId }
            ]
        });
        supplierParts.value = response.data.data.map((part: any) => ({
            id: part.id,
            part_number: part.part_number,
            description: part.description,
            unit_cost: part.replenishment_data.purchaseTerms[0].cost_per_part,
            quantity: 0,
            total_cost: 0,
            replenishment_data: part.replenishment_data
        }));
    } catch (error) {
        console.error('Error fetching parts:', error);
    } finally {
        loading.value = false;
    }
};

const calculateTotalCost = (part: PartData) => {
    return part.unit_cost * part.quantity;
};

const updateTotalCost = (part: PartData) => {
    part.total_cost = calculateTotalCost(part);
};

const totalOrderCost = computed(() => {
    return selectedParts.value.reduce((total, part) => total + calculateTotalCost(part), 0);
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};

const resetSelection = () => {
    selectedParts.value = [];
    supplierParts.value.forEach(part => {
        part.quantity = 0;
        part.total_cost = 0;
    });
};

defineExpose({ selectedParts, resetSelection });

onMounted(() => {
    if (props.supplierId) {
        loadParts();
    }
});
</script>
