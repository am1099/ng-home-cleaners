<?php

namespace App\Models;

use App\Enums\InvoiceDeliveryStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'invoice_id',
    'recipient_email',
    'status',
    'sent_at',
    'failed_at',
    'error_summary',
    'sent_by',
])]
class InvoiceDelivery extends Model
{
    protected function casts(): array
    {
        return [
            'status' => InvoiceDeliveryStatus::class,
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
