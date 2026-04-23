<?php

namespace App\Services\Export;

use App\Models\Product;
use App\Models\User;

class CsvExportService
{
    public function exportUsers(string $filePath)
    {
        $handle = fopen(storage_path("app/{$filePath}"), 'w');

        // Header
        fputcsv($handle, ['ID', 'Name', 'Email']);

        User::chunk(1000, function ($users) use ($handle) {

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                ]);
            }
        });

        fclose($handle);
    }

    public function exportProducts(string $filePath)
    {
        $handle = fopen(storage_path("app/{$filePath}"), 'w');

        // Header
        fputcsv($handle, ['ID', 'Name', 'Desciption']);

        Product::chunk(1000, function ($products) use ($handle) {
            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->id,
                    $product->name,
                    $product->description,
                ]);
            }
        });

        fclose($handle);
    }
}
