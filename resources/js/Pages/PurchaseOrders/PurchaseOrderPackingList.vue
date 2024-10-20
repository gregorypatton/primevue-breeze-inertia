<script setup>
import { computed } from 'vue';
import { usePurchaseOrderStore } from '@/Stores/purchaseOrderStore';

const store = usePurchaseOrderStore();

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};

const subtotal = computed(() => store.subtotal);
const taxAmount = computed(() => store.taxAmount);
const total = computed(() => store.total);
</script>

<template>
    <div class="p-4">
        <h1 class="text-2xl font-bold mb-4">Purchase Order Packing List</h1>
        <p class="mb-4">Date: {{ formatDate(new Date()) }}</p>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div>
                <h2 class="font-bold">Supplier:</h2>
                <p>{{ store.shipFromLocation?.name }}</p>
                <p>{{ store.shipFromLocation?.address1 }}</p>
                <p v-if="store.shipFromLocation?.address2">{{ store.shipFromLocation.address2 }}</p>
                <p>{{ store.shipFromLocation?.city }}, {{ store.shipFromLocation?.state }} {{ store.shipFromLocation?.zip }}</p>
            </div>
            <div>
                <h2 class="font-bold">Ship To:</h2>
                <p>{{ store.shipToLocation?.name }}</p>
                <p>{{ store.shipToLocation?.address1 }}</p>
                <p v-if="store.shipToLocation?.address2">{{ store.shipToLocation.address2 }}</p>
                <p>{{ store.shipToLocation?.city }}, {{ store.shipToLocation?.state }} {{ store.shipToLocation?.zip }}</p>
            </div>
            <div>
                <h2 class="font-bold">Bill To:</h2>
                <p>{{ store.billToLocation?.name }}</p>
                <p>{{ store.billToLocation?.address1 }}</p>
                <p v-if="store.billToLocation?.address2">{{ store.billToLocation.address2 }}</p>
                <p>{{ store.billToLocation?.city }}, {{ store.billToLocation?.state }} {{ store.billToLocation?.zip }}</p>
            </div>
        </div>

        <table class="w-full mb-6">
            <thead>
                <tr>
                    <th class="text-left">Part Number</th>
                    <th class="text-left">Description</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="part in store.selectedParts" :key="part.id">
                    <td>{{ part.part_number }}</td>
                    <td>{{ part.description }}</td>
                    <td class="text-right">{{ part.quantity }}</td>
                    <td class="text-right">{{ formatCurrency(part.unit_cost) }}</td>
                    <td class="text-right">{{ formatCurrency(part.quantity * part.unit_cost) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="text-right">
            <p>Subtotal: {{ formatCurrency(subtotal) }}</p>
            <p>Tax ({{ store.taxRate * 100 }}%): {{ formatCurrency(taxAmount) }}</p>
            <p class="font-bold">Total: {{ formatCurrency(total) }}</p>
        </div>
    </div>
</template>

<style scoped>
@media print {
    .p-4 {
        padding: 1rem;
    }
    @page {
        size: auto;
        margin: 20mm;
    }
}
</style>
