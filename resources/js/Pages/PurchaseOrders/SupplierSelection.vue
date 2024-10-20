<template>
    <div class="mb-4">
        <h3 class="text-lg font-semibold mb-2">Select Supplier</h3>
        <Dropdown
            v-model="selectedSupplier"
            :options="suppliers"
            optionLabel="$attributes.name"
            placeholder="Select a Supplier"
            class="w-full md:w-14rem"
            @change="onSupplierChange"
        />
        <Card v-if="selectedSupplier" class="mt-4">
            <template #title>
                {{ selectedSupplier.$attributes.name }}
            </template>
            <template #content>
                <p><strong>Account Number:</strong> {{ selectedSupplier.$attributes.account_number }}</p>
                <p><strong>Payment Terms:</strong> {{ selectedSupplier.$attributes.payment_terms }}</p>
                <div v-if="shipFromAddress">
                    <h4 class="font-semibold mt-2">Ship From Address:</h4>
                    <p>{{ shipFromAddress.address1 }}</p>
                    <p v-if="shipFromAddress.address2">{{ shipFromAddress.address2 }}</p>
                    <p>{{ shipFromAddress.city }}, {{ shipFromAddress.state_prov_code }} {{ shipFromAddress.zip }}</p>
                </div>
            </template>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Supplier } from '../../Models/Supplier';
import Dropdown from 'primevue/dropdown';
import Card from 'primevue/card';

const emit = defineEmits(['supplier-selected']);

const suppliers = ref<Supplier[]>([]);
const selectedSupplier = ref<Supplier | null>(null);

const shipFromAddress = computed(() => {
    if (selectedSupplier.value && selectedSupplier.value.$attributes.addresses) {
        return selectedSupplier.value.$attributes.addresses.shipFrom?.[0] || null;
    }
    return null;
});

const onSupplierChange = () => {
    if (selectedSupplier.value) {
        emit('supplier-selected', {
            id: selectedSupplier.value.$getKey(),
            shipFromAddressIndex: 0, // Assuming we're always using the first address
            supplier: selectedSupplier.value
        });
    }
};

onMounted(async () => {
    try {
        const response = await Supplier.$query().get();
        suppliers.value = response;
    } catch (error) {
        console.error('Error fetching suppliers:', error);
    }
});
</script>
