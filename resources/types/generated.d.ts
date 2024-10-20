declare namespace App.DTOs {
export type AddressDTO = {
address1: string | null;
address2: string | null;
city: string | null;
state_prov_code: string | null;
zip: string | null;
phone_number: string | null;
email_address: string | null;
};
export type IdentifierDTO = {
identifiers: Array<any>;
};
export type IdentifierData = {
type: string | null;
value: string | null;
};
export type LocationAddressesDTO = {
billTo: any | null;
shipFrom: any | null;
shipTo: any | null;
other: any | null;
};
export type ReplenishmentDataDTO = {
lead_days: number;
purchaseTerms: Array<any>;
};
export type SupplierAddressesDTO = {
billTo: any | null;
shipFrom: any | null;
shipTo: any | null;
returnTo: any | null;
};
}
declare namespace App.Enums {
export type DimensionType = 'box' | 'pallet' | 'individual_unit' | 'product' | 'packaging';
export type InventoryQuantityType = 'quantity_onhand' | 'quantity_allocated' | 'quantity_backordered' | 'quantity_reserved' | 'quantity_intransit';
export type PurchaseOrderStatus = 'draft' | 'submitted' | 'approved' | 'partially_received' | 'fully_received' | 'closed' | 'cancelled';
}
declare namespace App.Enums.Replenishment {
export type RecommendedAction = 'None' | 'Reorder' | 'Restock' | 'Hold';
}
declare namespace App.InventoryTransactions.Enums {
export type TransactionSourceType = 'purchase_order' | 'sales_order' | 'internal_transfer' | 'adjustment' | 'production' | 'return' | 'cycle_count';
export type TransactionType = 'adjustment' | 'transfer' | 'receipt' | 'issue' | 'return' | 'cycle_count' | 'allocate' | 'reserve' | 'backorder';
}
