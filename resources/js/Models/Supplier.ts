import { Model } from '@tailflow/laravel-orion/lib/model';
import { Part } from './Part';

export class Supplier extends Model {
  id!: number;
  name!: string;
  account_number!: string;
  payment_terms!: string;
  lead_time_days!: number | null;
  free_shipping_threshold_usd!: number;
  contact!: string;
  addresses?: {
    billTo: any[];
    shipFrom: any[];
    shipTo: any[];
    returnTo: any[];
  };
  created_at!: string;
  updated_at!: string;
  deleted_at: string | null = null;
  part_count: number = 0;
  parts?: Part[];

  constructor(data?: Partial<Supplier>) {
    super();
    if (data) {
      this.$setAttributes(data);
    }
  }

  $resource(): string {
    return 'suppliers';
  }
}
