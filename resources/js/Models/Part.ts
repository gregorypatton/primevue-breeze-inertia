import { Model } from "@tailflow/laravel-orion/lib/model";
import { BelongsTo } from "@tailflow/laravel-orion/lib/drivers/default/relations/belongsTo";
import { HasMany } from "@tailflow/laravel-orion/lib/drivers/default/relations/hasMany";
import { Supplier } from "./Supplier";
import { PurchaseOrderPart } from "./PurchaseOrderPart";

export class Part extends Model<{
    id: number,
    part_number: string,
    description: string,
    unit_cost: number,
    supplier_id: number,
    replenishment_data: {
        purchaseTerms: Array<{
            cost_per_part: number
        }>
    },
    options: {
        visible: boolean,
        key: string
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
        return 'parts';
    }

    public supplier(): BelongsTo<Supplier> {
        return new BelongsTo(Supplier, this);
    }

    public purchaseOrderParts(): HasMany<PurchaseOrderPart> {
        return new HasMany(PurchaseOrderPart, this);
    }
}
