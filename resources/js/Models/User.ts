import { Model } from '@tailflow/laravel-orion/lib/model';

export class User extends Model<{
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
}> {
  $resource(): string {
    return 'users';
  }

  static includes(): string[] {
    return [];
  }

  static filterableBy(): string[] {
    return ['id', 'name', 'email', 'email_verified_at'];
  }

  static sortableBy(): string[] {
    return ['id', 'name', 'email', 'email_verified_at'];
  }

  static searchableBy(): string[] {
    return ['name', 'email'];
  }
}
