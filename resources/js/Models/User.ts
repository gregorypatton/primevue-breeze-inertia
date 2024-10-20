import { Model } from './Model';

export class User extends Model {
  declare id: number;
  declare name: string;
  declare email: string;
  declare email_verified_at?: string;

  $attributes: {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
  };

  constructor(data?: Partial<User>) {
    super(data);
    this.$attributes = {
      id: this.id,
      name: this.name,
      email: this.email,
      email_verified_at: this.email_verified_at
    };
  }

  // Implement Orion-specific query methods
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

  static $query(): any {
    // This is a placeholder for the Orion query builder
    return {
      get: () => Promise.resolve([]),
      find: (id: number) => Promise.resolve(new User()),
    };
  }
}
