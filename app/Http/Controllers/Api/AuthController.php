<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/register",
     * summary="Register a new user",
     * description="Registers a new user and returns a token upon successful registration.",
     * tags={"Authentication"},
     * @OA\RequestBody(
     * required=true,
     * description="User registration data",
     * @OA\JsonContent(
     * required={"name", "email", "password", "password_confirmation", "role"},
     * @OA\Property(property="name", type="string", example="Naufal Fadhilah"),
     * @OA\Property(property="email", type="string", format="email", example="naufal@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123"),
     * @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     * @OA\Property(property="role", type="string", example="seller", enum={"seller", "buyer"})
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="User registered successfully"
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error"
     * )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:buyer,seller'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * @OA\Post(
     * path="/api/login",
     * summary="Login a user",
     * description="Logs in a user and returns a token.",
     * tags={"Authentication"},
     * @OA\RequestBody(
     * required=true,
     * description="User login data",
     * @OA\JsonContent(
     * required={"email", "password"},
     * @OA\Property(property="email", type="string", format="email", example="naufal@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Login successful"
     * ),
     * @OA\Response(
     * response=422,
     * description="Invalid credentials"
     * )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }
    
    /**
     * @OA\Post(
     * path="/api/logout",
     * summary="Logout the authenticated user",
     * description="Invalidates the token of the currently authenticated user.",
     * tags={"Authentication"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Successfully logged out"
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}