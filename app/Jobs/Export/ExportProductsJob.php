<?php

namespace App\Jobs\Export;

use App\Mail\ExportReady;
use App\Models\Export;
use App\Services\Export\CsvExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ExportProductsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $exportId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("[ExportProductsJob] Starting export for export ID: {$this->exportId}");

        $export = Export::findOrFail($this->exportId);

        $export->update(['status' => 'processing']);
        Log::info("[ExportProductsJob] Status set to processing");

        $filePath = "exports/products_{$export->id}.csv";

        try {
            app(CsvExportService::class)->exportProducts($filePath);
            Log::info("[ExportProductsJob] CSV file written to: {$filePath}");

            $export->update([
                'status' => 'completed',
                'file_path' => $filePath,
            ]);
            // Log::info("[ExportProductsJob] Status set to completed");

            $recipient = $export->user;
            // Log::info("[ExportProductsJob] Sending email to: {$recipient->email}");

            Mail::to($recipient)->send(new ExportReady($export));
            // Log::info("[ExportProductsJob] Email sent successfully to: {$recipient->email}");
Log::info('APP_URL: ' . config('app.url'));            
        } catch (\Throwable $e) {
            Log::error("[ExportProductsJob] Failed: {$e->getMessage()}", [
                'exception' => $e,
                'export_id' => $this->exportId,
            ]);
            $export->update(['status' => 'failed']);
            throw $e;
        }
    }
}
