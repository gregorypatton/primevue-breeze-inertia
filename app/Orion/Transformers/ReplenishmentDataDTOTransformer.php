<?php

namespace App\Orion\Transformers;

use App\DTOs\ReplenishmentDataDTO;
use Orion\Http\Resources\Resource;

class ReplenishmentDataDTOTransformer extends Resource
{
    public function toArray($request)
    {
        /** @var ReplenishmentDataDTO $replenishmentData */
        $replenishmentData = $this->resource;

        return [
            'lead_days' => $replenishmentData->lead_days,
            'purchaseTerms' => $replenishmentData->purchaseTerms,
        ];
    }
}
