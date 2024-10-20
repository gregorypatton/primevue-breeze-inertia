export interface AddressDTO {
  street1: string;
  street2?: string;
  city: string;
  state: string;
  postal_code: string;
  country: string;
  type?: 'billing' | 'shipping';

  // Alternative property names used in SupplierSelection.vue
  address1?: string;
  address2?: string;
  state_prov_code?: string;
  zip?: string;
}
