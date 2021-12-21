<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\ProposalSubmission;
use Illuminate\Http\Request;

class ProposalSubmissionController extends Controller
{
    public function index()
    {
        $proposal = ProposalSubmission::get();

        $data = [
            'propasl' => $proposal
        ];

        return ResponseFormatter::success('All Proposal', $data);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
