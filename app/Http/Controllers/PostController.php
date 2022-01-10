<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostController extends Controller
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
                    'category' => [
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
                $file->move('news/', $newName);
                $link = env('FILE_URL') . 'news/' . $newName;
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
                foreach (explode(',', $request->category) as $category) {
                    DB::table('category_post')->insert([
                        'post_id' => $post->id,
                        'category_id' => $category
                    ]);
                }
            }

            $data = [
                'post' => $post
            ];

            return ResponseFormatter::success('Success Store Post', $data);
        } catch (QueryException $error) {
            $data = [
                'error' => $error
            ];

            return ResponseFormatter::error(500, 'Query Error', $data);
        }
    }

    public function showAll()
    {
        $post = Post::with('categories')
            ->orderBy('id', 'DESC')
            ->get();

        $data = [
            'post' => $post
        ];

        return ResponseFormatter::success('All Post', $data);
    }

    public function showStatusPost()
    {
        $post = Post::where('status', 'Post')
            ->with('categories')
            ->orderBy('id', 'DESC')
            ->get();

        $data = [
            'post' => $post
        ];

        return ResponseFormatter::success('All Post Status Post', $data);
    }
    
    public function showCategory($slug)
    {
        $post = Post::where('status', 'Post')
            ->with('categories')
            ->orderBy('id', 'DESC')
            ->get();

        $post_category = [];
        $category = Category::where('slug', $slug)->first();

        foreach ($post as $row) {
            foreach ($row->categories as $ctg) {
                if ($ctg->slug === $slug) {
                    array_push($post_category, $row);
                }
            }
        }

        $data = [
            'label' => $category->label,
            'post' => $post_category,
        ];

        return ResponseFormatter::success('All Post Status Post', $data);
    }

    public function showStatusDraft()
    {
        $post = Post::where('status', 'Draft')
            ->with('categories')
            ->orderBy('id', 'DESC')
            ->get();

        $data = [
            'post' => $post
        ];

        return ResponseFormatter::success('All Post Status Draft', $data);
    }

    public function show($id, $slug)
    {
        $post = Post::where('id', $id)
            ->where('slug', $slug)
            ->with('categories')
            ->first();
        
        $data = [
            'post' => $post
        ];

        return ResponseFormatter::success('Post ' . $post->title, $data);
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
                    'category' => [
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

        $post = Post::where('id', $id)
            ->where('slug', $slug)
            ->with('categories')
            ->first();

        if ($request->status === "Draft") {
            if ($request->title !== null) {
                $post_title = $request->title;
                $post_slug = Str::slug($request->title);
            } else {
                $post_title = null;
                $post_slug = Str::random(3) . '-' . Str::random(4) . '-' . Str::random(3);
            }
        } else {
            if ($request->title !== null) {
                $post_title = $request->title;
                $post_slug = Str::slug($request->title);
            } else {
                $post_title = $post->title;
                $post_slug = $post->slug;
            }
        }
        
        if ($request->thumbnail !== null) {
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $extension = $file->getClientOriginalExtension();
                $newName = time() . '.' . $extension;
                $file->move('news/', $newName);
                $link = env('FILE_URL') . 'news/' . $newName;
            } else {
                $link = $post->thumbnail;
            }
        } else {
            $link = $post->thumbnail;
        }
        
        if ($request->body === null) {
            $body = $post->body;
        } else {
            $body = $request->body;
        }

        Post::where('id', $id)
            ->where('slug', $slug)
            ->update([
                'title' => $post_title,
                'slug' => $post_slug,
                'thumbnail' => $link,
                'body' => $body,
                'status' => $request->status,
            ]);

        if ($request->category !== null) {
            DB::table('category_post')
                ->where('post_id', $id)
                ->delete();

            foreach (explode(',',$request->category) as $row) {
                DB::table('category_post')->insert([
                    'post_id' => $id,
                    'category_id' => $row
                ]);
            }
        } else if ($request->category === null && $request->status === "Draft") {
            DB::table('category_post')
                ->where('post_id', $id)
                ->delete();
        }

        return ResponseFormatter::success('Success Update Post');
    }

    public function postToDraft($id, $slug)
    {  
        Post::where('id', $id)
            ->where('slug', $slug)
            ->update([
                'status' => 'Draft'
            ]);

        return ResponseFormatter::success('Success Draft Post');
    }

    public function destroy($id, $slug)
    {
        Post::where('id', $id)
            ->where('slug', $slug)
            ->with('categories')
            ->forceDelete();
        
        return ResponseFormatter::success('Success Delete Post');
    }
}
