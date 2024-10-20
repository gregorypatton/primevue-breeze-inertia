<?php

namespace App\Orion\Transformers;

use App\DTOs\AddressDTO;
use Orion\Http\Resources\Resource;

class AddressDTOTransformer extends Resource
{
    public function toArray($request)
    {
        /** @var AddressDTO $address */
        $address = $this->resource;

        return [
            'address1' => $address->address1,
            'address2' => $address->address2,
            'city' => $address->city,
            'state_prov_code' => $address->state_prov_code,
            'zip' => $address->zip,
            'phone_number' => $address->phone_number,
            'email_address' => $address->email_address,
        ];
    }
}
