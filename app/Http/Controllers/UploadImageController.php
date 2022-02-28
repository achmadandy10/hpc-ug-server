<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadImageController extends Controller
{
    public function uploadImage(Request $request)
    {
        $imgPath = $request->file('file');
        $extension = $imgPath->getClientOriginalExtension();
        $imgName = time() . '-' . '.' . $extension;
        $imgPath->move('image_articles', $imgName);

        return response()->json([
            'location' => env('FILE_URL') . 'image_articles/' . $imgName
        ]);
    }
}
