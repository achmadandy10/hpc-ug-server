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
    return view('welcome');
});

Route::get('/proposal/file/{filename}', function ($filename) {
    $response = Storage::disk('minio')->response('proposal/'.$filename);
    return $response;
});

Route::get('/application_dgx/file/{filename}', function ($filename) {
    $response = Storage::disk('minio')->response('application_dgx/'.$filename);
    return $response;
});

Route::get('/procedure/file/{filename}', function ($filename) {
    $response = Storage::disk('minio')->response('procedure/'.$filename);
    return $response;
});