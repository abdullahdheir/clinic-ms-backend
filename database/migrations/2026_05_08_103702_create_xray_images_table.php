<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xray_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            $table->foreignId('visit_id')->nullable()->constrained('visits')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_type'); // صدر، رأس، أسنان، إلخ
            $table->string('file_path');
            $table->string('thumbnail_path')->nullable();
            $table->date('xray_date');
            $table->text('findings')->nullable(); // النتائج الفنية
            $table->text('impression')->nullable(); // الانطباع الطبي
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xray_images');
    }
};
