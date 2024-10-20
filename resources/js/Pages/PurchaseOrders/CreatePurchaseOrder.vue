<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { usePurchaseOrderStore } from '@/Stores/purchaseOrderStore';
import SupplierSelection from './SupplierSelection.vue';
import PartSelector from './PartSelector.vue';
import AddressDisplay from '@/Components/AddressDisplay.vue';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import Dialog from 'primevue/dialog';
import PurchaseOrderPackingList from './PurchaseOrderPackingList.vue';
import ProgressSpinner from 'primevue/progressspinner';

const store = usePurchaseOrderStore();
const toast = useToast();

const loading = ref(false);
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

const onSupplierSelected = async (supplierData) => {
    loading.value = true;
    debugInfo.value += `Supplier selected: ${JSON.stringify(supplierData)}\n`;
    try {
        await store.setSupplier(supplierData);
        debugInfo.value += `Supplier set in store\n`;
    } catch (error) {
        console.error('Error setting supplier:', error);
        debugInfo.value += `Error setting supplier: ${error.message}\n`;
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load supplier data', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const canCreatePurchaseOrder = computed(() => {
    return store.supplier &&
           store.selectedParts.length > 0 &&
           store.billToLocation &&
           store.shipToLocation &&
           store.shipFromLocation;
});

const areSupplierPartsLoaded = computed(() => store.areSupplierPartsLoaded);

const createPurchaseOrder = () => {
    if (!canCreatePurchaseOrder.value) {
        debugInfo.value += `Cannot create purchase order: ${JSON.stringify({
            supplier: !!store.supplier,
            selectedParts: store.selectedParts.length,
            billToLocation: !!store.billToLocation,
            shipToLocation: !!store.shipToLocation,
            shipFromLocation: !!store.shipFromLocation
        })}\n`;
        return;
    }

    form.supplier_id = store.supplier.id;
    form.parts = store.selectedParts.map(part => ({
        id: part.id,
        quantity: part.quantity,
        unit_cost: part.unit_cost
    }));
    form.bill_to_location_id = store.billToLocation.id;
    form.ship_to_location_id = store.shipToLocation.id;
    form.ship_from_address_index = store.shipFromAddressIndex;

    debugInfo.value += `Submitting purchase order: ${JSON.stringify(form)}\n`;

    form.post(route('purchase-orders.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Purchase Order created successfully', life: 3000 });
            store.reset();
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
    loading.value = true;
    try {
        debugInfo.value += `Fetching warehouse locations\n`;
        await store.fetchWarehouseLocations();
        debugInfo.value += `Warehouse locations fetched\n`;
    } catch (error) {
        console.error('Error fetching warehouse locations:', error);
        debugInfo.value += `Error fetching warehouse locations: ${error.message}\n`;
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load warehouse locations', life: 3000 });
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div>
        <SupplierSelection @supplier-selected="onSupplierSelected" />

        <div v-if="store.supplier" class="bg-surface-0 dark:bg-surface-950 px-6 py-8 md:px-12 lg:px-20 mt-4">
            <div class="flex items-start flex-col lg:justify-between lg:flex-row">
                <div>
                    <div class="font-medium text-3xl text-surface-900 dark:text-surface-0">Supplier Parts</div>
                    <div class="flex items-center text-surface-700 dark:text-surface-100 flex-wrap">
                        <div class="mr-8 flex items-center mt-4">
                            <i class="pi pi-box mr-2" />
                            <span>{{ store.supplierParts.length }} Associated Parts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-4">
            <AddressDisplay
                title="Ship From Address"
                :address="store.shipFromLocation"
                :loading="loading"
            />
            <AddressDisplay
                title="Ship To Address"
                :address="store.shipToLocation"
                :loading="loading"
            />
            <AddressDisplay
                title="Bill To Address"
                :address="store.billToLocation"
                :loading="loading"
            />
            <AddressDisplay
                title="Return To Address"
                :address="store.returnToLocation"
                :loading="loading"
            />
        </div>

        <div v-if="store.isLoadingParts" class="flex justify-center items-center mt-4">
            <ProgressSpinner />
            <span class="ml-2">Loading parts...</span>
        </div>

        <PartSelector v-else-if="areSupplierPartsLoaded" />

        <div v-else-if="store.supplier" class="mt-4 p-4 bg-yellow-100 text-yellow-700 rounded">
            No parts found for this supplier.
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

        <div v-if="store.debugLog" class="mt-4 p-4 bg-gray-100 rounded">
            <h4 class="font-bold mb-2">Store Debug Log:</h4>
            <pre class="whitespace-pre-wrap">{{ store.debugLog }}</pre>
        </div>
    </div>
    <Toast />
</template>
