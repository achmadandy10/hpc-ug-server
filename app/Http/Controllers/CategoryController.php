<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'label' => [
                    'required',
                    Rule::unique(Category::class)
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
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $extension = $file->getClientOriginalExtension();
                $newName = time() . '.' . $extension;
                $file->move('category/', $newName);
                $link = env('FILE_URL') . 'category/' . $newName;
            } else {
                $link = 'https://ui-avatars.com/api/?name=' . $request->label . '&color=7F9CF5&background=EBF4FF';
            }

            $category = Category::create([
                'label' => $request->label,
                'slug' => Str::slug($request->label),
                'thumbnail' => $link,
            ]);

            $data = [
                'category' => $category
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
        $category = Category::orderBy('id', 'DESC')->get();

        $data = [
            'category' => $category
        ];

        return ResponseFormatter::success('All Categories', $data);
    }
    
    public function select()
    {
        $category = Category::get();

        $arr = [];

        foreach ($category as $row) {
            $newData = [
                'value' => $row->id,
                'label' => $row->label,
            ];

            array_push($arr, $newData);
        }

        $data = [
            'category' => $arr
        ];

        return ResponseFormatter::success('All Categories', $data);
    }
    
    public function showPostByCategory($label)
    {
        $category = Category::where('label', $label)->with('posts')->get();

        $data = [
            'category' => $category
        ];

        return ResponseFormatter::success('All Categories ' . $label, $data);
    }

    public function show($id)
    {
        $category = Category::where('id', $id)->first();

        $data = [
            'category' => $category
        ];

        return ResponseFormatter::success('Category ' . $category->label, $data);
    }

    public function update(Request $request, $id)
    {   
        $category = Category::where('id', $id)->first();

        if ($request->label === $category->label) {
            $label = $category->label;
            $slug = Str::slug($label);
        } else {
            $validate = Validator::make(
                $request->all(),
                [
                    'label' => [
                        Rule::unique(Category::class)
                    ],
                ]
            );
    
            if ($validate->fails()) {
                $data = [
                    'validation_errors' => $validate->errors(),
                ];
    
                return ResponseFormatter::validation_error('Validation Errors', $data);
            }

            $label = $request->label;
            $slug = Str::slug($label);
        }

        if ($request->thumbnail !== null) {
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $extension = $file->getClientOriginalExtension();
                $newName = time() . '.' . $extension;
                $file->move('category/', $newName);
                $link = env('FILE_URL') . 'category/' . $newName;
            } else {
                $link = $category->thumbnail;
            }
        } else {
            $link = $category->thumbnail;
        }

        $update = Category::where('id', $id)
            ->update([
                'label' => $label,
                'slug' => $slug,
                'thumbnail' => $link,
            ]);

        $data = [
            'category' => $update
        ];

        return ResponseFormatter::success('Success Update Category ' . $category->label, $data);
    }

    public function destroy($id)
    {
        Category::where('id', $id)->forceDelete();

        return ResponseFormatter::success('Success Delete Category ' . $id);
    }
}
