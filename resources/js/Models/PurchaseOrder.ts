import { Model } from '@tailflow/laravel-orion/lib/model';

export class PurchaseOrder extends Model<{
  id: number;
  number: string;
  supplier_id: number;
  location_id: number;
  status: 'draft' | 'submitted' | 'approved' | 'partially_received' | 'fully_received' | 'closed' | 'cancelled';
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
}> {
  static $keyName = 'id';

  $resource(): string {
    return 'purchase-orders';
  }

  $init(): void {
    // Initialization logic if needed
  }
}
