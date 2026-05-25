<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EmailVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email:rfc,filter', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('The provided credentials are incorrect.'),
            ]);
        }

        if (EmailVerification::isRequired() && ! $user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => __('Please verify your email before creating an API token.'),
            ]);
        }

        $deviceName = $validated['device_name'] ?? 'internal-api';

        // Sanctum only shows the plaintext token once; clients must store it securely.
        $token = $user->createToken($deviceName, ['internal-api'])->plainTextToken;

        return response()->json([
            'token_type' => 'Bearer',
            'token' => $token,
        ], 201);
    }
}
