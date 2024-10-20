import { Model } from "@tailflow/laravel-orion/lib/model";

export class Part extends Model<{
    part_number: string,
    description: string,
    unit_cost: number,
    replenishment_data: {
        purchaseTerms: Array<{
            cost_per_part: number
        }>
    },
    quantity?: number,
    total_cost?: number
}, {
    id: number,
    created_at: string,
    updated_at: string,
    deleted_at: string | null
}>
{
    public $resource(): string {
        return 'parts';
    }
}
