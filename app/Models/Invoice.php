<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['invoice_number', 'patient_id', 'visit_id', 'total_amount', 'tax_amount', 'tax_rate', 'status', 'due_date', 'paid_at', 'payment_method', 'notes'])]
class Invoice extends Model
{
    use HasFactory;

    protected $casts = [
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getRemainingAmountAttribute(): float
    {
        $paid = $this->payments()->sum('amount');
        return max(0, $this->total_amount - $paid);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid' || $this->remaining_amount <= 0;
    }
}
