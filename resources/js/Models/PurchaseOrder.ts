import { Model, Supplier, Location, PurchaseOrderPart, User } from './index';
import { AddressDTO } from '../Interfaces/AddressDTO';

export class PurchaseOrder extends Model {
  declare id: number;
  declare purchase_order_number: string;
  declare status: string;
  declare supplier_id: number;
  declare location_id: number;
  declare user_id: number;
  declare total_cost: number;

  // Relationships
  declare user?: User;
  declare supplier?: Supplier;
  declare location?: Location;
  declare purchaseOrderParts?: PurchaseOrderPart[];

  // Address properties
  declare billToAddress?: AddressDTO;
  declare shipFromAddress?: AddressDTO;
  declare shipToAddress?: AddressDTO;

  $attributes: {
    id: number;
    purchase_order_number: string;
    status: string;
    supplier_id: number;
    location_id: number;
    user_id: number;
    total_cost: number;
    billToAddress?: AddressDTO;
    shipFromAddress?: AddressDTO;
    shipToAddress?: AddressDTO;
  };

  constructor(data?: Partial<PurchaseOrder>) {
    super(data);
    this.$attributes = {
      id: this.id,
      purchase_order_number: this.purchase_order_number,
      status: this.status,
      supplier_id: this.supplier_id,
      location_id: this.location_id,
      user_id: this.user_id,
      total_cost: this.total_cost,
      billToAddress: this.billToAddress,
      shipFromAddress: this.shipFromAddress,
      shipToAddress: this.shipToAddress
    };
  }

  // Methods
  setStatus(status: string): void {
    this.status = status;
    this.$attributes.status = status;
  }

  calculateTotalCost(): number {
    if (this.purchaseOrderParts) {
      return this.purchaseOrderParts.reduce((total, part) => total + (part.total_cost || 0), 0);
    }
    return 0;
  }

  updateTotalCost(): void {
    this.total_cost = this.calculateTotalCost();
    this.$attributes.total_cost = this.total_cost;
  }

  isEditable(): boolean {
    return ['draft', 'pending'].includes(this.status);
  }

  // Implement Orion-specific query methods
  static includes(): string[] {
    return ['user', 'supplier', 'location', 'purchaseOrderParts'];
  }

  static filterableBy(): string[] {
    return ['id', 'purchase_order_number', 'status', 'supplier_id', 'location_id', 'user_id', 'total_cost'];
  }

  static sortableBy(): string[] {
    return ['id', 'purchase_order_number', 'status', 'supplier_id', 'location_id', 'user_id', 'total_cost'];
  }

  static searchableBy(): string[] {
    return ['purchase_order_number'];
  }

  static $query(): any {
    // This is a placeholder for the Orion query builder
    return {
      get: () => Promise.resolve([]),
      find: (id: number) => Promise.resolve(new PurchaseOrder()),
      // Add more methods as needed
    };
  }
}
