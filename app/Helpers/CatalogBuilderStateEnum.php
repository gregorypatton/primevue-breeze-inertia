<?php

namespace App\Helpers;

enum CatalogBuilderStateEnum: int
{
    case INITIALIZED = 0;
    case XLSM_UPLOADED = 1;
    case XLSM_CONVERTED = 2;
    case TXT_UPLOADED = 3;
    case IMPORT_READY = 4;
    case IMPORTED = 5;
}
