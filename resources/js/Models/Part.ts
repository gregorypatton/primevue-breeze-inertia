import { Model } from "@tailflow/laravel-orion/lib/model";
import { BelongsTo } from "@tailflow/laravel-orion/lib/drivers/default/relations/belongsTo";
import { HasMany } from "@tailflow/laravel-orion/lib/drivers/default/relations/hasMany";
import { Supplier } from "./Supplier";
import { Manufacturer } from "./Manufacturer";
import { PurchaseOrderPart } from "./PurchaseOrderPart";

interface IdentifierDTO {
    identifiers: Array<{ type: string; value: string }>;
}

interface ReplenishmentDataDTO {
    lead_days: number;
    purchaseTerms: Array<{ cost_per_part: number }>;
}

export class Part extends Model<{
    id: number;
    part_number: string;
    quantity: number;
    uom: string;
    description: string;
    identifiers: IdentifierDTO;
    regulatory_information: IdentifierDTO;
    replenishment_data: ReplenishmentDataDTO;
    supplier_id: number;
    manufacturer_id: number;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    unit_cost: number;
    total_cost: number;
}, {}, {
    supplier: Supplier;
    manufacturer: Manufacturer;
    purchaseOrderParts: PurchaseOrderPart[];
}> {
    public $resource(): string {
        return 'parts';
    }

    public supplier(): BelongsTo<Supplier> {
        return new BelongsTo(Supplier, this);
    }

    public manufacturer(): BelongsTo<Manufacturer> {
        return new BelongsTo(Manufacturer, this);
    }

    public purchaseOrderParts(): HasMany<PurchaseOrderPart> {
        return new HasMany(PurchaseOrderPart, this);
    }

    public getUnitCost(): number {
        if (this.$attributes.replenishment_data &&
            this.$attributes.replenishment_data.purchaseTerms &&
            this.$attributes.replenishment_data.purchaseTerms.length > 0) {
            return this.$attributes.replenishment_data.purchaseTerms[0].cost_per_part;
        }
        return 0;
    }

    public calculateTotalCost(): number {
        const unitCost = this.getUnitCost();
        return unitCost * this.$attributes.quantity;
    }

    public updateQuantity(quantity: number): void {
        this.$attributes.quantity = quantity;
        this.$attributes.total_cost = this.calculateTotalCost();
    }

    public getIdentifierValue(type: string): string | null {
        const identifier = this.$attributes.identifiers.identifiers.find(i => i.type === type);
        return identifier ? identifier.value : null;
    }

    public getRegulatoryInformationValue(type: string): string | null {
        const info = this.$attributes.regulatory_information.identifiers.find(i => i.type === type);
        return info ? info.value : null;
    }

    get id(): number {
        return this.$attributes.id;
    }
}
