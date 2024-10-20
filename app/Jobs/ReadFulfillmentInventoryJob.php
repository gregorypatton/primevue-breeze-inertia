<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Catalog;
use App\Models\Product;
use Illuminate\Support\Facades\File;

class ReadFulfillmentInventoryJob implements ShouldQueue
{
    use Queueable;
    public string $fnskuFilePath;
    public ?Catalog $catalog;
    public $stats = [];

    // Add counters for tracking
    private $updatedCount = 0;
    private $skippedCount = 0;
    private $notFoundCount = 0;

    public function __construct(string $fnskuFilePath, Catalog $catalog)
    {
        $this->fnskuFilePath = $fnskuFilePath;
        $this->catalog = $catalog;
    }

    public function handle()
    {
        $stats = $this->stats;
        try {
            // Get the file path
            $txtFullPath = Storage::path($this->fnskuFilePath);

            // Open the file for reading
            $file = fopen($txtFullPath, "r");
            if (!$file) {
                throw new Exception('Unable to open file: ' . $txtFullPath);
            }

            // Read the header row
            $headers = fgetcsv($file, 0, "\t");

            // Loop through each row of the file
            while (($row = fgetcsv($file, 0, "\t")) !== false) {
                // Combine header and row to create associative array
                $rowData = array_combine($headers, $row);

                if ($rowData === false) {
                    Log::error('Invalid data format in file: ' . $txtFullPath);
                    continue;
                }

                // Extract relevant columns
                $sellerSku = $rowData['seller-sku'] ?? null;
                $fulfillmentChannelSku = $rowData['fulfillment-channel-sku'] ?? null;

                if ($sellerSku && $fulfillmentChannelSku) {
                    $product = Product::where('sku', $sellerSku)->first();

                    if ($product) {

                        if ($product->brand !== 'ResOne') {
                            if ($product->catalog() == $this->catalog) {
                                $product->sellable_id = $fulfillmentChannelSku;

                                $product->save();

                                $this->updatedCount++;
                            }
                        } else {
                            $this->skippedCount++;
                        }
                    } else {
                        $this->notFoundCount++;
                    }
                } else {
                    Log::warning("Missing seller-sku or fulfillment-channel-sku in row: " . json_encode($rowData));
                }
            }

            fclose($file);
            $results = [
                'Product sellable_id updated' => $this->updatedCount,
                'Products skipped (ResOne)' => $this->skippedCount,
                'Products not found' => $this->notFoundCount
            ];
            // Log final stats
            Log::info('File processing completed.');
            Log::info(json_encode($results));
        } catch (Exception $e) {
            Log::error('Error processing file: ' . $e->getMessage());
            throw $e;
        }
    }
}
