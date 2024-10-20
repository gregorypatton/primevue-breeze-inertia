<script setup lang="ts">
import { computed } from 'vue';
import Card from 'primevue/card';
import ProgressSpinner from 'primevue/progressspinner';

interface AddressDTO {
  address1: string | null;
  address2: string | null;
  city: string | null;
  state_prov_code: string | null;
  zip: string | null;
  phone_number: string | null;
  email_address: string | null;
}

interface Props {
  address: AddressDTO | null;
  title: string;
  loading: boolean;
}

const props = defineProps<Props>();

const formattedAddress = computed(() => {
  if (!props.address) return 'No address available';
  let formatted = '';
  if (props.address.address1) formatted += `${props.address.address1}\n`;
  if (props.address.address2) formatted += `${props.address.address2}\n`;
  if (props.address.city || props.address.state_prov_code || props.address.zip) {
    formatted += `${props.address.city || ''}, ${props.address.state_prov_code || ''} ${props.address.zip || ''}\n`;
  }
  if (props.address.phone_number) formatted += `Phone: ${props.address.phone_number}\n`;
  if (props.address.email_address) formatted += `Email: ${props.address.email_address}`;
  return formatted.trim();
});
</script>

<template>
  <Card class="col-span-1">
    <template #title>
      <h3 class="text-lg font-semibold">{{ title }}</h3>
    </template>
    <template #content>
      <div v-if="address && !loading" class="whitespace-pre-wrap">
        {{ formattedAddress }}
      </div>
      <div v-else-if="loading">
        <ProgressSpinner />
      </div>
      <div v-else>No address available</div>
    </template>
  </Card>
</template>
