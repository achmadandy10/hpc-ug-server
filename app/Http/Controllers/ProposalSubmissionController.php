<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\ProposalSubmission;
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
                'phone_number' => [
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
                'facility_needs' => [
                    'required',
                ],
                'docker_image' => [
                    'required',
                ],
                'proposal_file' => [
                    'required',
                    'file',
                    'mimes:pdf',
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

            if ($request->shared_data === "yes") {
                $shared_data = 1;
            } else {
                $shared_data = 0;
            }

            $submission = ProposalSubmission::create([
                'id' => $id,
                'user_id' => auth()->user()->id,
                'phone_number' => $request->phone_number,
                'research_field' => $request->research_field,
                'short_description' => $request->short_description,
                'data_description' => $request->data_description,
                'shared_data' => $shared_data,
                'activity_plan' => $request->activity_plan,
                'output_plan' => $request->output_plan,
                'previous_experience' => $request->previous_experience,
                'facility_needs' => $request->facility_needs,
                'docker_image' => $request->docker_image,
                'proposal_file' => $link,
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
    
    public function approved($id)
    {
        ProposalSubmission::where('id', $id)
            ->update([
                'status' => 'Approved'
            ]);

        return ResponseFormatter::success('Success Approved Submission');
    }

    public function rejected($id)
    {
        ProposalSubmission::where('id', $id)
            ->update([
                'status' => 'Rejected'
            ]);

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

        return ResponseFormatter::success('All Submission', $data);
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'phone_number' => [
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
                'facility_needs' => [
                    'required',
                ],
                'docker_image' => [
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

            ProposalSubmission::where('id', $id)
                ->update([
                    'phone_number' => $request->phone_number,
                    'research_field' => $request->research_field,
                    'short_description' => $request->short_description,
                    'data_description' => $request->data_description,
                    'shared_data' => $shared_data,
                    'activity_plan' => $request->activity_plan,
                    'output_plan' => $request->output_plan,
                    'previous_experience' => $request->previous_experience,
                    'facility_needs' => $request->facility_needs,
                    'docker_image' => $request->docker_image,
                    'proposal_file' => $link,
                    'status' => 'Pending',
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
}
