import { Model } from '@tailflow/laravel-orion/lib/model';
import { BelongsTo } from '@tailflow/laravel-orion/lib/drivers/default/relations/belongsTo';
import { Part } from './Part';
import { PurchaseOrder } from './PurchaseOrder';

export class PurchaseOrderPart extends Model<{
  id: number;
  purchase_order_id: number;
  part_id: number;
  quantity_ordered: number;
  unit_cost: number | null;
  total_cost: number | null;
  quantity_invoiced: number;
  quantity_received: number;
  status: string | null;
  notes: string | null;
  created_at: string;
  updated_at: string;
  part?: Part;
}> {
  static $keyName = 'id';

  $resource(): string {
    return 'purchase-order-parts';
  }

  purchaseOrder(): BelongsTo<PurchaseOrder> {
    return new BelongsTo(PurchaseOrder, this);
  }

  part(): BelongsTo<Part> {
    return new BelongsTo(Part, this);
  }

  calculateTotalCost(): void {
    this.$attributes.total_cost = this.$attributes.quantity_ordered * (this.$attributes.unit_cost || 0);
  }

  static includes(): string[] {
    return ['purchaseOrder', 'part'];
  }

  static filterableBy(): string[] {
    return [
      'id',
      'purchase_order_id',
      'part_id',
      'quantity_ordered',
      'unit_cost',
      'total_cost',
      'quantity_invoiced',
      'quantity_received',
      'status',
    ];
  }

  static sortableBy(): string[] {
    return [
      'id',
      'quantity_ordered',
      'unit_cost',
      'total_cost',
      'quantity_invoiced',
      'quantity_received',
      'status',
    ];
  }

  static searchableBy(): string[] {
    return ['notes'];
  }

  $init(): void {
    // Initialization logic if needed
  }
}
