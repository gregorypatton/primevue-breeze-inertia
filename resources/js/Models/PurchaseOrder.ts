import { Model } from '@tailflow/laravel-orion/lib/model';
import { BelongsTo } from '@tailflow/laravel-orion/lib/drivers/default/relations/belongsTo';
import { HasMany } from '@tailflow/laravel-orion/lib/drivers/default/relations/hasMany';
import { Supplier } from './Supplier';
import { Location } from './Location';
import { PurchaseOrderPart } from './PurchaseOrderPart';
import { User } from './User';

export type PurchaseOrderStatus = 'draft' | 'submitted' | 'approved' | 'partially_received' | 'fully_received' | 'closed' | 'cancelled';

export class PurchaseOrder extends Model<{
  id: number;
  number: string;
  supplier_id: number;
  location_id: number;
  status: PurchaseOrderStatus;
  total_cost: number | null;
  user_id: number;
  opened_at: string | null;
  closed_at: string | null;
  bill_to_address_index: number | null;
  ship_from_address_index: number | null;
  ship_to_address_index: number | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
}, {}, {
  supplier: Supplier;
  location: Location;
  parts: PurchaseOrderPart[];
  user: User;
}> {
  static $keyName = 'id';

  $resource(): string {
    return 'purchase-orders';
  }

  supplier(): BelongsTo<Supplier> {
    return new BelongsTo(Supplier, this);
  }

  location(): BelongsTo<Location> {
    return new BelongsTo(Location, this);
  }

  parts(): HasMany<PurchaseOrderPart> {
    return new HasMany(PurchaseOrderPart, this);
  }

  user(): BelongsTo<User> {
    return new BelongsTo(User, this);
  }

  $init(): void {
    // Initialization logic if needed
  }
}
