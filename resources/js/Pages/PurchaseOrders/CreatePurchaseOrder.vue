<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useSupplierStore } from '@/Stores/supplierStore';
import { usePartStore } from '@/Stores/partStore';
import { useLocationStore } from '@/Stores/locationStore';
import SupplierSelection from './SupplierSelection.vue';
import PartSelector from './PartSelector.vue';
import AddressDisplay from '@/Components/AddressDisplay.vue';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import Dialog from 'primevue/dialog';
import PurchaseOrderPackingList from './PurchaseOrderPackingList.vue';
import ProgressSpinner from 'primevue/progressspinner';

const supplierStore = useSupplierStore();
const partStore = usePartStore();
const locationStore = useLocationStore();
const toast = useToast();

const showPackingList = ref(false);
const debugInfo = ref('');

const form = useForm({
    supplier_id: null,
    parts: [],
    bill_to_location_id: null,
    ship_to_location_id: null,
    bill_to_address_index: null,
    ship_from_address_index: null,
    ship_to_address_index: null
});

const onSupplierSelected = async (supplier) => {
    supplierStore.setCurrentSupplier(supplier);
    await partStore.fetchPartsBySupplier(supplier.id);
};

const canCreatePurchaseOrder = computed(() => {
    return supplierStore.currentSupplier &&
           partStore.parts.length > 0 &&
           form.bill_to_location_id &&
           form.ship_to_location_id &&
           form.ship_from_address_index !== null;
});

const createPurchaseOrder = () => {
    if (!canCreatePurchaseOrder.value) {
        debugInfo.value += `Cannot create purchase order: ${JSON.stringify({
            supplier: !!supplierStore.currentSupplier,
            parts: partStore.parts.length,
            billToLocation: !!form.bill_to_location_id,
            shipToLocation: !!form.ship_to_location_id,
            shipFromAddress: form.ship_from_address_index !== null
        })}\n`;
        return;
    }

    form.supplier_id = supplierStore.currentSupplier!.id;
    form.parts = partStore.parts.map(part => ({
        id: part.id,
        quantity: part.quantity,
        unit_cost: part.unit_cost
    }));

    debugInfo.value += `Submitting purchase order: ${JSON.stringify(form)}\n`;

    form.post(route('purchase-orders.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Purchase Order created successfully', life: 3000 });
            form.reset();
            debugInfo.value += `Purchase order created successfully\n`;
        },
        onError: (errors) => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to create Purchase Order', life: 3000 });
            debugInfo.value += `Error creating purchase order: ${JSON.stringify(errors)}\n`;
        }
    });
};

const openPackingList = () => {
    showPackingList.value = true;
};

const closePackingList = () => {
    showPackingList.value = false;
};

onMounted(async () => {
    await locationStore.fetchWarehouseLocations();
});
</script>

<template>
    <div>
        <SupplierSelection @supplier-selected="onSupplierSelected" />

        <div v-if="supplierStore.currentSupplier" class="bg-surface-0 dark:bg-surface-950 px-6 py-8 md:px-12 lg:px-20 mt-4">
            <div class="flex items-start flex-col lg:justify-between lg:flex-row">
                <div>
                    <div class="font-medium text-3xl text-surface-900 dark:text-surface-0">Supplier Parts</div>
                    <div class="flex items-center text-surface-700 dark:text-surface-100 flex-wrap">
                        <div class="mr-8 flex items-center mt-4">
                            <i class="pi pi-box mr-2" />
                            <span>{{ partStore.parts.length }} Associated Parts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-4">
            <AddressDisplay
                title="Ship From Address"
                :address="supplierStore.currentSupplier?.addresses?.shipping[form.ship_from_address_index]"
                :loading="supplierStore.loading"
            />
            <AddressDisplay
                title="Ship To Address"
                :address="locationStore.locations.find(l => l.id === form.ship_to_location_id)?.address"
                :loading="locationStore.loading"
            />
            <AddressDisplay
                title="Bill To Address"
                :address="locationStore.locations.find(l => l.id === form.bill_to_location_id)?.address"
                :loading="locationStore.loading"
            />
        </div>

        <div v-if="partStore.loading" class="flex justify-center items-center mt-4">
            <ProgressSpinner />
            <span class="ml-2">Loading parts...</span>
        </div>

        <PartSelector v-else-if="partStore.parts.length > 0" :parts="partStore.parts" />

        <div v-else-if="supplierStore.currentSupplier" class="mt-4 p-4 bg-yellow-100 text-yellow-700 rounded">
            {{ partStore.error || 'No parts found for this supplier.' }}
        </div>

        <div class="mt-4 flex justify-between">
            <Button label="Create Purchase Order" @click="createPurchaseOrder" :disabled="!canCreatePurchaseOrder" />
            <Button label="Print Preview" icon="pi pi-print" @click="openPackingList" :disabled="!canCreatePurchaseOrder" />
        </div>

        <Dialog v-model:visible="showPackingList" modal header="Purchase Order Packing List" :style="{ width: '80vw' }">
            <PurchaseOrderPackingList />
            <template #footer>
                <Button label="Print" icon="pi pi-print" @click="window.print()" autofocus />
                <Button label="Close" icon="pi pi-times" @click="closePackingList" class="p-button-text" />
            </template>
        </Dialog>

        <div v-if="debugInfo" class="mt-4 p-4 bg-gray-100 rounded">
            <h4 class="font-bold mb-2">Debug Information:</h4>
            <pre class="whitespace-pre-wrap">{{ debugInfo }}</pre>
        </div>
    </div>
    <Toast />
</template>
