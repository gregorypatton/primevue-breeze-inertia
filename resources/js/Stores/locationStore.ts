import { defineStore } from 'pinia';
import { ref } from 'vue';
import { Location } from '../Models/Location';
import axios from 'axios';

export const useLocationStore = defineStore('location', () => {
  const locations = ref<Location[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  async function fetchWarehouseLocations() {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/locations', {
        params: { type: 'warehouse' }
      });
      locations.value = response.data.data.map((item: any) => new Location(item));
    } catch (err) {
      console.error('Failed to fetch warehouse locations:', err);
      error.value = 'Failed to fetch warehouse locations';
    } finally {
      loading.value = false;
    }
  }

  return {
    locations,
    loading,
    error,
    fetchWarehouseLocations,
  };
});
