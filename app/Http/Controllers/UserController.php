<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function showAll()
    {
        $user = User::whereIn('role', [4, 5])
            ->with('user_profile')
            ->orderBy('id', 'DESC')->get();

        $data = [
            'user' => $user
        ];

        return ResponseFormatter::success('All User', $data);
    }
}
