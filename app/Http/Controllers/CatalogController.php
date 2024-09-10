<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Catalog;
use Illuminate\Support\Facades\Log;
use App\Jobs\ConvertXlsmToXlsxJob;
use App\Jobs\ReadCategoryFileJob;

class CatalogController extends Controller
{
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categoryFile' => ['required', 'file'],
            'fnskuFile' => ['required', 'file'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $catalog = new Catalog();
        $catalog->channel = 'amazon';
        $catalog->save();

        $fileUrls = [];

        try {
            if ($request->hasFile('categoryFile')) {
                $categoryFile = $request->file('categoryFile');
                $categoryFileName = $catalog->id . '.xlsm';
                if ($request->hasFile('fnskuFile')) {
                    $fnskuFile = $request->file('fnskuFile');
                    $fnskuFileName = $catalog->id . '.txt';

                    $fnskuPath = $fnskuFile->storeAs('uploads/imports', $fnskuFileName); // Store in default disk
                    $fileUrls['fnskuFile'] = Storage::url($fnskuPath);
                }
                // Store the file in the local storage (storage/app/uploads)
                $categoryPath = $categoryFile->storeAs('uploads/imports', $categoryFileName); // Don't use "public" disk here
                $fileUrls['categoryFile'] = Storage::url($categoryPath);

                // Dispatch the job with the relative path
                dispatch(new ReadCategoryFileJob($categoryPath, $catalog));
                Log::info('Job dispatched with file path: ' . $categoryPath);
            }



            return response()->json([
                'message' => 'Files uploaded and catalog created successfully!',
                'catalogUuid' => $catalog->id,
                'fileUrls' => $fileUrls,
            ], 200);
        } catch (\Exception $e) {
            Log::error('File upload or job dispatch failed: ' . $e->getMessage());
            return response()->json(['message' => 'File upload failed'], 500);
        }
    }
}
