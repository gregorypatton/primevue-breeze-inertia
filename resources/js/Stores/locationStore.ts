import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { Location } from '../Models/Location';
import api from '../api';

export const useLocationStore = defineStore('location', () => {
  const locations = ref<Location[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const warehouseLocations = computed(() => {
    return locations.value.filter(location => location.type === 'warehouse');
  });

  async function fetchLocations() {
    loading.value = true;
    error.value = null;
    try {
      const response = await api.get('/locations');
      locations.value = response.data.data.map((item: any) => new Location(item));
      console.log('Fetched locations:', locations.value);
    } catch (err: any) {
      console.error('Failed to fetch locations:', err);
      error.value = err.message || 'Failed to fetch locations';
    } finally {
      loading.value = false;
    }
  }

  return {
    locations,
    warehouseLocations,
    loading,
    error,
    fetchLocations,
  };
});
