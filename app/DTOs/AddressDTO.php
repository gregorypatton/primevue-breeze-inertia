<?php

namespace App\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

/** @typescript */
class AddressDTO extends Data
{
    public function __construct(
        public ?string $address1 = null,
        public ?string $address2 = null,
        public ?string $city = null,
        public ?string $state_prov_code = null,
        public ?string $zip = null,
        public ?string $phone_number = null,
        public ?string $email_address = null
    ) {}

    public static function collection(array $data): DataCollection
    {
        return new DataCollection(AddressDTO::class, array_map(fn($item) => new self(...$item), $data));
    }
}
