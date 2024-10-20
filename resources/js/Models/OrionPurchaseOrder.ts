import { Model } from "@tailflow/laravel-orion/lib/model";
import { BelongsTo } from "@tailflow/laravel-orion/lib/drivers/default/relations/belongsTo";
import { HasMany } from "@tailflow/laravel-orion/lib/drivers/default/relations/hasMany";
import { Supplier } from "./Supplier";
import { PurchaseOrderPart } from "./PurchaseOrderPart";

export class OrionPurchaseOrder extends Model<{
    id: number,
    purchase_order_number: string,
    status: string,
    supplier_id: number,
    total_cost: number,
    bill_to_address: {
        street1: string,
        street2: string,
        city: string,
        state: string,
        postal_code: string,
        country: string
    },
    ship_from_address: {
        street1: string,
        street2: string,
        city: string,
        state: string,
        postal_code: string,
        country: string
    },
    ship_to_address: {
        street1: string,
        street2: string,
        city: string,
        state: string,
        postal_code: string,
        country: string
    }
}, {
    created_at: string,
    updated_at: string,
    deleted_at: string | null,
}, {
    supplier: Supplier,
    purchaseOrderParts: Array<PurchaseOrderPart>
}> {
    public $resource(): string {
        return 'purchase-orders';
    }

    public supplier(): BelongsTo<Supplier> {
        return new BelongsTo(Supplier, this);
    }

    public purchaseOrderParts(): HasMany<PurchaseOrderPart> {
        return new HasMany(PurchaseOrderPart, this);
    }
}
