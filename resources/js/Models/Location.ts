import { Model } from '@tailflow/laravel-orion/lib/model';
import { AddressDTO } from '../Interfaces/AddressDTO';

export class Location extends Model<{
  id: number;
  name: string;
  virtual_type: string | null;
  addresses: AddressDTO[];
  type: string;
  parent_id: number | null;
  supplier_id: number | null;
  created_at: string;
  updated_at: string;
}> {
  static $keyName = 'id';

  $resource(): string {
    return 'locations';
  }

  $init(): void {
    // Initialization logic if needed
  }
}
