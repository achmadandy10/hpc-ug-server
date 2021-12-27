<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Facility;
use App\Models\ProposalSubmission;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
                'use_stock' => [
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
                $id = 'F' . date('dmy') . '0001';
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
                $newName = time() . '.' . $extension;
                $file->move('proposal_file/', $newName);
                $link = env('FILE_URL') . 'proposal_file/' . $newName;
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
                'facility_id' => $request->facility_needs,
                'use_stock' => $request->use_stock,
                'proposal_file' => $link,
                'status' => 'Pending',
            ]);

            $facility = Facility::where('id', $request->facility_needs)
                ->first();

            Facility::where('id', $request->facility_needs)
                ->update([
                    'remaining_stock' => $facility->remaining_stock - $request->use_stock,
                    'use_stock' => $facility->use_stock + $request->use_stock
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
        $submission = ProposalSubmission::where('id', $id)->first();

        ProposalSubmission::where('id', $id)
            ->update([
                'status' => 'Rejected'
            ]);

        $facility = Facility::where('id', $submission->facility_id)->first();
        Facility::where('id', $submission->facility_id)
                ->update([
                    'remaining_stock' => $submission->use_stock + $facility->remaining_stock,
                    'use_stock' => $facility->use_stock - $submission->use_stock
                ]);

        return ResponseFormatter::success('Success Rejected Submission');
    }

    public function finished($id)
    {
        $submission = ProposalSubmission::where('id', $id)->first();

        ProposalSubmission::where('id', $id)
            ->update([
                'status' => 'Finished'
            ]);

        $facility = Facility::where('id', $submission->facility_id)->first();
        Facility::where('id', $submission->facility_id)
                ->update([
                    'remaining_stock' => $submission->use_stock + $facility->remaining_stock,
                    'use_stock' => $facility->use_stock - $submission->use_stock
                ]);

        return ResponseFormatter::success('Success Finished Submission');
    }

    public function showAll()
    {
        $submission = ProposalSubmission::with('facility')
            ->orderBy('id', 'DESC')
            ->get();

        $data = [
            'submission' => $submission
        ];

        return ResponseFormatter::success('All Submission', $data);
    }

    public function showAllUser()
    {
        $submission = ProposalSubmission::with('facility')
            ->where('user_id', auth()->user()->id)
            ->orderBy('id', 'DESC')
            ->get();

        $data = [
            'submission' => $submission
        ];

        return ResponseFormatter::success('All Submission', $data);
    }

    public function show($id)
    {
        $submission = ProposalSubmission::with('facility')
            ->where('id', $id)
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
                'use_stock' => [
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
                $newName = time() . '.' . $extension;
                $file->move('proposal_file/', $newName);
                $link = env('FILE_URL') . 'proposal_file/' . $newName;
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
                    'facility_id' => $request->facility_needs,
                    'use_stock' => $request->use_stock,
                    'proposal_file' => $link,
                    'status' => 'Pending',
                ]);
            
            $facility = Facility::where('id', $request->facility_needs)
            ->first();

            Facility::where('id', $request->facility_needs)
                ->update([
                    'remaining_stock' => ($submission->use_stock + $facility->remaining_stock) - $request->use_stock,
                    'use_stock' => ($facility->use_stock - $submission->use_stock) + $request->use_stock
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
        $submission = ProposalSubmission::with('facility')->where('id', $id)->first();
        $facility = Facility::where('id', $submission->facility_id)->first();
        Facility::where('id', $submission->facility_id)
                ->update([
                    'remaining_stock' => $submission->use_stock + $facility->remaining_stock,
                    'use_stock' => $facility->use_stock - $submission->use_stock
                ]);
        $submission->forceDelete();
    
        return ResponseFormatter::success('Success Delete Submission ' . $id);
    }
}
