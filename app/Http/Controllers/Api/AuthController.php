<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\EmailForgotPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User; // Import your User model
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * @OA\PathItem(
 *     path="/api/auth"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Login Process",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="your_auth_token")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required',
                'password' => 'required',
            ]);
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'success' => true,
                    'message' => 'credential not found',
                    'token' => null,
                    'data' => null
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'success',
                'token' => $user->createToken('token')->plainTextToken,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Register Process",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "foto"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret"),
     *             @OA\Property(property="foto", type="string", format="base64", example="base64encodedstring")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful registration",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registration successful"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid data",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid data")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => 'required',
            'email' => 'required',
            'password' => 'required',
            'foto' => 'required',
            'nohp' => 'required'
        ]);

        // Decode the base64 image
        $foto = $request->input('foto');
        $foto = str_replace('data:image/png;base64,', '', $foto);
        $foto = str_replace('data:image/jpg;base64,', '', $foto);
        $foto = str_replace('data:image/jpeg;base64,', '', $foto);
        $foto = str_replace(' ', '+', $foto);
        $fotoName = Str::slug($data['username']) . '_' . time() . '.png';

        // Save the image to public folder
        $filePath = public_path('user_profile/' . $fotoName);
        file_put_contents($filePath, base64_decode($foto));

        // Create the user
        $user = User::create([
            'name' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'foto' => 'user_profile/' . $fotoName,
            'nohp' => $data['nohp']
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => $user
            ]
        ], 201);
    }

    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function changepassword(Request $request)
    {
        try {
            $user = $request->user();
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'password tidak sama',
                ], 302);
            }
            User::where('id', '=', $user->id)->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'change password success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 200);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => "Logout Success"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 200);
        }
    }
    function generateFourDigitCode()
    {
        return str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function forgotpassword(Request $request)
    {
        try {
            $code = $this->generateFourDigitCode();
            $account = User::where('email', '=', $request->email)->first();
            $account->update([
                'code_verify' => $code
            ]);
            if ($account) {
                Mail::to($request->email)->send(new EmailForgotPassword($code, $account->email));
                return response()->json([
                    'success' => true,
                    'message' => "success"
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "failed"
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login_with_code(Request $request)
    {
        try {
            $account = User::where('code_verify', '=', $request->code)->first();
            $user = User::where('email','=',$account->email)->first();
            return response()->json([
                'success' => true,
                'message' => 'success',
                'token' => $user->createToken('token')->plainTextToken,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
