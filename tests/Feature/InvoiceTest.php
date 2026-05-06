<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder']);
    }

    public function test_can_create_invoice_with_items()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $manager = User::factory()->create(['role' => 'manager']);
        
        Sanctum::actingAs($manager);

        $invoiceData = [
            'patient_id' => $patient->id,
            'tax_rate' => 15,
            'due_date' => now()->addDays(7)->toDateString(),
            'payment_method' => 'cash',
            'notes' => 'فاتورة اختبار',
            'items' => [
                [
                    'item_type' => 'consultation',
                    'description' => 'استشارة طبية',
                    'quantity' => 1,
                    'unit_price' => 200,
                ],
                [
                    'item_type' => 'lab_test',
                    'description' => 'تحليل دم كامل',
                    'quantity' => 1,
                    'unit_price' => 150,
                ],
            ],
        ];

        $response = $this->postJson('/api/invoices', $invoiceData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'invoice_number',
                    'patient_id',
                    'total_amount',
                    'tax_amount',
                    'tax_rate',
                    'status',
                    'items' => [
                        '*' => [
                            'id',
                            'item_type',
                            'description',
                            'quantity',
                            'unit_price',
                            'total',
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseHas('invoices', [
            'patient_id' => $patient->id,
            'total_amount' => 402.5, // 350 + 15% tax
            'tax_amount' => 52.5,
            'tax_rate' => 15,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'item_type' => 'consultation',
            'description' => 'استشارة طبية',
            'quantity' => 1,
            'unit_price' => 200,
            'total' => 200,
        ]);
    }

    public function test_can_list_invoices_with_filters()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $manager = User::factory()->create(['role' => 'manager']);
        
        Sanctum::actingAs($manager);

        // Create invoices with different statuses
        Invoice::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'pending',
            'total_amount' => 200,
        ]);

        Invoice::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'paid',
            'total_amount' => 300,
        ]);

        // Test filtering by status
        $response = $this->getJson('/api/invoices?status=pending');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('pending', $data[0]['status']);

        // Test filtering by patient
        $response = $this->getJson("/api/invoices?patient_id={$patient->id}");
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_can_show_invoice_details()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $manager = User::factory()->create(['role' => 'manager']);
        
        Sanctum::actingAs($manager);

        $invoice = Invoice::factory()->create([
            'patient_id' => $patient->id,
            'total_amount' => 200,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'استشارة طبية',
            'total' => 200,
        ]);

        $response = $this->getJson("/api/invoices/{$invoice->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'invoice_number',
                    'patient' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'items' => [
                        '*' => [
                            'id',
                            'description',
                            'total',
                        ],
                    ],
                ],
            ]);
    }

    public function test_can_mark_invoice_as_paid()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $manager = User::factory()->create(['role' => 'manager']);
        
        Sanctum::actingAs($manager);

        $invoice = Invoice::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'pending',
            'total_amount' => 200,
        ]);

        $paymentData = [
            'payment_method' => 'cash',
            'transaction_id' => 'TXN123456',
            'notes' => 'دفع نقد',
        ];

        $response = $this->postJson("/api/invoices/{$invoice->id}/pay", $paymentData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $invoice->id,
                    'status' => 'paid',
                    'payment_method' => 'cash',
                ],
            ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
            'payment_method' => 'cash',
        ]);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 200,
            'payment_method' => 'cash',
            'transaction_id' => 'TXN123456',
            'notes' => 'دفع نقد',
        ]);
    }

    public function test_cannot_mark_paid_invoice_as_paid_again()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $manager = User::factory()->create(['role' => 'manager']);
        
        Sanctum::actingAs($manager);

        $invoice = Invoice::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'paid',
            'total_amount' => 200,
        ]);

        $response = $this->postJson("/api/invoices/{$invoice->id}/pay", [
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Invoice is already paid',
            ]);
    }

    public function test_can_update_pending_invoice()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $manager = User::factory()->create(['role' => 'manager']);
        
        Sanctum::actingAs($manager);

        $invoice = Invoice::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'pending',
            'tax_rate' => 0,
            'total_amount' => 200,
        ]);

        $updateData = [
            'tax_rate' => 15,
            'due_date' => now()->addDays(14)->toDateString(),
            'notes' => 'فاتورة محدثة',
            'items' => [
                [
                    'item_type' => 'consultation',
                    'description' => 'استشارة طبية محدثة',
                    'quantity' => 1,
                    'unit_price' => 250,
                ],
            ],
        ];

        $response = $this->putJson("/api/invoices/{$invoice->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'tax_rate' => 15,
            'notes' => 'فاتورة محدثة',
        ]);
    }

    public function test_cannot_update_paid_invoice()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $manager = User::factory()->create(['role' => 'manager']);
        
        Sanctum::actingAs($manager);

        $invoice = Invoice::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'paid',
            'total_amount' => 200,
        ]);

        $response = $this->putJson("/api/invoices/{$invoice->id}", [
            'notes' => 'محاولة تحديث',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Cannot update a paid invoice',
            ]);
    }

    public function test_can_delete_pending_invoice()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $manager = User::factory()->create(['role' => 'manager']);
        
        Sanctum::actingAs($manager);

        $invoice = Invoice::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'pending',
            'total_amount' => 200,
        ]);

        $response = $this->deleteJson("/api/invoices/{$invoice->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Invoice deleted successfully',
            ]);

        $this->assertDatabaseMissing('invoices', [
            'id' => $invoice->id,
        ]);
    }

    public function test_cannot_delete_paid_invoice()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $manager = User::factory()->create(['role' => 'manager']);
        
        Sanctum::actingAs($manager);

        $invoice = Invoice::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'paid',
            'total_amount' => 200,
        ]);

        $response = $this->deleteJson("/api/invoices/{$invoice->id}");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Cannot delete a paid invoice',
            ]);
    }

    public function test_invoice_auto_created_after_visit()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $manager = User::factory()->create(['role' => 'manager']);
        
        Sanctum::actingAs($manager);

        // Get the seeded doctor and clinic
        $doctorUser = User::where('role', 'doctor')->first();
        $doctor = \App\Models\Doctor::where('user_id', $doctorUser->id)->first();
        $clinic = \App\Models\Clinic::first();

        $visitData = [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'visited_at' => now()->toDateTimeString(),
            'diagnosis' => 'ألم في الرأس',
            'prescription' => [
                ['name' => 'باراسيتامول', 'dosage' => '500mg'],
            ],
        ];

        $response = $this->postJson('/api/visits', $visitData);
        $response->assertStatus(201);

        // Check if visit was created
        $this->assertDatabaseHas('visits', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        // Check if invoice was created by observer
        $this->assertDatabaseHas('invoices', [
            'patient_id' => $patient->id,
            'total_amount' => 200,
            'tax_rate' => 0,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'item_type' => 'consultation',
            'description' => 'استشارة طبية',
            'quantity' => 1,
            'unit_price' => 200,
            'total' => 200,
        ]);
    }

    public function test_invoice_number_is_sequential()
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $manager = User::factory()->create(['role' => 'manager']);
        
        Sanctum::actingAs($manager);

        // Create first invoice
        $response1 = $this->postJson('/api/invoices', [
            'patient_id' => $patient->id,
            'items' => [
                [
                    'item_type' => 'consultation',
                    'description' => 'استشارة طبية',
                    'quantity' => 1,
                    'unit_price' => 200,
                ],
            ],
        ]);

        // Create second invoice
        $response2 = $this->postJson('/api/invoices', [
            'patient_id' => $patient->id,
            'items' => [
                [
                    'item_type' => 'consultation',
                    'description' => 'استشارة طبية',
                    'quantity' => 1,
                    'unit_price' => 200,
                ],
            ],
        ]);

        $invoice1 = $response1->json('data');
        $invoice2 = $response2->json('data');

        $this->assertEquals('INV-0001', $invoice1['invoice_number']);
        $this->assertEquals('INV-0002', $invoice2['invoice_number']);
    }
}
