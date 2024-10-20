import { Model } from "@tailflow/laravel-orion/lib/model";
import { BelongsTo } from "@tailflow/laravel-orion/lib/drivers/default/relations/belongsTo";
import { HasMany } from "@tailflow/laravel-orion/lib/drivers/default/relations/hasMany";
import { Supplier } from "./Supplier";
import { Manufacturer } from "./Manufacturer";
import { PurchaseOrderPart } from "./PurchaseOrderPart";

export class Part extends Model<{
    id: number;
    part_number: string;
    quantity: number;
    uom: string;
    description: string;
    identifiers: App.DTOs.IdentifierDTO;
    regulatory_information: App.DTOs.IdentifierDTO;
    replenishment_data: App.DTOs.ReplenishmentDataDTO;
    supplier_id: number;
    manufacturer_id: number;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
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
        if (this.replenishment_data &&
            this.replenishment_data.purchaseTerms &&
            this.replenishment_data.purchaseTerms.length > 0) {
            return this.replenishment_data.purchaseTerms[0].cost_per_part;
        }
        return 0;
    }

    public calculateTotalCost(): number {
        const unitCost = this.getUnitCost();
        return unitCost * this.quantity;
    }

    public updateQuantity(quantity: number): void {
        this.quantity = quantity;
    }

    public getIdentifierValue(type: string): string | null {
        const identifier = this.identifiers.identifiers.find(i => i.type === type);
        return identifier ? identifier.value : null;
    }

    public getRegulatoryInformationValue(type: string): string | null {
        const info = this.regulatory_information.identifiers.find(i => i.type === type);
        return info ? info.value : null;
    }
}
