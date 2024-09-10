<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Catalog;
use App\Enums\BarcodeIDTypeEnum;
use Milon\Barcode\Facades\DNS1DFacade;
use App\Helpers\BarcodeValidator;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['sku', 'title', 'prep_detail_id', 'special_instructions', 'supplier', 'image_path'];


    protected $guarded = [];

    public function getSellableIdAttribute($value)
    {
        switch ($this->getBarcodeIdType()) {
            case BarcodeIDTypeEnum::GTIN:

                $gtin = ltrim($value, '0');
                if (BarcodeValidator::isValidGTIN($gtin)) {
                    return $gtin;
                }
                throw new \InvalidArgumentException('Invalid GTIN');

            case BarcodeIDTypeEnum::UPC:
                if (BarcodeValidator::isValidUPC($value)) {
                    return $value;
                }
                throw new \InvalidArgumentException('Invalid UPC');

            case BarcodeIDTypeEnum::FNSKU:
                return $value;

            default:
                return $value;
        }
    }

    function getBarcodeIdType(): ?BarcodeIDTypeEnum
    {
        $idPatterns = [
            BarcodeIDTypeEnum::FNSKU => '/^[A-Z0-9]{10}$/',
            BarcodeIDTypeEnum::GTIN => '/^\d{14}$/',
            BarcodeIDTypeEnum::UPC  => '/^\d{12}$/',
            BarcodeIDTypeEnum::EAN  => '/^\d{13}$/',
        ];

        foreach ($idPatterns as $barcodeIdType => $pattern) {
            if (preg_match($pattern, $this->sellable_id)) {
                return $barcodeIdType;
            }
        }

        return null;
    }

    public function barcode()
    {
        if ($this->id_type)
            return DNS1DFacade::getBarcodeHTML($this->sellableId, $this->id_type);
    }
}
