import { Model, Location } from './index';

export class Inventory extends Model {
  declare id: number;
  declare location_id: number;
  declare inventoryable_id: number;
  declare inventoryable_type: string;
  declare quantity: number;
  declare available_quantity: number;
  declare total_quantity: number;

  // Relationships
  declare location?: Location;
  declare inventoryable?: any; // This could be Part or Product, depending on the polymorphic relationship

  $attributes: {
    id: number;
    location_id: number;
    inventoryable_id: number;
    inventoryable_type: string;
    quantity: number;
    available_quantity: number;
    total_quantity: number;
  };

  constructor(data?: Partial<Inventory>) {
    super(data);
    this.$attributes = {
      id: this.id,
      location_id: this.location_id,
      inventoryable_id: this.inventoryable_id,
      inventoryable_type: this.inventoryable_type,
      quantity: this.quantity,
      available_quantity: this.available_quantity,
      total_quantity: this.total_quantity
    };
  }

  // Implement Orion-specific query methods
  static includes(): string[] {
    return ['location', 'inventoryable'];
  }

  static filterableBy(): string[] {
    return ['id', 'location_id', 'inventoryable_id', 'inventoryable_type', 'quantity', 'available_quantity', 'total_quantity'];
  }

  static sortableBy(): string[] {
    return ['id', 'location_id', 'inventoryable_id', 'inventoryable_type', 'quantity', 'available_quantity', 'total_quantity'];
  }

  static searchableBy(): string[] {
    return [];
  }

  static $query(): any {
    // This is a placeholder for the Orion query builder
    return {
      get: () => Promise.resolve([]),
      find: (id: number) => Promise.resolve(new Inventory()),
    };
  }

  $resource(): string {
    return 'inventory';
  }
}
