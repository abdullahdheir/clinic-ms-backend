<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'manager', 'doctor', 'receptionist', 'patient'])->default('patient')->after('email');
            $table->string('national_id')->nullable()->after('role');
            $table->date('date_of_birth')->nullable()->after('national_id');
            $table->enum('gender', ['male', 'female'])->nullable()->after('date_of_birth');
            $table->string('phone')->nullable()->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'national_id', 'date_of_birth', 'gender', 'phone']);
        });
    }
};
