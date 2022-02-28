<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // $created = Storage::disk('minio')->put('test.txt','Hello World!');
    // dd($created);

    // $exists = Storage::disk('minio')->exists('/test.txt');
    // dd($exists);

    // $size = Storage::disk('minio')->size('/test.txt');
    // dd($size);

    // $readedFile = Storage::disk('minio')->get('test.txt');
    // dd($readedFile);

    // $files = Storage::disk('minio')->files('/');
    // dd($files);

    // $url = Storage::disk('minio')->url('test.txt');
    // dd($url);

    // $deleted = Storage::disk('minio')->delete('/MCSQfkybV4Anm2HdB7xFEaWCddjaFuRDBIlZsOJ2.pdf');
    // dd($deleted);
    return view('welcome');
});