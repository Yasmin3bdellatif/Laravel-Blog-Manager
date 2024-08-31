<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Vonage\Client\Credentials\Basic;
use Vonage\Client;
//use Vonage\Verify\Request;
use Vonage\Exceptions\VonageException;

class UserController extends Controller
{
    // Register Method
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verificationCode = random_int(100000, 999999);

        $user = User::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'verification_code' => $verificationCode,
            'is_verified' => false,
        ]);

//        // Initialize Vonage Client
//        $basic  = new Basic(env('VONAGE_API_KEY'), env('VONAGE_API_SECRET'));
//        $client = new Client($basic);
//
//        // Send Verification Code
//       // $request = new VerifyRequest($user->phone_number, "Your verification code is: {$verificationCode}");
//        try {
//            // Start Verification
//            $request = new \Vonage\Verify\Request($user->phone_number, "Vonage");
//            $response = $client->verify()->start($request);
//
//            Log::info("Started verification, `request_id` is " . $response->getRequestId());
//
//        } catch (\Vonage\Exceptions\Request $e) {
//            Log::error('Vonage Request Error: ' . $e->getMessage());
//            return response()->json(['message' => 'Failed to send verification code'], 500);
//        } catch (\Exception $e) {
//            Log::error('General Error: ' . $e->getMessage());
//            return response()->json(['message' => 'Failed to send verification code'], 500);
//        }

        Log::info("Verification code for {$user->phone_number}: {$verificationCode}");


        return response()->json([
            'user' => $user,
           // 'token' => $user->createToken('API Token')->plainTextToken,
        ]);
    }



    // Login Method
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->is_verified) {
            return response()->json(['message' => 'Account not verified'], 403);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('API Token')->plainTextToken,
        ]);
    }

    // Verify Code Method
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'verification_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

       /* // Initialize Vonage Client
        $basic  = new Basic(env('VONAGE_API_KEY'), env('VONAGE_API_SECRET'));
        $client = new Client($basic);

        // Verify Code
        try {
            $result = $client->verify()->check($request->verification_code, $request->phone_number);
            if ($result->getStatus() == 0) { // Status 0 means success
                $user = User::where('phone_number', $request->phone_number)
                    ->where('verification_code', $request->verification_code)
                    ->first();

                if (!$user) {
                    return response()->json(['message' => 'Invalid code'], 400);
                }

                $user->is_verified = true;
                $user->verification_code = null;
                $user->save();

                return response()->json(['message' => 'Account verified successfully']);
            } else {
                return response()->json(['message' => 'Invalid code'], 400);
            }
        } catch (\Exception $e) {
            Log::error("Error while verifying code: " . $e->getMessage());
            return response()->json(['message' => 'Verification failed'], 500);
        }*/
        $user = User::where('phone_number', $request->phone_number)
            ->where('verification_code', $request->verification_code)
            ->first();

        // If the user or code is not found, return an error
        if (!$user) {
            return response()->json(['message' => 'Invalid verification code'], 400);
        }

        // Mark the user as verified and clear the verification code
        $user->is_verified = true;
        $user->verification_code = null;
        $user->save();

        return response()->json(['message' => 'Account verified successfully']);
    }
}
