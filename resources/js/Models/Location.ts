import { Model } from './Model';
import { Supplier } from './Supplier';
import { Inventory } from './Inventory';
import { PurchaseOrder } from './PurchaseOrder';
import { AddressDTO } from '../Interfaces/AddressDTO';

export class Location extends Model {
  declare id: number;
  declare name: string;
  declare type: string;
  declare parent_id: number | null;
  declare supplier_id: number | null;
  declare address: AddressDTO | null;

  // Relationships
  declare parent?: Location;
  declare children?: Location[];
  declare supplier?: Supplier;
  declare inventory?: Inventory[];
  declare purchaseOrders?: PurchaseOrder[];
  declare billToPurchaseOrders?: PurchaseOrder[];
  declare supplierPurchaseOrders?: PurchaseOrder[];
  declare shipFromPurchaseOrders?: PurchaseOrder[];

  // Methods
  static getValidTypes(): string[] {
    return ['warehouse', 'store', 'supplier', 'customer'];
  }

  static getValidVirtualTypes(): string[] {
    return ['supplier', 'customer'];
  }

  validateHierarchy(): boolean {
    // Implement validation logic here
    return true;
  }

  // Implement Orion-specific query methods
  static includes(): string[] {
    return ['parent', 'children', 'supplier', 'inventory', 'purchaseOrders'];
  }

  static filterableBy(): string[] {
    return ['id', 'name', 'type', 'parent_id', 'supplier_id'];
  }

  static sortableBy(): string[] {
    return ['id', 'name', 'type', 'parent_id', 'supplier_id'];
  }

  static searchableBy(): string[] {
    return ['name'];
  }
}
