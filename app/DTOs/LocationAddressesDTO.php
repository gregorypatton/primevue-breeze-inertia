<?php

namespace App\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

/** @typescript */
class LocationAddressesDTO extends Data
{
    public function __construct(
        #[DataCollectionOf(AddressDTO::class)]
        public ?DataCollection $billTo = null,

        #[DataCollectionOf(AddressDTO::class)]
        public ?DataCollection $shipFrom = null,

        #[DataCollectionOf(AddressDTO::class)]
        public ?DataCollection $shipTo = null,

        #[DataCollectionOf(AddressDTO::class)]
        public ?DataCollection $other = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            billTo: isset($data['bill_to']) ? AddressDTO::collection($data['bill_to']) : null,
            shipFrom: isset($data['ship_from']) ? AddressDTO::collection($data['ship_from']) : null,
            shipTo: isset($data['ship_to']) ? AddressDTO::collection($data['ship_to']) : null,
            other: isset($data['other']) ? AddressDTO::collection($data['other']) : null
        );
    }

    public function toArray(): array
    {
        return [
            'bill_to' => $this->billTo?->toArray(),
            'ship_from' => $this->shipFrom?->toArray(),
            'ship_to' => $this->shipTo?->toArray(),
            'other' => $this->other?->toArray(),
        ];
    }
}
