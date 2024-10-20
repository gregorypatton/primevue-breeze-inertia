<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import SupplierSelection from './SupplierSelection.vue';
import PartSelector from './PartSelector.vue';
import AddressDisplay from '../AddressDisplay.vue';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import Badge from 'primevue/badge';
import { Location } from '@/Models/Location';
import { Supplier } from '@/Models/Supplier';

/** @type {import('vue').Ref<number|null>} */
const selectedSupplierId = ref(null);
/** @type {import('vue').Ref<InstanceType<typeof PartSelector>|null>} */
const partSelector = ref(null);
const toast = useToast();
/** @type {import('vue').Ref<App.DTOs.AddressDTO|null>} */
const billToLocation = ref(null);
/** @type {import('vue').Ref<App.DTOs.AddressDTO|null>} */
const shipToLocation = ref(null);
/** @type {import('vue').Ref<App.DTOs.AddressDTO|null>} */
const shipFromLocation = ref(null);
/** @type {import('vue').Ref<App.DTOs.AddressDTO|null>} */
const returnToLocation = ref(null);
/** @type {import('vue').Ref<boolean>} */
const loading = ref(true);
/** @type {import('vue').Ref<string>} */
const debugInfo = ref('');

const form = useForm({
    supplier_id: null,
    parts: [],
    bill_to_location_id: null,
    ship_to_location_id: null
});

/**
 * @param {Supplier} supplier
 */
const onSupplierSelected = async (supplier) => {
    selectedSupplierId.value = supplier.id;
    form.supplier_id = supplier.id;

    try {
        const response = await Location.$query()
            .filter('supplier_id', '=', supplier.id)
            .get();

        console.log('Supplier location response:', response);
        debugInfo.value += `Supplier location response: ${JSON.stringify(response, null, 2)}\n`;

        if (response.data && response.data.length > 0) {
            const supplierLocation = response.data[0];
            shipFromLocation.value = supplierLocation.addresses?.shipFrom?.[0] || null;
            returnToLocation.value = supplierLocation.addresses?.returnTo?.[0] || null;
        }

        console.log('Selected supplier:', supplier);
        console.log('Ship from location:', shipFromLocation.value);
        debugInfo.value += `Selected supplier: ${JSON.stringify(supplier, null, 2)}\n`;
        debugInfo.value += `Ship from location: ${JSON.stringify(shipFromLocation.value, null, 2)}\n`;
    } catch (error) {
        console.error('Error fetching supplier location:', error);
        debugInfo.value += `Error fetching supplier location: ${error.message}\n`;
        if (error.response) {
            debugInfo.value += `Response data: ${JSON.stringify(error.response.data, null, 2)}\n`;
        }
    }
};

const canCreatePurchaseOrder = computed(() => {
    return form.supplier_id &&
           partSelector.value &&
           partSelector.value.selectedParts &&
           partSelector.value.selectedParts.length > 0 &&
           form.bill_to_location_id &&
           form.ship_to_location_id;
});

const createPurchaseOrder = () => {
    if (!canCreatePurchaseOrder.value) {
        return;
    }

    form.parts = partSelector.value.selectedParts.map(part => ({
        part_id: part.id,
        quantity: part.quantity,
        unit_cost: part.replenishment_data.purchaseTerms[0].cost_per_part
    }));

    form.post(route('purchase-orders.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Purchase Order created successfully', life: 3000 });
            // Reset the form
            form.reset();
            selectedSupplierId.value = null;
            shipFromLocation.value = null;
            returnToLocation.value = null;
            if (partSelector.value) {
                partSelector.value.resetSelection();
            }
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to create Purchase Order', life: 3000 });
        }
    });
};

const loadLocations = async () => {
    try {
        console.log('Starting to fetch locations...');
        debugInfo.value += 'Starting to fetch locations...\n';

        const response = await Location.$query().get();

        console.log('Locations response:', response);
        debugInfo.value += `Locations response: ${JSON.stringify(response, null, 2)}\n`;

        if (response.data && response.data.length > 0) {
            const warehouseLocations = response.data.filter(loc => loc.type === 'warehouse');
            if (warehouseLocations.length > 0) {
                const warehouseLocation = warehouseLocations[0];
                billToLocation.value = warehouseLocation.addresses?.billTo?.[0] || null;
                shipToLocation.value = warehouseLocation.addresses?.shipTo?.[0] || null;

                if (billToLocation.value) {
                    form.bill_to_location_id = warehouseLocation.id;
                }
                if (shipToLocation.value) {
                    form.ship_to_location_id = warehouseLocation.id;
                }
            }
        }

        console.log('Bill to location:', billToLocation.value);
        console.log('Ship to location:', shipToLocation.value);
        debugInfo.value += `Bill to location: ${JSON.stringify(billToLocation.value, null, 2)}\n`;
        debugInfo.value += `Ship to location: ${JSON.stringify(shipToLocation.value, null, 2)}\n`;

        if (!billToLocation.value || !shipToLocation.value) {
            console.error('Not enough locations found');
            debugInfo.value += 'Error: Not enough locations found\n';
        }
    } catch (error) {
        console.error('Error fetching locations:', error);
        debugInfo.value += `Error fetching locations: ${error.message}\n`;
        debugInfo.value += `Error stack: ${error.stack}\n`;
        if (error.response) {
            debugInfo.value += `Response data: ${JSON.stringify(error.response.data, null, 2)}\n`;
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadLocations();
});
</script>

<template>
    <div>
        <SupplierSelection @supplier-selected="onSupplierSelected" />

        <div class="grid grid-cols-2 gap-4 mt-4">
            <AddressDisplay
                title="Ship From Address"
                :address="shipFromLocation"
                :loading="loading"
            />
            <AddressDisplay
                title="Ship To Address"
                :address="shipToLocation"
                :loading="loading"
            />
            <AddressDisplay
                title="Bill To Address"
                :address="billToLocation"
                :loading="loading"
            />
            <AddressDisplay
                title="Return To Address"
                :address="returnToLocation"
                :loading="loading"
            />
        </div>

        <PartSelector v-if="selectedSupplierId" :supplier-id="selectedSupplierId" ref="partSelector" />

        <div class="mt-4">
            <Button label="Create Purchase Order" @click="createPurchaseOrder" :disabled="!canCreatePurchaseOrder" />
        </div>

        <div v-if="debugInfo" class="mt-4 p-4 bg-gray-100 rounded">
            <h4 class="font-bold mb-2">Debug Information:</h4>
            <pre class="whitespace-pre-wrap">{{ debugInfo }}</pre>
        </div>
    </div>
    <Toast />
</template>
