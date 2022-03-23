<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Jobs\ApproveEmailJob;
use App\Jobs\RevisionEmailJob;
use App\Models\ProposalSubmission;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProposalSubmissionController extends Controller
{
    public function store(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'type_of_proposal' => [
                    'required',
                ],
                'phone_number' => [
                    'required',
                    'numeric',
                ],
                'educational_level' => [
                    'required',
                ],
                'application_file' => [
                    'required',
                    'file',
                    'mimes:pdf',
                ],
                'study_program' => [
                    'required',
                ],
                'gpu' => [
                    'required',
                    'numeric',
                    'gt:0',
                ],
                'ram' => [
                    'required',
                    'numeric',
                    'gt:0',
                ],
                'storage' => [
                    'required',
                    'numeric',
                    'gt:0',
                ],
                'partner' => [
                    'required',
                ],
                'duration' => [
                    'required',
                    'numeric',
                    'gt:0',
                ],
                'research_field' => [
                    'required',
                ],
                'short_description' => [
                    'required',
                ],
                'data_description' => [
                    'required',
                ],
                'shared_data' => [
                    'required',
                ],
                'activity_plan' => [
                    'required',
                ],
                'output_plan' => [
                    'required',
                ],
                'docker_image' => [
                    'required',
                ],
                'research_fee' => [
                    'required',
                    'numeric',
                ],
                'proposal_file' => [
                    'required',
                    'file',
                    'mimes:pdf',
                ],
                'term_and_condition' => [
                    'required',
                ],
            ]
        );

        if ($validate->fails()) {
            $data = [
                'validation_errors' => $validate->errors(),
            ];

            return ResponseFormatter::validation_error('Validation Errors', $data);
        }

        try {
            $check_submission = ProposalSubmission::select('*')
                ->withTrashed()
                ->whereDate('created_at', '>=', date('Y-m-d') . ' 00:00:00')
                ->count();
            
            if ($check_submission === 0) {
                $id = 'PS' . date('dmy') . '0001';
            } else {
                $item = $check_submission + 1;
                if ($item < 10) {
                    $id = 'PS' . date('dmy') . '000' . $item;
                } elseif ($item >= 10 && $item <= 99) {
                    $id = 'PS' . date('dmy') . '00' . $item;
                } elseif ($item >= 100 && $item <= 999) {
                    $id = 'PS' . date('dmy') . '0' . $item;
                } elseif ($item >= 1000 && $item <= 9999) {
                    $id = 'PS' . date('dmy') . $item;
                }
            }

            if ($request->hasFile('proposal_file')) {
                $file = $request->file('proposal_file');
                $extension = $file->getClientOriginalExtension();
                $newName = Str::random(40) . '.' . $extension;

                $file->storeAs('proposal', $newName, 'minio');
                $link = $newName;
            } else {
                $data = [
                    'validation_errors' => [
                        'proposal_file' => 'File tidak ditemukan.'
                    ]
                ];
                return ResponseFormatter::validation_error('Error Proposal File', $data);
            }

            if ($request->hasFile('application_file')) {
                $file = $request->file('application_file');
                $extension = $file->getClientOriginalExtension();
                $newName = Str::random(40) . '.' . $extension;

                $file->storeAs('application_dgx', $newName, 'minio');
                $linkDGX = $newName;
            } else {
                $data = [
                    'validation_errors' => [
                        'application_file' => 'File tidak ditemukan.'
                    ]
                ];
                return ResponseFormatter::validation_error('Error Proposal File', $data);
            }

            if ($request->shared_data === "yes") {
                $shared_data = 1;
            } else {
                $shared_data = 0;
            }
            
            if ($request->term_and_condition === "agree") {
                $term_and_condition = 1;
            } else {
                $term_and_condition = 0;
            }

            $submission = ProposalSubmission::create([
                'id' => $id,
                'type_of_proposal' => $request->type_of_proposal,
                'user_id' => auth()->user()->id,
                'phone_number' => $request->phone_number,
                'educational_level' => $request->educational_level,
                'application_file' => $linkDGX,
                'study_program' => $request->study_program,
                'gpu' => $request->gpu,
                'ram' => $request->ram,
                'storage' => $request->storage,
                'partner' => $request->partner,
                'duration' => $request->duration,                
                'research_field' => $request->research_field,
                'short_description' => $request->short_description,
                'data_description' => $request->data_description,
                'shared_data' => $shared_data,
                'activity_plan' => $request->activity_plan,
                'output_plan' => $request->output_plan,
                'previous_experience' => $request->previous_experience,
                'docker_image' => $request->docker_image,
                'research_fee' => (int)$request->research_fee,
                'proposal_file' => $link,
                'term_and_condition' => $term_and_condition,
                'status' => 'Pending',
            ]);

            $data = [
                'submission' => $submission
            ];

            return ResponseFormatter::success('Success Store Submission', $data);
        } catch (QueryException $error) {
            $data = [
                'error' => $error
            ];

            return ResponseFormatter::error(500, 'Query Error', $data);
        }
    }
    
    public function approved(Request $request, $id)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'appr_description' => [
                    'required',
                ],
            ]
        );

        if ($validate->fails()) {
            $data = [
                'validation_errors' => $validate->errors(),
            ];

            return ResponseFormatter::validation_error('Validation Errors', $data);
        }

        ProposalSubmission::where('id', $id)
            ->update([
                'status' => 'Approved'
            ]);

        $proposal = ProposalSubmission::where('id', $id)
            ->first();

        $user = User::where('id', $proposal->user_id)
            ->with('user_profile')
            ->first();

        $checkLastName = $user->user_profile->last_name === null ? "" : " ".$user->user_profile->last_name;

        $details = [
            "subject" => env('SUBJECT_APPROVE_PROPOSAL'),
            "body" => $request->appr_description,
            "name" => $user->user_profile->first_name . $checkLastName,
            "email" => $user->email
        ];
        
        dispatch(new ApproveEmailJob($details));

        return ResponseFormatter::success('Success Approved Submission');
    }

    public function rejected(Request $request, $id)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'rev_description' => [
                    'required',
                ],
            ]
        );

        if ($validate->fails()) {
            $data = [
                'validation_errors' => $validate->errors(),
            ];

            return ResponseFormatter::validation_error('Validation Errors', $data);
        }
        
        ProposalSubmission::where('id', $id)
            ->update([
                'status' => 'Rejected',
                'rev_description' => $request->rev_description,
            ]);

        $proposal = ProposalSubmission::where('id', $id)
            ->first();

        $user = User::where('id', $proposal->user_id)
            ->with('user_profile')
            ->first();

            
        $checkLastName = $user->user_profile->last_name === null ? "" : " ".$user->user_profile->last_name;
        
        $details = [
            "subject" => env('SUBJECT_REVISION_PROPOSAL'),
            "body" => $request->rev_description,
            "name" => $user->user_profile->first_name . $checkLastName,
            "email" => $user->email
        ];

        
        dispatch(new RevisionEmailJob($details));

        return ResponseFormatter::success('Success Rejected Submission');
    }

    public function finished($id)
    {
        ProposalSubmission::where('id', $id)
            ->update([
                'status' => 'Finished'
            ]);

        return ResponseFormatter::success('Success Finished Submission');
    }

    public function showAll()
    {
        $submission = ProposalSubmission::orderBy('id', 'DESC')
            ->with('user')
            ->get();

        $data = [
            'submission' => $submission
        ];

        return ResponseFormatter::success('All Submission', $data);
    }

    public function showAllUser()
    {
        $submission = ProposalSubmission::where('user_id', auth()->user()->id)
            ->orderBy('id', 'DESC')
            ->with('user')
            ->get();

        $data = [
            'submission' => $submission
        ];

        return ResponseFormatter::success('All Submission', $data);
    }

    public function show($id)
    {
        $submission = ProposalSubmission::where('id', $id)
            ->with('user')
            ->first();

        $data = [
            'submission' => $submission
        ];

        return ResponseFormatter::success('Submission ' . $id, $data);
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'type_of_proposal' => [
                    'required',
                ],
                'phone_number' => [
                    'required',
                    'numeric',
                ],
                'educational_level' => [
                    'required',
                ],
                'study_program' => [
                    'required',
                ],
                'gpu' => [
                    'required',
                    'numeric',
                ],
                'ram' => [
                    'required',
                    'numeric',
                ],
                'storage' => [
                    'required',
                    'numeric',
                ],
                'partner' => [
                    'required',
                ],
                'duration' => [
                    'required',
                    'numeric',
                ],
                'research_field' => [
                    'required',
                ],
                'short_description' => [
                    'required',
                ],
                'data_description' => [
                    'required',
                ],
                'shared_data' => [
                    'required',
                ],
                'activity_plan' => [
                    'required',
                ],
                'output_plan' => [
                    'required',
                ],
                'docker_image' => [
                    'required',
                ],
                'research_fee' => [
                    'required',
                ],
            ]
        );

        if ($validate->fails()) {
            $data = [
                'validation_errors' => $validate->errors(),
            ];

            return ResponseFormatter::validation_error('Validation Errors', $data);
        }

        try {
            $submission = ProposalSubmission::where('id', $id)
                ->first();

            if ($request->hasFile('proposal_file')) {
                $file = $request->file('proposal_file');
                $extension = $file->getClientOriginalExtension();
                $newName = Str::random(40) . '.' . $extension;

                $file->storeAs('proposal', $newName, 'minio');
                $link = $newName;
            } else {
                $link = $submission->proposal_file;
            }

            if ($request->shared_data === "yes") {
                $shared_data = 1;
            } else {
                $shared_data = 0;
            }

            if ($request->hasFile('application_file')) {
                $file = $request->file('application_file');
                $extension = $file->getClientOriginalExtension();
                $newName = Str::random(40) . '.' . $extension;

                $file->storeAs('application_dgx', $newName, 'minio');
                $linkDGX = $newName;
            } else {
                $linkDGX = $submission->application_file;
            }

            if ($request->term_and_condition === "agree") {
                $term_and_condition = 1;
            } else {
                $term_and_condition = 0;
            }

            ProposalSubmission::where('id', $id)
                ->update([
                    'type_of_proposal' => $request->type_of_proposal,
                    'phone_number' => $request->phone_number,
                    'educational_level' => $request->educational_level,
                    'application_file' => $linkDGX,
                    'study_program' => $request->study_program,
                    'gpu' => $request->gpu,
                    'ram' => $request->ram,
                    'storage' => $request->storage,
                    'partner' => $request->partner,
                    'duration' => $request->duration,
                    'research_field' => $request->research_field,
                    'short_description' => $request->short_description,
                    'data_description' => $request->data_description,
                    'shared_data' => $shared_data,
                    'activity_plan' => $request->activity_plan,
                    'output_plan' => $request->output_plan,
                    'previous_experience' => $request->previous_experience,
                    'docker_image' => $request->docker_image,
                    'research_fee' => $request->research_fee,
                    'proposal_file' => $link,
                    'term_and_condition' => $term_and_condition,
                    'status' => 'Pending',
                    'rev_description' => null,
                ]);

            $data = [
                'submission' => $submission
            ];

            return ResponseFormatter::success('Success Update Submission', $data);
        } catch (QueryException $error) {
            $data = [
                'error' => $error
            ];

            return ResponseFormatter::error(500, 'Query Error', $data);
        }
    }

    public function destroy($id)
    {
        $submission = ProposalSubmission::where('id', $id)->first();
        $submission->forceDelete();
    
        return ResponseFormatter::success('Success Delete Submission ' . $id);
    }

    public function readFile($filename)
    {
        $response = Storage::disk('minio')->response('proposal/'.$filename);
        
        return $response;
    }

    public function readFileApplication($filename)
    {
        $response = Storage::disk('minio')->response('application_dgx/'.$filename);
        
        return $response;
    }
}
