import { Model } from "@tailflow/laravel-orion/lib/model";
import { HasMany } from "@tailflow/laravel-orion/lib/drivers/default/relations/hasMany";
import { Part } from "./Part";
import { AddressDTO } from "@/Interfaces/AddressDTO";

export class Supplier extends Model<{
    id: number;
    name: string;
    account_number: string;
    payment_terms: string;
    free_shipping: boolean;
    free_shipping_threshold_usd: number;
    addresses: {
        billing: AddressDTO[];
        shipping: AddressDTO[];
    };
}, {
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}, {
    parts: Part[];
}> {
    public $resource(): string {
        return 'suppliers';
    }

    public parts(): HasMany<Part> {
        return new HasMany(Part, this);
    }
}
