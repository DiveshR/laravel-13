<?php

namespace App\Jobs\Export;

use App\Mail\ExportReady;
use App\Models\Export;
use App\Services\Export\CsvExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ExportUsersJob implements ShouldQueue
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
        Log::info("[ExportUsersJob] Starting export for export ID: {$this->exportId}");

        $export = Export::findOrFail($this->exportId);

        $export->update(['status' => 'processing']);
        Log::info("[ExportUsersJob] Status set to processing");

        $filePath = "exports/users_{$export->id}.csv";

        try {
            app(CsvExportService::class)->exportUsers($filePath);
            Log::info("[ExportUsersJob] CSV file written to: {$filePath}");

            $export->update([
                'status' => 'completed',
                'file_path' => $filePath,
            ]);
            Log::info("[ExportUsersJob] Status set to completed");

            $recipient = $export->user;
            Log::info("[ExportUsersJob] Sending email to: {$recipient->email}");

            Mail::to($recipient)->send(new ExportReady($export));
            Log::info("[ExportUsersJob] Email sent successfully to: {$recipient->email}");
        } catch (\Throwable $e) {
            Log::error("[ExportUsersJob] Failed: {$e->getMessage()}", [
                'exception' => $e,
                'export_id' => $this->exportId,
            ]);
            $export->update(['status' => 'failed']);
            throw $e;
        }
    }
}
