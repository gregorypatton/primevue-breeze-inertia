import { Model } from "@tailflow/laravel-orion/lib/model";

export class Supplier extends Model<{
    name: string;
    account_number: string;
    payment_terms: string;
    addresses: App.DTOs.SupplierAddressesDTO;
}, {
    id: number;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}>
{
    public $resource(): string {
        return 'suppliers';
    }

    static $query(): any {
        return super.$query();
    }
}
