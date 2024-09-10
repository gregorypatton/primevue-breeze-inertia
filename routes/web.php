<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\HandleInertiaRequests;
use App\Helpers\CatalogBuilder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Bus;

Route::post('/catalogs/upload', [CatalogController::class, 'upload'])->name('catalog.upload');
Route::get('/catalog/batch-status/{batchId}', function ($batchId) {
    $batch = Bus::findBatch($batchId);

    if ($batch) {
        return response()->json([
            'status' => $batch->progress(), // Returns the progress (0 to 100)
            'finished' => $batch->finished(),
            'failedJobs' => $batch->failedJobs,
        ]);
    } else {
        return response()->json(['error' => 'Batch not found'], 404);
    }
});
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
