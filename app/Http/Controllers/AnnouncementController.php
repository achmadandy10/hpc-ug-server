<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Jobs\AnnouncementEmailJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'subject' => [
                    'required',
                ],
                'announcement' => [
                    'required',
                ],
            ]
        );

        if ($validate->fails()) {
            $data = [
                'validation_errors' => $validate->errors()
            ];

            return ResponseFormatter::validation_error('validation_errors', $data);
        }

        $user = User::whereIn('role', [4, 5])
            ->with('user_profile')
            ->get();

        foreach ($user as $row) {
            $checkLastName = $row->user_profile->last_name === null ? "" : " ".$row->user_profile->last_name;
            $details = [
                "subject" => $request->subject,
                "body" => $request->announcement,
                "name" => $row->user_profile->first_name . $checkLastName,
                "email" => $row->email
            ];

            dispatch(new AnnouncementEmailJob($details));
        }

        return ResponseFormatter::success('Announcement success send!');
    }
}
