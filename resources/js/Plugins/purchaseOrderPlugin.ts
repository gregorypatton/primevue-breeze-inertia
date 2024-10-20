import { PiniaPluginContext } from 'pinia'

export function purchaseOrderPlugin({ store }: PiniaPluginContext) {
  if (store.$id === 'purchaseOrder') {
    store.$state.creationTime = new Date()

    // Add a subscription to save state to localStorage
    store.$subscribe((mutation, state) => {
      localStorage.setItem('purchaseOrderState', JSON.stringify(state))
    })

    // Extend the store with a new property
    return {
      creationTime: new Date(),
    }
  }
}

// Extend the PiniaCustomProperties interface
declare module 'pinia' {
  export interface PiniaCustomProperties {
    creationTime?: Date
  }
}
