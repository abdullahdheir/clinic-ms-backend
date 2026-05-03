<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user and assign a role.
     * 
     * @param Request $request The registration request.
     * @return JsonResponse User details and auth token.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:super_admin,manager,doctor,receptionist,patient',
            'phone' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'national_id' => $request->national_id,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
        ]);

        // Assign role using Spatie
        $user->assignRole($request->role);

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->createdResponse([
            'user' => $user->load('roles', 'permissions'),
            'token' => $token,
        ], 'User registered successfully');
    }

    /**
     * Authenticate user and return token.
     * 
     * @param Request $request The login request.
     * @return JsonResponse User details and auth token.
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->successResponse([
            'user' => $user->load('roles', 'permissions'),
            'token' => $token,
        ], 'Logged in successfully');
    }

    /**
     * Logout user and revoke current token.
     * 
     * @param Request $request The authenticated request.
     * @return JsonResponse Success message.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Get authenticated user details with roles and permissions.
     * 
     * @param Request $request The authenticated request.
     * @return JsonResponse User data.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse($request->user()->load('roles', 'permissions'));
    }
}
