<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Part;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Manufacturer;
use App\Models\BillOfMaterial;
use App\Models\Dimension;
use App\Models\Gtin;
use App\Models\Location;
use App\Enums\DimensionType;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class RelationshipSeeder extends Seeder
{
    public function run()
    {
        $this->command->info("Starting to set relationships...");

        $this->setManufacturerRelationships();
        $this->setSupplierRelationships();
        $this->setLocationRelationships();
        $this->setPartRelationships();
        $this->setProductRelationships();
        $this->setBillOfMaterialRelationships();
        $this->setGtinRelationships();
        $this->setDimensions();

        $this->command->info("Relationship seeding completed.");
    }

    private function setManufacturerRelationships()
    {
        $this->command->info("Setting manufacturer relationships...");

        $legacyManufacturers = DB::connection('legacy')->table('manufacturer')->get();
        $successCount = 0;
        $failureCount = 0;

        foreach ($legacyManufacturers as $legacyManufacturer) {
            try {
                Manufacturer::updateOrCreate(
                    ['id' => $legacyManufacturer->man_id],
                    ['name' => $legacyManufacturer->man_name]
                );
                $successCount++;
            } catch (QueryException $e) {
                $failureCount++;
                Log::error("Database error setting manufacturer relationships for manufacturer ID {$legacyManufacturer->man_id}: " . $e->getMessage());
                Log::error("SQL: " . $e->getSql());
                Log::error("Bindings: " . implode(', ', $e->getBindings()));
            } catch (\Exception $e) {
                $failureCount++;
                Log::error("Error setting manufacturer relationships for manufacturer ID {$legacyManufacturer->man_id}: " . $e->getMessage());
                Log::error("Manufacturer data: " . json_encode($legacyManufacturer));
            }
        }

        $this->command->info("Manufacturer relationships: {$successCount} succeeded, {$failureCount} failed.");
    }

    private function setSupplierRelationships()
    {
        $this->command->info("Setting supplier relationships...");

        $legacySuppliers = DB::connection('legacy')->table('supplier')->get();
        $successCount = 0;
        $failureCount = 0;

        foreach ($legacySuppliers as $legacySupplier) {
            try {
                Supplier::updateOrCreate(
                    ['id' => $legacySupplier->supplier_id],
                    [
                        'name' => $legacySupplier->supplier_name,
                        'account_number' => $legacySupplier->account,
                        'payment_terms' => $legacySupplier->payment_terms,
                        'free_shipping_threshold_usd' => $legacySupplier->free_ship_min,
                        'contact' => json_encode([
                            'website' => $legacySupplier->website,
                            'phone' => $legacySupplier->phone,
                            'fax' => $legacySupplier->fax,
                        ]),
                    ]
                );
                $successCount++;
            } catch (QueryException $e) {
                $failureCount++;
                Log::error("Database error setting supplier relationships for supplier ID {$legacySupplier->supplier_id}: " . $e->getMessage());
                Log::error("SQL: " . $e->getSql());
                Log::error("Bindings: " . implode(', ', $e->getBindings()));
            } catch (\Exception $e) {
                $failureCount++;
                Log::error("Error setting supplier relationships for supplier ID {$legacySupplier->supplier_id}: " . $e->getMessage());
                Log::error("Supplier data: " . json_encode($legacySupplier));
            }
        }

        $this->command->info("Supplier relationships: {$successCount} succeeded, {$failureCount} failed.");
    }

    private function setLocationRelationships()
    {
        $this->command->info("Setting location relationships...");

        $legacyLocations = DB::connection('legacy')->table('location')->get();
        $successCount = 0;
        $failureCount = 0;

        foreach ($legacyLocations as $legacyLocation) {
            try {
                $location = Location::find($legacyLocation->location_id);
                if ($location) {
                    $locationType = $this->getLocationType($legacyLocation);
                    $location->type = $locationType['type'];
                    $location->virtual_type = $locationType['virtual_type'] ?? null;

                    if ($legacyLocation->supplier_id) {
                        $supplier = Supplier::firstOrCreate(['id' => $legacyLocation->supplier_id], ['name' => "Supplier {$legacyLocation->supplier_id}"]);
                        $location->supplier_id = $supplier->id;
                    }

                    if ($legacyLocation->parent_id) {
                        $parentLocation = Location::find($legacyLocation->parent_id);
                        if ($parentLocation) {
                            $location->parent_id = $parentLocation->id;
                        } else {
                            Log::warning("Parent location not found for location ID {$legacyLocation->location_id}, parent ID {$legacyLocation->parent_id}");
                        }
                    }

                    $location->save();
                    $successCount++;
                } else {
                    $failureCount++;
                    Log::error("Location not found for ID {$legacyLocation->location_id}");
                }
            } catch (QueryException $e) {
                $failureCount++;
                Log::error("Database error setting location relationships for location ID {$legacyLocation->location_id}: " . $e->getMessage());
                Log::error("SQL: " . $e->getSql());
                Log::error("Bindings: " . implode(', ', $e->getBindings()));
            } catch (\Exception $e) {
                $failureCount++;
                Log::error("Error setting location relationships for location ID {$legacyLocation->location_id}: " . $e->getMessage());
                Log::error("Location data: " . json_encode($legacyLocation));
            }
        }

        $this->command->info("Location relationships: {$successCount} succeeded, {$failureCount} failed.");
    }

    private function setPartRelationships()
    {
        $this->command->info("Setting part relationships...");

        $legacyParts = DB::connection('legacy')->table('part')->get();
        $successCount = 0;
        $failureCount = 0;

        foreach ($legacyParts as $legacyPart) {
            try {
                $part = Part::find($legacyPart->part_id);
                if ($part) {
                    $supplier = Supplier::firstOrCreate(['id' => $legacyPart->supplier_id], ['name' => "Supplier {$legacyPart->supplier_id}"]);
                    $manufacturer = Manufacturer::firstOrCreate(['id' => $legacyPart->man_id], ['name' => "Manufacturer {$legacyPart->man_id}"]);

                    $part->supplier_id = $supplier->id;
                    $part->manufacturer_id = $manufacturer->id;
                    $part->save();
                    $successCount++;
                } else {
                    $failureCount++;
                    Log::error("Part not found for ID {$legacyPart->part_id}");
                }
            } catch (QueryException $e) {
                $failureCount++;
                Log::error("Database error setting part relationships for part ID {$legacyPart->part_id}: " . $e->getMessage());
                Log::error("SQL: " . $e->getSql());
                Log::error("Bindings: " . implode(', ', $e->getBindings()));
            } catch (\Exception $e) {
                $failureCount++;
                Log::error("Error setting part relationships for part ID {$legacyPart->part_id}: " . $e->getMessage());
                Log::error("Part data: " . json_encode($legacyPart));
            }
        }

        $this->command->info("Part relationships: {$successCount} succeeded, {$failureCount} failed.");
    }

    private function setProductRelationships()
    {
        $this->command->info("Setting product relationships...");

        $legacyProducts = DB::connection('legacy')->table('product')->get();
        $successCount = 0;
        $failureCount = 0;

        foreach ($legacyProducts as $legacyProduct) {
            try {
                $product = Product::find($legacyProduct->product_id);
                if ($product) {
                    $manufacturer = Manufacturer::firstOrCreate(['id' => $legacyProduct->man_id], ['name' => "Manufacturer {$legacyProduct->man_id}"]);
                    $product->manufacturer_id = $manufacturer->id;
                    $product->save();
                    $successCount++;
                } else {
                    $failureCount++;
                    Log::error("Product not found for ID {$legacyProduct->product_id}");
                }
            } catch (QueryException $e) {
                $failureCount++;
                Log::error("Database error setting product relationships for product ID {$legacyProduct->product_id}: " . $e->getMessage());
                Log::error("SQL: " . $e->getSql());
                Log::error("Bindings: " . implode(', ', $e->getBindings()));
            } catch (\Exception $e) {
                $failureCount++;
                Log::error("Error setting product relationships for product ID {$legacyProduct->product_id}: " . $e->getMessage());
                Log::error("Product data: " . json_encode($legacyProduct));
            }
        }

        $this->command->info("Product relationships: {$successCount} succeeded, {$failureCount} failed.");
    }

    private function setBillOfMaterialRelationships()
    {
        $this->command->info("Setting bill of material relationships...");

        $legacyProductParts = DB::connection('legacy')->table('product_part')->get();
        $successCount = 0;
        $failureCount = 0;

        foreach ($legacyProductParts as $legacyProductPart) {
            try {
                $product = Product::find($legacyProductPart->product_id);
                $part = Part::find($legacyProductPart->part_id);

                if ($product && $part) {
                    BillOfMaterial::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'part_id' => $part->id,
                        ],
                        ['quantity_required' => $legacyProductPart->part_qty]
                    );
                    $successCount++;
                } else {
                    $failureCount++;
                    Log::warning("Product or Part not found for BOM: product_id={$legacyProductPart->product_id}, part_id={$legacyProductPart->part_id}");
                }
            } catch (QueryException $e) {
                $failureCount++;
                Log::error("Database error setting bill of material relationships for product ID {$legacyProductPart->product_id} and part ID {$legacyProductPart->part_id}: " . $e->getMessage());
                Log::error("SQL: " . $e->getSql());
                Log::error("Bindings: " . implode(', ', $e->getBindings()));
            } catch (\Exception $e) {
                $failureCount++;
                Log::error("Error setting bill of material relationships for product ID {$legacyProductPart->product_id} and part ID {$legacyProductPart->part_id}: " . $e->getMessage());
                Log::error("Product Part data: " . json_encode($legacyProductPart));
            }
        }

        $this->command->info("Bill of Material relationships: {$successCount} succeeded, {$failureCount} failed.");
    }

    private function setGtinRelationships()
    {
        $this->command->info("Setting GTIN relationships...");

        $legacyGtins = DB::connection('legacy')->table('gtin')->get();
        $successCount = 0;
        $failureCount = 0;

        foreach ($legacyGtins as $legacyGtin) {
            try {
                $product = Product::find($legacyGtin->seller_id);

                if ($product) {
                    Gtin::updateOrCreate(
                        ['gtin' => $legacyGtin->gtin_nbr],
                        [
                            'status' => $this->mapStatus($legacyGtin->status_id),
                            'lease_end_date' => $legacyGtin->last_change_ts !== '0000-00-00 00:00:00' ? $legacyGtin->last_change_ts : null,
                            'product_id' => $product->id,
                        ]
                    );
                    $successCount++;
                } else {
                    $failureCount++;
                    Log::warning("Product not found for GTIN: gtin={$legacyGtin->gtin_nbr}, seller_id={$legacyGtin->seller_id}");
                }
            } catch (QueryException $e) {
                $failureCount++;
                Log::error("Database error setting GTIN relationships for GTIN {$legacyGtin->gtin_nbr}: " . $e->getMessage());
                Log::error("SQL: " . $e->getSql());
                Log::error("Bindings: " . implode(', ', $e->getBindings()));
            } catch (\Exception $e) {
                $failureCount++;
                Log::error("Error setting GTIN relationships for GTIN {$legacyGtin->gtin_nbr}: " . $e->getMessage());
                Log::error("GTIN data: " . json_encode($legacyGtin));
            }
        }

        $this->command->info("GTIN relationships: {$successCount} succeeded, {$failureCount} failed.");
    }

    private function setDimensions()
    {
        $this->setProductDimensions();
        $this->setPartDimensions();
    }

    private function setProductDimensions()
    {
        $this->command->info("Setting product dimensions...");

        $legacyProducts = DB::connection('legacy')->table('product')->get();
        $successCount = 0;
        $failureCount = 0;

        foreach ($legacyProducts as $legacyProduct) {
            try {
                $product = Product::find($legacyProduct->product_id);
                if ($product && $legacyProduct->ship_length_in && $legacyProduct->ship_width_in && $legacyProduct->ship_height_in) {
                    $this->attachDimension($product, $legacyProduct->ship_length_in, $legacyProduct->ship_width_in, $legacyProduct->ship_height_in, DimensionType::PRODUCT);
                    $successCount++;
                } else {
                    $failureCount++;
                    Log::warning("Product not found or missing dimensions for product ID {$legacyProduct->product_id}");
                }
            } catch (QueryException $e) {
                $failureCount++;
                Log::error("Database error setting dimensions for product ID {$legacyProduct->product_id}: " . $e->getMessage());
                Log::error("SQL: " . $e->getSql());
                Log::error("Bindings: " . implode(', ', $e->getBindings()));
            } catch (\Exception $e) {
                $failureCount++;
                Log::error("Error setting dimensions for product ID {$legacyProduct->product_id}: " . $e->getMessage());
                Log::error("Product data: " . json_encode($legacyProduct));
            }
        }

        $this->command->info("Product dimensions: {$successCount} succeeded, {$failureCount} failed.");
    }

    private function setPartDimensions()
    {
        $this->command->info("Setting part dimensions...");

        $legacyParts = DB::connection('legacy')->table('part')->get();
        $successCount = 0;
        $failureCount = 0;

        foreach ($legacyParts as $legacyPart) {
            try {
                $part = Part::find($legacyPart->part_id);
                if ($part && isset($legacyPart->length) && isset($legacyPart->width) && isset($legacyPart->height)) {
                    $this->attachDimension($part, $legacyPart->length, $legacyPart->width, $legacyPart->height, DimensionType::INDIVIDUAL_UNIT);
                    $successCount++;
                } else {
                    $failureCount++;
                    Log::warning("Part not found or missing dimensions for part ID {$legacyPart->part_id}");
                }
            } catch (QueryException $e) {
                $failureCount++;
                Log::error("Database error setting dimensions for part ID {$legacyPart->part_id}: " . $e->getMessage());
                Log::error("SQL: " . $e->getSql());
                Log::error("Bindings: " . implode(', ', $e->getBindings()));
            } catch (\Exception $e) {
                $failureCount++;
                Log::error("Error setting dimensions for part ID {$legacyPart->part_id}: " . $e->getMessage());
                Log::error("Part data: " . json_encode($legacyPart));
            }
        }

        $this->command->info("Part dimensions: {$successCount} succeeded, {$failureCount} failed.");
    }

    private function attachDimension($model, $length, $width, $height, $type)
    {
        $dimension = Dimension::firstOrCreate(
            [
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'unit' => 'in',
                'type' => $type,
            ]
        );

        $model->dimensions()->syncWithoutDetaching([
            $dimension->id => ['dimensionable_type' => get_class($model)]
        ]);
    }

    private function mapStatus($legacyStatus)
    {
        $statusMap = [
            1 => 'active',
            2 => 'inactive',
        ];

        return $statusMap[$legacyStatus] ?? 'unknown';
    }

    private function getLocationType($legacyLocation)
    {
        if ($legacyLocation->buyer_ind) {
            return [
                'type' => Location::TYPE_VIRTUAL,
                'virtual_type' => Location::VIRTUAL_TYPE_BILL_TO
            ];
        } elseif ($legacyLocation->supplier_id) {
            return ['type' => Location::TYPE_SUPPLIER];
        } elseif ($legacyLocation->bill_to_ind) {
            return [
                'type' => Location::TYPE_VIRTUAL,
                'virtual_type' => Location::VIRTUAL_TYPE_BILL_TO
            ];
        } elseif ($legacyLocation->ship_to_ind) {
            return [
                'type' => Location::TYPE_VIRTUAL,
                'virtual_type' => Location::VIRTUAL_TYPE_SHIP_TO
            ];
        } else {
            return ['type' => Location::TYPE_VIRTUAL];
        }
    }
}
