<?php

namespace App\Services;

use ZipArchive;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class MacroRemovalService
{
    /**
     * Remove macros from the given .xlsm file by deleting vbaProject.bin.
     *
     * @param string $filePath Path to the uploaded .xlsm file.
     * @return string New file path of the .xlsx file without macros.
     * @throws \Exception if file operations fail.
     */
    public function removeMacros($filePath)
    {
        $zip = new ZipArchive;
        $tempDir = sys_get_temp_dir() . '/' . uniqid(); // System temporary directory
        Log::info("Temporary extraction dir: " . $tempDir);
        Log::info("Input file path: " . $filePath);

        if ($zip->open($filePath) === TRUE) {
            $zip->extractTo($tempDir);
            $zip->close(); // Ensure that the archive is closed properly
        } else {
            throw new Exception('Failed to open the .xlsm file.');
        }

        // Path to the vbaProject.bin file inside the unzipped folder
        $vbaFile = $tempDir . '/xl/vbaProject.bin';

        // Delete vbaProject.bin to remove macros
        if (file_exists($vbaFile)) {
            unlink($vbaFile); // Delete the macro file
            Log::info("vbaProject.bin file deleted to remove macros.");
        } else {
            Log::info("No vbaProject.bin file found; no macros to remove.");
        }

        // Generate a new temporary file path for the resulting .xlsx file
        $tempXlsxFilePath = sys_get_temp_dir() . '/' . uniqid() . '.xlsx';
        Log::info("Temporary .xlsx file path: " . $tempXlsxFilePath);

        // Recreate the file without macros by zipping the modified directory
        $this->zipFolder($tempDir, $tempXlsxFilePath);

        // Cleanup: Delete the temporary directory
        $this->deleteDirectory($tempDir);

        // Now move the .xlsx file to storage/app/uploads
        $finalXlsxFilePath = 'uploads/imports/' . uniqid() . '.xlsx';
        Storage::put($finalXlsxFilePath, file_get_contents($tempXlsxFilePath));

        // Ensure the temporary .xlsx file is closed and deleted
        if (file_exists($tempXlsxFilePath)) {
            unlink($tempXlsxFilePath); // Delete temp .xlsx file
            Log::info("Temporary .xlsx file deleted.");
        }

        Log::info("Final .xlsx file path: " . $finalXlsxFilePath);

        return $finalXlsxFilePath; // Return relative path to storage/app/uploads
    }

    /**
     * Zip the folder contents into a new file.
     *
     * @param string $folderPath Path to the folder to zip.
     * @param string $zipFilePath Path to save the zip file.
     * @throws \Exception if zipping fails.
     */
    private function zipFolder($folderPath, $zipFilePath)
    {
        $zip = new ZipArchive;

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($folderPath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                // Skip directories (they will be added automatically)
                if (!$file->isDir()) {
                    // Get the relative path of the file in the zip archive
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($folderPath) + 1);

                    // Add file to zip
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close(); // Ensure the zip archive is closed properly
            Log::info("Folder successfully zipped into: " . $zipFilePath);
        } else {
            throw new Exception('Failed to create the .xlsx file.');
        }
    }

    /**
     * Recursively delete a directory and its contents.
     *
     * @param string $dir Directory to delete.
     */
    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $dir . '/' . $file;
            (is_dir($filePath)) ? $this->deleteDirectory($filePath) : unlink($filePath);
        }

        rmdir($dir);
        Log::info("Temporary directory deleted: " . $dir);
    }
}
