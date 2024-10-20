import { Model } from "@tailflow/laravel-orion/lib/model";

export class Location extends Model<{
    name: string;
    type: string;
    virtual_type: string | null;
    addresses: App.DTOs.LocationAddressesDTO;
    parent_id: number | null;
    supplier_id: number | null;
}, {
    id: number;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}>
{
    public $resource(): string {
        return 'locations';
    }

    static $query(): any {
        return super.$query();
    }
}
