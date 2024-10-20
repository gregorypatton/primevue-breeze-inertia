import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { PurchaseOrder } from '@/Models/PurchaseOrder'

export const usePurchaseOrderStore = defineStore('purchaseOrder', () => {
  const purchaseOrders = ref<PurchaseOrder[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const lastUpdated = ref(new Date())

  async function fetchPurchaseOrders() {
    loading.value = true
    error.value = null
    try {
      const response = await PurchaseOrder.$query()
        .with(['supplier', 'purchaseOrderParts', 'billToLocation', 'shipToLocation'])
        .get()
      purchaseOrders.value = response as PurchaseOrder[]
      lastUpdated.value = new Date()
    } catch (err) {
      handleError(err, 'Failed to fetch purchase orders')
    } finally {
      loading.value = false
    }
  }

  function handleError(err: unknown, defaultMessage: string) {
    if (err instanceof Error) {
      error.value = err.message
    } else {
      error.value = defaultMessage
    }
    console.error(err)
  }

  const getTotalPurchaseOrders = computed(() => purchaseOrders.value.length)

  const getTotalPurchaseOrderValue = computed(() => {
    return purchaseOrders.value.reduce((total, po) => total + (po.$attributes.total_cost || 0), 0)
  })

  function formatCurrency(amount: number) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount)
  }

  return {
    purchaseOrders,
    loading,
    error,
    lastUpdated,
    fetchPurchaseOrders,
    getTotalPurchaseOrders,
    getTotalPurchaseOrderValue,
    formatCurrency,
  }
})

export type PurchaseOrderStore = ReturnType<typeof usePurchaseOrderStore>
