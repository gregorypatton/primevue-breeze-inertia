import { Model } from '@tailflow/laravel-orion/lib/model';

interface Address {
  zip: string;
  city: string;
  address1: string;
  address2: string;
  phone_number: string;
  email_address: string;
  state_prov_code: string;
}

interface Addresses {
  other: Address[] | null;
  billTo: Address[];
  shipTo: Address[];
  shipFrom: Address[];
}

export class Location extends Model {
  id!: number;
  name!: string;
  virtual_type: string | null = null;
  addresses!: Addresses;
  type!: string;
  parent_id: number | null = null;
  supplier_id: number | null = null;
  created_at!: string;
  updated_at!: string;

  constructor(data?: Partial<Location>) {
    super();
    if (data) {
      this.$setAttributes(data);
      if (typeof this.addresses === 'string') {
        this.addresses = JSON.parse(this.addresses);
      }
    }
  }

  $resource(): string {
    return 'locations';
  }
}
