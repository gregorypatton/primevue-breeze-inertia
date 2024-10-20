<template>
  <div class="mb-4">
    <h3 class="text-lg font-semibold mb-2">Select Supplier</h3>
    <Dropdown
      v-model="selectedSupplier"
      :options="suppliers"
      optionLabel="name"
      placeholder="Select a Supplier"
      class="w-full md:w-14rem"
      @change="onSupplierChange"
    />
    <Card v-if="selectedSupplier" class="mt-4">
      <template #title>
        {{ selectedSupplier.name }}
      </template>
      <template #content>
        <p><strong>Account Number:</strong> {{ selectedSupplier.account_number }}</p>
        <p><strong>Payment Terms:</strong> {{ selectedSupplier.payment_terms }}</p>
        <div v-if="shipFromAddress">
          <h4 class="font-semibold mt-2">Ship From Address:</h4>
          <p>{{ shipFromAddress.address1 }}</p>
          <p v-if="shipFromAddress.address2">{{ shipFromAddress.address2 }}</p>
          <p>{{ shipFromAddress.city }}, {{ shipFromAddress.state_prov_code }} {{ shipFromAddress.zip }}</p>
          <p v-if="shipFromAddress.phone_number">Phone: {{ shipFromAddress.phone_number }}</p>
          <p v-if="shipFromAddress.email_address">Email: {{ shipFromAddress.email_address }}</p>
        </div>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Supplier } from '../../Models/Supplier';
import { AddressDTO } from '../../Interfaces/AddressDTO';
import { usePurchaseOrderStore } from '../../Stores/purchaseOrderStore';
import Dropdown from 'primevue/dropdown';
import Card from 'primevue/card';

const emit = defineEmits<{
  (e: 'supplier-selected', supplier: Supplier): void
}>();

const purchaseOrderStore = usePurchaseOrderStore();

const suppliers = ref<Supplier[]>([]);
const selectedSupplier = ref<Supplier | null>(null);

const shipFromAddress = computed((): AddressDTO | null => {
  if (selectedSupplier.value && selectedSupplier.value.addresses) {
    const addresses = selectedSupplier.value.addresses;
    const address = addresses.shipFrom?.[0];
    if (address) {
      return {
        address1: address.address1 || '',
        address2: address.address2 || null,
        city: address.city || '',
        state_prov_code: address.state_prov_code || '',
        zip: address.zip || '',
        phone_number: address.phone_number || null,
        email_address: address.email_address || null,
        type: 'shipping'
      };
    }
  }
  return null;
});

const onSupplierChange = () => {
  if (selectedSupplier.value) {
    purchaseOrderStore.setSupplier(selectedSupplier.value);
    if (shipFromAddress.value) {
      purchaseOrderStore.setAddress('shipFrom', shipFromAddress.value);
    }
    emit('supplier-selected', selectedSupplier.value);
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
