export abstract class Model {
  declare id: number;

  constructor(data?: Partial<Model>) {
    Object.assign(this, data);
  }

  static includes(): string[] {
    return [];
  }

  static filterableBy(): string[] {
    return [];
  }

  static sortableBy(): string[] {
    return [];
  }

  static searchableBy(): string[] {
    return [];
  }
}
