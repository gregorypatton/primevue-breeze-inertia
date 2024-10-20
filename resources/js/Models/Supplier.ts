import { Model } from '@tailflow/laravel-orion/lib/model';
import { HasMany } from '@tailflow/laravel-orion/lib/drivers/default/relations/hasMany';
import { Part } from './Part';
import { Location } from './Location';

export class Supplier extends Model {
  $resource(): string {
    return 'suppliers';
  }

  // Properties
  id!: number;
  name!: string;
  account_number!: string;
  payment_terms!: string;
  lead_time_days!: number;
  free_shipping_threshold_usd!: number;
  addresses: any;
  contact: any;
  created_at!: string;
  updated_at!: string;
  deleted_at: string | null = null;

  // Relationships
  parts(): HasMany<Part> {
    return this.hasMany(Part);
  }

  locations(): HasMany<Location> {
    return this.hasMany(Location);
  }
}
