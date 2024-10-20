import { Model } from "@tailflow/laravel-orion/lib/model";
import { BelongsTo } from "@tailflow/laravel-orion/lib/drivers/default/relations/belongsTo";
import { OrionPurchaseOrder } from "./OrionPurchaseOrder";
import { Part } from "./Part";

export class PurchaseOrderPart extends Model<{
    id: number,
    purchase_order_id: number,
    part_id: number,
    quantity: number,
    unit_cost: number,
    total_cost: number,
    received_quantity: number,
    status: string
}, {
    created_at: string,
    updated_at: string,
    deleted_at: string | null,
}, {
    purchaseOrder: OrionPurchaseOrder,
    part: Part
}> {
    public $resource(): string {
        return 'purchase-order-parts';
    }

    public purchaseOrder(): BelongsTo<OrionPurchaseOrder> {
        return new BelongsTo(OrionPurchaseOrder, this);
    }

    public part(): BelongsTo<Part> {
        return new BelongsTo(Part, this);
    }
}
