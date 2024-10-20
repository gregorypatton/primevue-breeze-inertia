<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;
use Shuchkin\SimpleXLSX;
use App\Models\Catalog;
use App\Models\Product;

class ReadCategoryFileJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public string $categoryFilePath;
    public ?Catalog $catalog;
    public $stats = [];

    private $batchSize = 500; // Process 500 rows at a time

    public function __construct(string $categoryFilePath, Catalog $catalog)
    {
        $this->categoryFilePath = $categoryFilePath;
        $this->catalog = $catalog;
    }

    public function handle()
    {
        $stats = $this->stats;
        try {
            $xlsmFullPath = Storage::path($this->categoryFilePath);
            $xlsx = SimpleXLSX::parse($xlsmFullPath); // macro-enabled xls is an old format

            if (!$xlsx) {
                Log::error(SimpleXLSX::parseError());
                throw new Exception('Failed to parse the XLSM file.');
            }

            $sheetNames = $xlsx->sheetNames();
            $sheetIndex = array_search('Template', $sheetNames);

            if ($sheetIndex === false) {
                throw new Exception('Invalid Category Listings File: "Template" sheet not found.');
            }

            $rows = $xlsx->readRowsEx($sheetIndex); //summon the generator

            $batch = [];
            $rowIndex = 0;

            $this->stats = [
                'errors' => 0,
                'rows' => 0,
                'active' => 0,
                'asin' => 0,
                'gtin' => 0
            ];

            foreach ($rows as $rowData) {
                $rowIndex++;

                if (empty($rowData)) {
                    $this->stats['errors']++;
                    continue;
                }


                $status = strtolower($rowData[0]['value'] ?? '');
                $title = $rowData[1]['value'] ?? '';
                $seller_sku = $rowData[3]['value'] ?? '';
                $brand = $rowData[4]['value'] ?? null;
                $image_url = $rowData[43]['value'] ?? '';
                $productId = $rowData[7]['value'] ?? '';

                $asinPattern = "/B[0-9]{2}[0-9A-Z]{7}|[0-9]{9}(X|0-9])/";
                if (preg_match($asinPattern, $productId)) {
                    $this->stats['asin']++;
                } else {
                    $this->stats['gtin']++;
                }

                if ($status === 'active' && !empty($productId)) {
                    $this->stats['active']++;
                    $batch[] = [
                        'sellable_id' => $productId,
                        'title' => $title,
                        'sku' => $seller_sku,
                        'brand' => $brand,
                        'image_path' => $image_url,
                        'catalog_id' => $this->catalog->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (count($batch) === $this->batchSize) { // batch check
                        $this->insertBatch($batch);
                        $batch = []; // Clear batch after insert
                    }
                } else {
                    $this->stats['errors']++;
                }

                $this->stats['rows']++;
            }


            if (!empty($batch)) {
                $this->insertBatch($batch);
                $fnskuPath = str_replace('xlsm', 'txt', $this->categoryFilePath);
                dispatch(new ReadFulfillmentInventoryJob($fnskuPath, $this->catalog));
            }

            Log::info('Processing complete', $this->stats);
        } catch (Exception $e) {
            Log::error('Error converting XLSM to XLSX: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Insert a batch of rows into the products table.
     *
     * @param array $batch
     * @return void
     */
    private function insertBatch(array $batch)
    {
        Product::insert($batch);
        Log::info('Inserted ' . count($batch) . ' rows into the products table.');
    }
}
