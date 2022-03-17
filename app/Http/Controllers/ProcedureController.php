<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Procedure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProcedureController extends Controller
{
    public function store(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'type' => [
                    'required',
                ],
                'document_type' => [
                    'required',
                ],
                'file' => [
                    'required',
                    'file',
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
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $newName = Str::random(40) . '.' . $extension;

                $file->storeAs('procedure', $newName, 'minio');
                $link = $newName;
            } else {
                $data = [
                    'validation_errors' => [
                        'file' => 'File tidak ditemukan.'
                    ]
                ];
                return ResponseFormatter::validation_error('Error File', $data);
            }

            $procedure = Procedure::create([
                'type' => $request->type,
                'document_type' => $request->document_type,
                'file' => $link,
            ]);

            $data = [
                'procedure' => $procedure
            ];

            return ResponseFormatter::success('Success Store Procedure', $data);
        } catch (QueryException $error) {
            $data = [
                'error' => $error
            ];

            return ResponseFormatter::error(500, 'Query Error', $data);
        }
    }

    public function show($id)
    {
        $procedure = Procedure::where('id', $id)
            ->first();

        $data = [
            'procedure' => $procedure
        ];

        return ResponseFormatter::success('Procedure ' . $id, $data);        
    }

    public function showAll()
    {
        $procedure = Procedure::get();

        $data = [
            'procedure' => $procedure
        ];

        return ResponseFormatter::success('All Procedure', $data);        
    }

    public function update(Request $request, $id)
    {
        try {
            $procedure = Procedure::where('id', $id)->first();

            $request->type === null ? $type = $procedure->type : $type = $request->type;
            $request->dcument_type === null ? $document_type = $procedure->document_type : $ducument_type = $request->document_type;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $newName = Str::random(40) . '.' . $extension;

                $file->storeAs('procedure', $newName, 'minio');
                $link = $newName;
            } else {
                $link = $procedure->file;
            }

            Procedure::where('id', $id)
                ->update([
                    'type' => $type,
                    'document_type' => $document_type,
                    'file' => $link,
                ]);

            $data = [
                'procedure' => $procedure
            ];

            return ResponseFormatter::success('Success Update Procedure', $data);
        } catch (QueryException $error) {
            $data = [
                'error' => $error
            ];

            return ResponseFormatter::error(500, 'Query Error', $data);
        }
    }

    public function destroy($id)
    {
        $procedure = Procedure::where('id', $id)->first();
        $procedure->forceDelete();
    
        return ResponseFormatter::success('Success Delete Procedure ' . $id);
    }

    public function readFile($filename)
    {
        $response = Storage::disk('minio')->response('procedure/'.$filename);
        
        return $response;
    }
}
