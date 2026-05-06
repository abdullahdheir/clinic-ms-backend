<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

class VisitObserver
{
    /**
     * Handle the Visit "created" event.
     */
    public function created(Visit $visit): void
    {
        DB::transaction(function () use ($visit) {
            $lastInvoice = Invoice::orderBy('id', 'desc')->first();
            $nextNumber = $lastInvoice ? (int)str_replace('INV-', '', $lastInvoice->invoice_number) + 1 : 1;
            $invoiceNumber = 'INV-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $consultationFee = 200;
            $taxRate = 0;
            $taxAmount = 0;
            $totalAmount = $consultationFee;

            // Ensure patient_id is properly set
            $patientId = $visit->patient_id ?? $visit->appointment?->patient_id;
            
            if (!$patientId) {
                return; // Skip invoice creation if no patient is associated
            }

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'patient_id' => $patientId,
                'visit_id' => $visit->id,
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'tax_rate' => $taxRate,
                'status' => 'pending',
                'due_date' => now()->addDays(7),
                'payment_method' => null,
                'notes' => 'فاتورة استشارة طبية تلقائية',
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'consultation',
                'description' => 'استشارة طبية',
                'quantity' => 1,
                'unit_price' => $consultationFee,
                'total' => $consultationFee,
            ]);
        });
    }

    /**
     * Handle the Visit "updated" event.
     */
    public function updated(Visit $visit): void
    {
        //
    }

    /**
     * Handle the Visit "deleted" event.
     */
    public function deleted(Visit $visit): void
    {
        //
    }

    /**
     * Handle the Visit "restored" event.
     */
    public function restored(Visit $visit): void
    {
        //
    }

    /**
     * Handle the Visit "force deleted" event.
     */
    public function forceDeleted(Visit $visit): void
    {
        //
    }
}
