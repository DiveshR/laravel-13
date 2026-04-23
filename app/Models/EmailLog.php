<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'campaign_id',
    'user_id',
    'email',
    'status',
    'error',
    'sent_at',
    'opened_at',
    'clicked_at',
])]
class EmailLog extends Model
{
    use HasFactory;

    /**
     * Casts
     */
    protected function casts(): array
    {
        return [
            'sent_at'    => 'datetime',
            'opened_at'  => 'datetime',
            'clicked_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helpers
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error' => $error,
        ]);
    }
}