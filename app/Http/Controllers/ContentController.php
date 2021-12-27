<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Content;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    public function store(Request $request)
    {
        if ($request->status == "Post") {
            $validate = Validator::make(
                $request->all(),
                [
                    'title' => [
                        'required',
                    ],
                    'body' => [
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
        }

        try {
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $extension = $file->getClientOriginalExtension();
                $newName = time() . '.' . $extension;
                $file->move('content/', $newName);
                $link = env('FILE_URL') . 'content/' . $newName;
            } else {
                $request->title === null ? $title = 'Title' : $title = $request->title;
                $link = 'https://ui-avatars.com/api/?name=' . $title . '&color=7F9CF5&background=EBF4FF';
            }

            if ($request->title === null) {
                $slug = Str::random(3) . '-' . Str::random(4) . '-' . Str::random(3);
            } else {
                $slug = Str::slug($request->title);
            }

            $content = Content::create([
                'title' => $request->title,
                'slug' => $slug,
                'thumbnail' => $link,
                'body' => $request->body,
                'type' => $request->type,
                'status' => $request->status
            ]);

            $data = [
                'content' => $content
            ];

            return ResponseFormatter::success('Success Store Content', $data);
        } catch (QueryException $error) {
            $data = [
                'error' => $error
            ];

            return ResponseFormatter::error(500, 'Query Error', $data);
        }
    }

    public function showAllAbout()
    {
        $content = Content::orderBy('id', 'DESC')
            ->where('type', 'About')
            ->get();

        $data = [
            'content' => $content
        ];

        return ResponseFormatter::success('All Content About', $data);
    }

    public function showAllService()
    {
        $content = Content::orderBy('id', 'DESC')
            ->where('type', 'Service')
            ->get();

        $data = [
            'content' => $content
        ];

        return ResponseFormatter::success('All Content Service', $data);
    }

    public function showStatusPostAbout()
    {
        $content = Content::where('status', 'Post')
            ->where('type', 'About')
            ->orderBy('id', 'DESC')
            ->get();

        $data = [
            'content' => $content
        ];

        return ResponseFormatter::success('All Content About Status Post', $data);
    }

    public function showStatusPostService()
    {
        $content = Content::where('status', 'Post')
            ->where('type', 'Service')
            ->orderBy('id', 'DESC')
            ->get();

        $data = [
            'content' => $content
        ];

        return ResponseFormatter::success('All Content Service Status Post', $data);
    }

    public function showStatusDraftAbout()
    {
        $content = Content::where('status', 'Draft')
            ->where('type', 'About')
            ->orderBy('id', 'DESC')
            ->get();

        $data = [
            'content' => $content
        ];

        return ResponseFormatter::success('All Content About Status Draft', $data);
    }

    public function showStatusDraftService()
    {
        $content = Content::where('status', 'Draft')
            ->where('type', 'Service')
            ->orderBy('id', 'DESC')
            ->get();

        $data = [
            'content' => $content
        ];

        return ResponseFormatter::success('All Content Service Status Draft', $data);
    }

    public function show($id, $slug)
    {
        $content = Content::where('id', $id)
            ->where('slug', $slug)
            ->first();
        
        $data = [
            'content' => $content
        ];

        return ResponseFormatter::success('Content ' . $content->title, $data);
    }

    public function update(Request $request, $id, $slug)
    {
        if ($request->status === 'Post') {
            $validate = Validator::make(
                $request->all(),
                [
                    'title' => [
                        'required',
                    ],
                    'body' => [
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
        }

        $content = Content::where('id', $id)
            ->where('slug', $slug)
            ->first();

        if ($request->status === "Draft") {
            if ($request->title !== null) {
                $content_title = $request->title;
                $content_slug = Str::slug($request->title);
            } else {
                $content_title = null;
                $content_slug = Str::random(3) . '-' . Str::random(4) . '-' . Str::random(3);
            }
        } else {
            if ($request->title !== null) {
                $content_title = $request->title;
                $content_slug = Str::slug($request->title);
            } else {
                $content_title = $content->title;
                $content_slug = $content->slug;
            }
        }
        
        if ($request->thumbnail !== null) {
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $extension = $file->getClientOriginalExtension();
                $newName = time() . '.' . $extension;
                $file->move('content/', $newName);
                $link = env('FILE_URL') . 'content/' . $newName;
            } else {
                $link = $content->thumbnail;
            }
        } else {
            $link = $content->thumbnail;
        }
        
        if ($request->body === null) {
            $body = $content->body;
        } else {
            $body = $request->body;
        }

        Content::where('id', $id)
            ->where('slug', $slug)
            ->update([
                'title' => $content_title,
                'slug' => $content_slug,
                'thumbnail' => $link,
                'body' => $body,
                'status' => $request->status,
            ]);

        return ResponseFormatter::success('Success Update Content');
    }

    public function postToDraft($id, $slug)
    {  
        Content::where('id', $id)
            ->where('slug', $slug)
            ->update([
                'status' => 'Draft'
            ]);

        return ResponseFormatter::success('Success Draft Content');
    }

    public function destroy($id, $slug)
    {
        Content::where('id', $id)
            ->where('slug', $slug)
            ->forceDelete();
        
        return ResponseFormatter::success('Success Delete Content');
    }
}
