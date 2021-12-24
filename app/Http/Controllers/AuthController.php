<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'first_name' => [
                    'required',
                    'string',
                    "regex:/^['\p{L}\s-]+$/u",
                ],
                'college' => [
                    'required',
                    'string',
                    "regex:/^['\p{L}\s-]+$/u",
                ],
                'email' => [
                    'required',
                    'string',
                    'email',
                    Rule::unique(User::class),
                ],
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised(),
                ],
                'password_confirmation' => [
                   'required' 
                ]
            ]
        );

        if ($validate->fails()) {
            $data = [
                'validation_errors' => $validate->errors()
            ];

            return ResponseFormatter::validation_error('validation_errors', $data);
        }

        try {
            $check_user = User::select('*')
                ->withTrashed()
                ->whereDate('created_at', '>=', date('Y-m-d') . ' 00:00:00')
                ->count();
            
            if ($check_user === 0) {
                $id = 'HPC' . date('dmy') . '0001';
            } else {
                $item = $check_user + 1;
                if ($item < 10) {
                    $id = 'HPC' . date('dmy') . '000' . $item;
                } elseif ($item >= 10 && $item <= 99) {
                    $id = 'HPC' . date('dmy') . '00' . $item;
                } elseif ($item >= 100 && $item <= 999) {
                    $id = 'HPC' . date('dmy') . '0' . $item;
                } elseif ($item >= 1000 && $item <= 9999) {
                    $id = 'HPC' . date('dmy') . $item;
                }
            }
            $user = User::create([
                'id' => $id,
                'role' => 5,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $profile = UserProfile::create([
                'user_id' => $id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'college' => $request->college,
            ]);

            $token = $user->createToken($user->email . '_token', ['server:user_external'])->plainTextToken;
            
            $data = [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ];

            return ResponseFormatter::success('Registered Successfully', $data);
        } catch (QueryException $error) {
            $data = [
                'error' => $error
            ];

            return ResponseFormatter::error(500, 'Query Error', $data);
        }
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ResponseFormatter::validation_error('Authentication Failed');
        }

        if ($user->role === 1) {
            $token = $user->createToken($user->email . '_token', ['server:admin_content'])->plainTextToken;
        } else if ($user->role === 2) {
            $token = $user->createToken($user->email . '_token', ['server:admin_proposal_submission'])->plainTextToken;
        } else if ($user->role === 3) {
            $token = $user->createToken($user->email . '_token', ['server:admin_super'])->plainTextToken;
        } else if ($user->role === 4) {
            $token = $user->createToken($user->email . '_token', ['server:user_internal'])->plainTextToken;
        } else if ($user->role === 5) {
            $token = $user->createToken($user->email . '_token', ['server:user_external'])->plainTextToken;
        }

        $data = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ];

        return ResponseFormatter::success('Login Success', $data);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return ResponseFormatter::success('Token Revoked');
    }
}