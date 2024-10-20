<?php

namespace App\Helpers;

use BadFunctionCallException;
use Exception;
use Illuminate\Support\Facades\File;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Services\MacroRemovalService;
use App\Helpers\CatalogBuilderStateEnum;

class CatalogBuilder
{
    protected $catalogMimeType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
    public ?CatalogBuilderStateEnum $state;
    protected bool $macrosRemoved = false;

    public function __construct(
        public int $currentStep = 0,
        public array
        $productEntities = [],
        public string
        $catalogXlsxPath = '',
        public string
        $catalogXlsxmPath = '',
        public string $fbaTxtPath = ''
    ) {

        Debugbar::info("CatalogBuilder instantiated");
        $this->state = CatalogBuilderStateEnum::INITIALIZED;
        return $this;
    }
    public function processCatalog()
    {
        try {
            if ($this->state->value < 1 || !$this->macrosRemoved) {
                throw new Exception("Macros have not been removed.");
            }
            if (empty($this->catalogXlsxmPath)) {
                throw new Exception("XLSM Catalog path is not set.");
            }

            if (!File::exists($this->catalogXlsxmPath)) {
                throw new Exception("Catalog XLSM file not found.");
            }

            Debugbar::info("Processing Catalog XLSM file.");

            $macroRemover = new MacroRemovalService();

            $newXlsxPath = $macroRemover->removeMacros($this->catalogXlsxmPath);
            $this->macrosRemoved = true;
            $this->state = CatalogBuilderStateEnum::XLSM_CONVERTED;
            Debugbar::info("Macros removed. New XLSX file created at: " . $newXlsxPath);
        } catch (Exception $e) {
            Debugbar::error("Error processing catalog: " . $e->getMessage());
        } finally {
            $this->catalogXlsxmPath = null;
            $this->catalogXlsxPath = $newXlsxPath;
        }
    }
    public function setCatalogXlsxm(string $xlsxmPath)
    {
        if (File::exists($xlsxmPath) && File::type($xlsxmPath) == "xlsxm") {
            $this->catalogXlsxmPath = $xlsxmPath;

            Debugbar::info("XLSM Catalog path set at " . $xlsxmPath);
            return $this;
        } else {
            throw new Exception("Error loading Catalog XLSM.");
        }
    }

    public function setFbaTxt(string $fbaTxtPath)
    {
        if (File::exists($fbaTxtPath) && File::type($fbaTxtPath) == "txt") {
            $this->fbaTxtPath = $fbaTxtPath;

            Debugbar::info("FBA TXT path set at " . $fbaTxtPath);
            return $this;
        } else {
            throw new Exception("Error loading FBA TXT.");
        }
    }
    public function build()
    {
        try {
            if ($this->currentStep !== CatalogBuilderStateEnum::IMPORT_READY)
                throw new BadFunctionCallException("Builder instance not fully initialized. Builder state: " . $this->state->name);
        } catch (Exception $e) {

            Debugbar::warning("Exception thrown: " . $e->getMessage());
        }
    }
}
