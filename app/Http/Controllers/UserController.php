<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\AdminProfile;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function registerAdmin(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'first_name' => [
                    'required',
                    'string',
                    "regex:/^['\p{L}\s-]+$/u",
                ],
                'role' => [
                    'required',
                    'numeric',
                ],
                'email' => [
                    'required',
                    'string',
                    'email',
                    Rule::unique(User::class),
                ],
                'password' => [
                    'required',
                    Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised(),
                ],
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
            ->whereIn('role', [1, 2, 3])
            ->whereDate('created_at', '>=', date('Y-m-d') . ' 00:00:00')
                ->count();

            if ($check_user === 0) {
                $id = 'ADM' . date('dmy') . '0001';
            } else {
                $item = $check_user + 1;
                if ($item < 10) {
                    $id = 'ADM' . date('dmy') . '000' . $item;
                } elseif ($item >= 10 && $item <= 99) {
                    $id = 'ADM' . date('dmy') . '00' . $item;
                } elseif ($item >= 100 && $item <= 999) {
                    $id = 'ADM' . date('dmy') . '0' . $item;
                } elseif ($item >= 1000 && $item <= 9999) {
                    $id = 'ADM' . date('dmy') . $item;
                }
            }
                
            $user = User::create([
                'id' => $id,
                'role' => $request->role,
                'email' => $request->email,
                'email_verified_at' => date('now'),
                'password' => Hash::make($request->password),
            ]);

            AdminProfile::create([
                'user_id' => $id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
            ]);
            
            $data = [
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

    public function showAdmin($id)
    {
        $user = User::where('id', $id)
            ->with('admin_profile')
            ->first();

        $data = [
            'admin' => $user
        ];

        return ResponseFormatter::success('All Admin', $data);
    }

    public function showUser($id)
    {
        $user = User::where('id', $id)
            ->with('user_profile')
            ->first();

        $data = [
            'user' => $user
        ];

        return ResponseFormatter::success('All User', $data);
    }

    public function showAllAdmin()
    {
        $user = User::whereIn('role', [1, 2, 3])
            ->with('admin_profile')
            ->orderBy('id', 'DESC')->get();

        $data = [
            'admin' => $user
        ];

        return ResponseFormatter::success('All Admin', $data);
    }

    public function showAllUser()
    {
        $user = User::whereIn('role', [4, 5])
            ->with('user_profile')
            ->orderBy('id', 'DESC')->get();

        $data = [
            'user' => $user
        ];

        return ResponseFormatter::success('All User', $data);
    }

    public function updateAdmin(Request $request, $id)
    {
        $user = User::where('id', $id)
            ->with('admin_profile')
            ->first();

        $request->role === null ? $role = $user->role : $role = $request->role;
        $request->email === null ? $email = $user->email : $role = $request->email;
        $request->password === null ? $password = $user->password : $password = Hash::make($request->password);
        $request->first_name === null ? $first_name = $user->admin_profile->first_name : $first_name = $request->first_name;
        $request->last_name === null ? $last_name = $user->admin_profile->last_name : $last_name = $request->last_name;

        User::where('id', $id)
            ->update([
                'role' => $role,
                'email' => $email,
                'password' => $password,
            ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $newName = Str::random(40) . '.' . $extension;

            $file->storeAs('avatar', $newName, 'minio');
            $link = $newName;
        } else {
            $link = $user->admin_profile->avatar;
        }

        AdminProfile::where('user_id', $id)
            ->update([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'avatar' => $link
            ]);

        $data = [
            'user' => $user
        ];

        return ResponseFormatter::success('Success Update Admin', $data);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::where('id', $id)
            ->with('user_profile')
            ->first();

        $request->email === null ? $email = $user->email : $email = $request->email;
        $request->password === null ? $password = $user->password : $password = Hash::make($request->password);
        $request->first_name === null ? $first_name = $user->user_profile->first_name : $first_name = $request->first_name;
        $request->last_name === null ? $last_name = $user->user_profile->last_name : $last_name = $request->last_name;
        $request->college === null ? $college = $user->user_profile->college : $college = $request->college;

        User::where('id', $id)
            ->update([
                'email' => $email,
                'password' => $password,
            ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $newName = Str::random(40) . '.' . $extension;

            $file->storeAs('avatar', $newName, 'minio');
            $link = $newName;
        } else {
            $link = $user->user_profile->avatar;
        }

        UserProfile::where('user_id', $id)
            ->update([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'college' => $college,
                'avatar' => $link,
            ]);

        $data = [
            'user' => $user
        ];

        return ResponseFormatter::success('Success Update Users' , $data);
    }

    public function destroyAdmin($id)
    {
        $user = User::where('id', $id)
            ->whereIn('role', [1, 2, 3])
            ->with('admin_profile')
            ->first();

        $user->forceDelete();

        return ResponseFormatter::success('Success Delete Admin' . $id);
    }

    public function destroyUser($id)
    {
        $user = User::where('id', $id)
            ->whereIn('role', [4, 5])
            ->with('user_profile')
            ->first();

        $user->forceDelete();

        return ResponseFormatter::success('Success Delete User' . $id);
    }
}
