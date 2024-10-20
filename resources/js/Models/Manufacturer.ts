import { Model } from "@tailflow/laravel-orion/lib/model";
import { HasMany } from "@tailflow/laravel-orion/lib/drivers/default/relations/hasMany";
import { Part } from "./Part";

export class Manufacturer extends Model<{
    id: number;
    name: string;
}, {
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}, {
    parts: Part[];
}> {
    public $resource(): string {
        return 'manufacturers';
    }

    public parts(): HasMany<Part> {
        return new HasMany(Part, this);
    }
}
