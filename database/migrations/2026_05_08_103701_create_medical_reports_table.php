<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('visit_id')->nullable()->constrained('visits')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('report_type'); // تحليل دم، أشعة، تقرير طبي
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable(); // pdf, image
            $table->date('report_date');
            $table->text('results')->nullable();
            $table->text('recommendations')->nullable();
            $table->enum('status', ['pending', 'completed', 'reviewed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_reports');
    }
};
