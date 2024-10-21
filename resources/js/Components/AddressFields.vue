<template>
  <div class="address-fields">
    <h2>Addresses</h2>
    <div class="grid">
      <div class="col-12 md:col-4">
        <h3>Ship From</h3>
        <AddressDisplay :address="store.purchaseOrder.shipFrom" />
      </div>
      <div class="col-12 md:col-4">
        <h3>Bill To</h3>
        <AddressDisplay :address="store.purchaseOrder.billTo" />
      </div>
      <div class="col-12 md:col-4">
        <h3>Ship To</h3>
        <AddressDisplay :address="store.purchaseOrder.shipTo" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { usePurchaseOrderStore } from '../Stores/purchaseOrderStore';
import AddressDisplay from './AddressDisplay.vue';

const store = usePurchaseOrderStore();
</script>
