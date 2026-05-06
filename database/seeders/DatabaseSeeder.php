<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * @return void
     */
    public function run(): void
    {
        // Create Roles
        $roles = ['super_admin', 'manager', 'doctor', 'patient', 'receptionist'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }
        
        // Create Super Admin User
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@clinic.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        // Create Users
        $managerUser = User::firstOrCreate(
            ['email' => 'manager@clinic.com'],
            [
                'name' => 'Clinic Manager',
                'password' => Hash::make('password'),
            ]
        );
        $managerUser->assignRole('manager');

        $doctorUser = User::firstOrCreate(
            ['email' => 'doctor@clinic.com'],
            [
                'name' => 'Dr. Smith',
                'password' => Hash::make('password'),
            ]
        );
        $doctorUser->assignRole('doctor');

        $patientUser = User::firstOrCreate(
            ['email' => 'patient@clinic.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
            ]
        );
        $patientUser->assignRole('patient');

        // Create Clinic
        $clinic = Clinic::create([
            'name' => 'Central Health Clinic',
            'address' => '123 Medical St, Health City',
            'phone' => '123-456-7890',
            'manager_id' => $managerUser->id,
            'is_active' => true,
        ]);

        // Create Departments
        $cardiology = Department::create([
            'clinic_id' => $clinic->id,
            'name' => 'Cardiology',
            'specialty' => 'Heart Care',
            'max_capacity' => 15,
        ]);

        $pediatrics = Department::create([
            'clinic_id' => $clinic->id,
            'name' => 'Pediatrics',
            'specialty' => 'Child Care',
            'max_capacity' => 20,
        ]);

        // Create Doctor Profile
        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'department_id' => $cardiology->id,
            'bio' => 'Specialist in cardiology with 10 years experience.',
            'specialization' => 'Cardiologist',
            'session_duration_minutes' => 30,
            'consultation_fee' => 100.00,
        ]);

        // Create Medical Record
        MedicalRecord::firstOrCreate(
            ['patient_id' => $patientUser->id],
            [
                'blood_type' => 'O+',
                'chronic_diseases' => ['Hypertension'],
                'allergies' => ['Peanuts'],
                'emergency_contact' => 'Jane Doe: 987-654-3210',
                'notes' => 'Patient requires regular checkups.',
            ]
        );
    }
}
