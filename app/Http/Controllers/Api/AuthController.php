<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\EmailForgotPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
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

            // Ubah path foto jadi URL
            if ($user->foto) {
                $user->foto = url($user->foto);
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

    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => 'required',
            'email' => 'required',
            'password' => 'required',
            'foto' => 'required',
            'nohp' => 'required'
        ]);

        $foto = $request->input('foto');
        $foto = str_replace(['data:image/png;base64,', 'data:image/jpg;base64,', 'data:image/jpeg;base64,'], '', $foto);
        $foto = str_replace(' ', '+', $foto);
        $fotoName = Str::slug($data['username']) . '_' . time() . '.png';

        $filePath = public_path('user_profile/' . $fotoName);
        file_put_contents($filePath, base64_decode($foto));

        $user = User::create([
            'name' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'foto' => 'user_profile/' . $fotoName,
            'nohp' => $data['nohp']
        ]);

        // Ubah path foto jadi URL
        if ($user->foto) {
            $user->foto = url($user->foto);
        }

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

            // Ubah path foto jadi URL
            if ($user->foto) {
                $user->foto = url($user->foto);
            }

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
            $user = User::where('email', '=', $account->email)->first();

            // Ubah path foto jadi URL
            if ($user->foto) {
                $user->foto = url($user->foto);
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
}
