<template>
  <div>
    <div class="mb-4">
      <label class="block text-gray-700 text-sm font-bold mb-2" :for="type + 'Location'">
        {{ label }}
      </label>
      <select
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
      >
        <option v-for="location in locations" :key="location.$attributes.id" :value="location">
          {{ location.$attributes.name }}
        </option>
      </select>
    </div>
    <div v-if="selectedLocation && selectedLocation.$attributes.addresses">
      <label class="block text-gray-700 text-sm font-bold mb-2" :for="type + 'Address'">
        {{ type === 'billTo' ? 'Bill To' : 'Ship To' }} Address
      </label>
      <select
        :value="selectedAddress"
        @input="$emit('address-selected', $event.target.value)"
        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
      >
        <option v-for="(address, index) in selectedLocation.$attributes.addresses" :key="index" :value="address">
          {{ formatAddress(address) }}
        </option>
      </select>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Location } from '@/Models/Location';
import { App } from '@/Models/types';

type AddressDTO = App.DTOs.AddressDTO;

const props = defineProps<{
  modelValue: Location | null;
  locations: Location[];
  type: 'billTo' | 'shipTo';
  label: string;
  selectedAddress: AddressDTO | null;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: Location): void;
  (e: 'address-selected', value: AddressDTO): void;
}>();

const selectedLocation = computed(() => props.modelValue);

const formatAddress = (address: AddressDTO) => {
  return `${address.address1}${address.address2 ? ', ' + address.address2 : ''}, ${address.city}, ${address.state_prov_code} ${address.zip}`;
};
</script>
