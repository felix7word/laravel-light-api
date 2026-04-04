<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Authentication
 * 
 * APIs for user authentication
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     * 
     * @bodyParam name string required The user's name. Example: John Doe
     * @bodyParam email string required The user's email. Example: john@example.com
     * @bodyParam password string required The user's password. Example: password123
     * 
     * @response 200 {"access_token": "string", "token_type": "Bearer", "user": {"id": 1, "name": "John Doe", "email": "john@example.com", "email_verified_at": null, "created_at": "2026-04-05T00:00:00.000000Z", "updated_at": "2026-04-05T00:00:00.000000Z"}}
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 'User registered successfully');
    }

    /**
     * Login a user
     * 
     * @bodyParam email string required The user's email. Example: john@example.com
     * @bodyParam password string required The user's password. Example: password123
     * 
     * @response 200 {"access_token": "string", "token_type": "Bearer", "user": {"id": 1, "name": "John Doe", "email": "john@example.com", "email_verified_at": null, "created_at": "2026-04-05T00:00:00.000000Z", "updated_at": "2026-04-05T00:00:00.000000Z"}}
     * @response 422 {"message": "The provided credentials are incorrect.", "errors": {"email": ["The provided credentials are incorrect."]}}
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('The provided credentials are incorrect.', 422, [
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 'Login successful');
    }

    /**
     * Logout a user
     * 
     * @authenticated
     * 
     * @response 200 {"message": "Successfully logged out"}
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Successfully logged out');
    }

    /**
     * Get the current user
     * 
     * @authenticated
     * 
     * @response 200 {"id": 1, "name": "John Doe", "email": "john@example.com", "email_verified_at": null, "created_at": "2026-04-05T00:00:00.000000Z", "updated_at": "2026-04-05T00:00:00.000000Z"}
     */
    public function user(Request $request)
    {
        return $this->success($request->user(), 'User retrieved successfully');
    }
}
