<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a paginated list of invoices with optional filtering.
     * 
     * @queryParam patient_id integer Filter by patient ID
     * @queryParam status string Filter by invoice status (pending, paid, cancelled)
     * @queryParam from_date date Filter invoices created from this date
     * @queryParam to_date date Filter invoices created until this date
     * 
     * @response {
     *   "data": [...],
     *   "meta": {
     *     "current_page": 1,
     *     "per_page": 20,
     *     "total": 100
     *   }
     * }
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['patient', 'visit', 'items']);

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'data' => $invoices->items(),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    /**
     * Display a specific invoice with all related data.
     * 
     * @urlParam id integer required Invoice ID
     * 
     * @response {
     *   "data": {
     *     "id": 1,
     *     "invoice_number": "INV-0001",
     *     "patient": {...},
     *     "visit": {...},
     *     "items": [...],
     *     "payments": [...]
     *   }
     * }
     */
    public function show($id)
    {
        $invoice = Invoice::with(['patient', 'visit.doctor.user', 'items', 'payments'])->findOrFail($id);

        return response()->json([
            'data' => $invoice,
        ]);
    }

    /**
     * Create a new invoice with items.
     * 
     * @bodyParam patient_id integer required Patient ID
     * @bodyParam visit_id integer nullable Visit ID
     * @bodyParam tax_rate number nullable Tax rate (0-100)
     * @bodyParam due_date date nullable Due date
     * @bodyParam payment_method string nullable Payment method (cash, card, bank_transfer, insurance)
     * @bodyParam notes string nullable Invoice notes
     * @bodyParam items array required Invoice items
     * @bodyParam items.*.item_type string required Item type (consultation, lab_test, medication, procedure, other)
     * @bodyParam items.*.description string required Item description
     * @bodyParam items.*.quantity integer required Item quantity (min: 1)
     * @bodyParam items.*.unit_price number required Unit price (min: 0)
     * 
     * @response {
     *   "data": {
     *     "id": 1,
     *     "invoice_number": "INV-0001",
     *     "total_amount": 200.00,
     *     "status": "pending",
     *     "items": [...]
     *   }
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:users,id',
            'visit_id' => 'nullable|exists:visits,id',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'due_date' => 'nullable|date',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,insurance',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:consultation,lab_test,medication,procedure,other',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $lastInvoice = Invoice::orderBy('id', 'desc')->first();
            $nextNumber = $lastInvoice ? (int)str_replace('INV-', '', $lastInvoice->invoice_number) + 1 : 1;
            $invoiceNumber = 'INV-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $subtotal = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            $taxRate = $validated['tax_rate'] ?? 0;
            $taxAmount = $subtotal * ($taxRate / 100);
            $totalAmount = $subtotal + $taxAmount;

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'patient_id' => $validated['patient_id'],
                'visit_id' => $validated['visit_id'] ?? null,
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'tax_rate' => $taxRate,
                'status' => 'pending',
                'due_date' => $validated['due_date'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => $item['item_type'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return response()->json([
                'data' => $invoice->load('items'),
            ], 201);
        });
    }

    /**
     * Update an existing invoice (only pending invoices can be updated).
     * 
     * @urlParam id integer required Invoice ID
     * @bodyParam tax_rate number nullable Tax rate (0-100)
     * @bodyParam due_date date nullable Due date
     * @bodyParam payment_method string nullable Payment method (cash, card, bank_transfer, insurance)
     * @bodyParam notes string nullable Invoice notes
     * @bodyParam items array nullable Updated invoice items
     * @bodyParam items.*.item_type string required Item type (consultation, lab_test, medication, procedure, other)
     * @bodyParam items.*.description string required Item description
     * @bodyParam items.*.quantity integer required Item quantity (min: 1)
     * @bodyParam items.*.unit_price number required Unit price (min: 0)
     * 
     * @response {
     *   "data": {
     *     "id": 1,
     *     "invoice_number": "INV-0001",
     *     "total_amount": 250.00,
     *     "status": "pending",
     *     "items": [...]
     *   }
     * }
     * @response 400 {
     *   "message": "Cannot update a paid invoice"
     * }
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->status === 'paid') {
            return response()->json([
                'message' => 'Cannot update a paid invoice',
            ], 400);
        }

        $validated = $request->validate([
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'due_date' => 'nullable|date',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,insurance',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|exists:invoice_items,id',
            'items.*.item_type' => 'required_with:items.*.id|in:consultation,lab_test,medication,procedure,other',
            'items.*.description' => 'required_with:items.*.id|string',
            'items.*.quantity' => 'required_with:items.*.id|integer|min:1',
            'items.*.unit_price' => 'required_with:items.*.id|numeric|min:0',
        ]);

        return DB::transaction(function () use ($invoice, $validated) {
            if (isset($validated['items'])) {
                $invoice->items()->delete();

                foreach ($validated['items'] as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_type' => $item['item_type'],
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total' => $item['quantity'] * $item['unit_price'],
                    ]);
                }

                $subtotal = collect($validated['items'])->sum(function ($item) {
                    return $item['quantity'] * $item['unit_price'];
                });

                $taxRate = $validated['tax_rate'] ?? $invoice->tax_rate;
                $taxAmount = $subtotal * ($taxRate / 100);
                $totalAmount = $subtotal + $taxAmount;

                $invoice->update([
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                ]);
            }

            if (isset($validated['due_date'])) {
                $invoice->due_date = $validated['due_date'];
            }

            if (isset($validated['payment_method'])) {
                $invoice->payment_method = $validated['payment_method'];
            }

            if (isset($validated['notes'])) {
                $invoice->notes = $validated['notes'];
            }

            $invoice->save();

            return response()->json([
                'data' => $invoice->load('items'),
            ]);
        });
    }

    /**
     * Delete an invoice (only pending invoices can be deleted).
     * 
     * @urlParam id integer required Invoice ID
     * 
     * @response {
     *   "message": "Invoice deleted successfully"
     * }
     * @response 400 {
     *   "message": "Cannot delete a paid invoice"
     * }
     */
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->status === 'paid') {
            return response()->json([
                'message' => 'Cannot delete a paid invoice',
            ], 400);
        }

        $invoice->delete();

        return response()->json([
            'message' => 'Invoice deleted successfully',
        ]);
    }

    /**
     * Mark an invoice as paid and create payment record.
     * 
     * @urlParam id integer required Invoice ID
     * @bodyParam payment_method string required Payment method (cash, card, bank_transfer, insurance)
     * @bodyParam transaction_id string nullable Transaction ID
     * @bodyParam notes string nullable Payment notes
     * 
     * @response {
     *   "data": {
     *     "id": 1,
     *     "status": "paid",
     *     "paid_at": "2026-05-05T13:30:00.000000Z",
     *     "payments": [...]
     *   }
     * }
     * @response 400 {
     *   "message": "Invoice is already paid"
     * }
     */
    public function markAsPaid(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->status === 'paid') {
            return response()->json([
                'message' => 'Invoice is already paid',
            ], 400);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:cash,card,bank_transfer,insurance',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($invoice, $validated) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $validated['payment_method'],
            ]);

            $invoice->payments()->create([
                'amount' => $invoice->total_amount,
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $validated['transaction_id'] ?? null,
                'paid_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'data' => $invoice->load('payments'),
            ]);
        });
    }
}
