<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Post;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function store(Request $request)
    {
        if ($request->status == "Draft") {
            $validate = Validator::make(
                $request->all(),
                [
                    'thumbnail' => [
                        'file',
                    ],
                ]
            );
        } else {
            $validate = Validator::make(
                $request->all(),
                [
                    'title' => [
                        'required',
                    ],
                    'thumbnail' => [
                        'file',
                        'mimes:jpg,jpeg,png,svg',
                    ],
                    'body' => [
                        'required',
                    ],
                ]
            );
        }

        if ($validate->fails()) {
            $data = [
                'validation_errors' => $validate->errors(),
            ];

            return ResponseFormatter::error(401, 'Validation Errors', $data);
        }

        try {
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $extension = $file->getClientOriginalExtension();
                $newName = time() . $request->title . '.' . $extension;
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

            $post = Post::create([
                'title' => $request->title,
                'slug' => $slug,
                'thumbnail' => $link,
                'body' => $request->body,
                'status' => $request->status
            ]);

            if ($request->category !== null) {
                foreach ($request->category as $category) {
                    DB::table('post_category')->insert([
                        'post_id' => $post->id,
                        'category_id' => $category
                    ]);
                }
            }

            $data = [
                'post' => $post
            ];

            return ResponseFormatter::success('Success Store Category', $data);
        } catch (QueryException $error) {
            $data = [
                'error' => $error
            ];

            return ResponseFormatter::error(500, 'Query Error', $data);
        }
    }

    public function showAll()
    {
        $post = Post::with('categories')->get();

        $data = [
            'post' => $post
        ];

        return ResponseFormatter::success('All Facility', $data);
    }

    public function showStatusPost()
    {
        $post = Post::where('status', 'Post')->with('categories')->get();

        $data = [
            'post' => $post
        ];

        return ResponseFormatter::success('All Facility Status Post', $data);
    }

    public function showStatusDraft()
    {
        $post = Post::where('status', 'Draft')->with('categories')->get();

        $data = [
            'post' => $post
        ];

        return ResponseFormatter::success('All Facility Status Draft', $data);
    }

    public function show($id)
    {
        $post = Post::where('id', $id)->first();
        
        $data = [
            'post' => $post
        ];

        return ResponseFormatter::success('Post ' . $post->title, $data);
    }

    public function update(Request $request, $id)
    {
        
    }

    public function destroy($id)
    {
        
    }
}
