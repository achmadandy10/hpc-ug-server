<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadImageController extends Controller
{
    public function uploadImage(Request $request)
    {
        $imgPath = $request->file('file');
        $extension = $imgPath->getClientOriginalExtension();
        $imgName = Str::random(40) . '-' . '.' . $extension;

        Storage::disk('minio')->put($imgName, 'image_articles/');
        $link = Storage::disk('minio')->url('image_articles/'.$imgName);
        
        return response()->json([
            'location' => $link
        ]);
    }
}
