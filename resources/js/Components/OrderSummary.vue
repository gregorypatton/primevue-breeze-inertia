<template>
  <div class="order-summary">
    <h2>Order Summary</h2>
    <div class="summary-details">
      <div class="summary-row">
        <span>Subtotal:</span>
        <span>{{ formatCurrency(store.subtotal) }}</span>
      </div>
      <div class="summary-row">
        <span>Tax:</span>
        <span>{{ formatCurrency(store.tax) }}</span>
      </div>
      <div class="summary-row total">
        <span>Total:</span>
        <span>{{ formatCurrency(store.total) }}</span>
      </div>
    </div>
    <div class="special-instructions">
      <h3>Special Instructions</h3>
      <Textarea v-model="specialInstructions" @input="updateSpecialInstructions" rows="5" class="w-full" />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { usePurchaseOrderStore } from '../Stores/purchaseOrderStore';
import Textarea from 'primevue/textarea';

const store = usePurchaseOrderStore();
const specialInstructions = ref('');

function formatCurrency(value) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
}

function updateSpecialInstructions() {
  store.setSpecialInstructions(specialInstructions.value);
}

watch(() => store.purchaseOrder.specialInstructions, (newValue) => {
  specialInstructions.value = newValue;
});
</script>

<style scoped>
.order-summary {
  margin-top: 2rem;
}

.summary-details {
  margin-bottom: 1rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.total {
  font-weight: bold;
  font-size: 1.2em;
}

.special-instructions {
  margin-top: 1rem;
}
</style>
