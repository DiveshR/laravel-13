<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Export\ExportProductsJob;
use App\Jobs\Export\ExportUsersJob;
use App\Models\Export;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function exportUsers(): RedirectResponse
    {
        $export = Export::create([
            'user_id' => Auth::id(),
            'type' => 'users',
            'status' => 'pending',
        ]);

        ExportUsersJob::dispatch($export->id);

        return back()->with('success', 'Users export started! You will receive an email with the download link shortly.');
    }

    public function exportProducts(): RedirectResponse
    {
        $export = Export::create([
            'user_id' => Auth::id(),
            'type' => 'products',
            'status' => 'pending',
        ]);

        ExportProductsJob::dispatch($export->id);

        return back()->with('success', 'Products export started! You will receive an email with the download link shortly.');
    }

    public function download(Export $export): BinaryFileResponse
    {
        if ($export->status !== 'completed' || !$export->file_path) {
            abort(404, 'Export not ready or not found.');
        }

        $fullPath = storage_path("app/{$export->file_path}");

        if (!file_exists($fullPath)) {
            abort(404, 'Export file not found.');
        }

        return response()->download($fullPath, "{$export->type}_{$export->id}.csv");
    }
}
