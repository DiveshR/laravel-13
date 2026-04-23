<?php

namespace App\Actions\Export;

use App\Jobs\Export\ExportProductsJob;
use App\Jobs\Export\ExportUsersJob;
use App\Models\Export;

class StartExportAction
{
    public function execute(string $type)
    {
        $export = Export::create([
            'type' => $type,
            'status' => 'pending',
        ]);

        if ($type === 'users') {
            ExportUsersJob::dispatch($export->id);
        } else {
            ExportProductsJob::dispatch($export->id);
        }

        return $export;
    }
}
