<?php

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/check_admin', [
    'middleware' => ['isAdmin', 'auth:sanctum'],
    function () {
        return ResponseFormatter::success('You are Admin');
}]);
    
Route::get('/check_user', [
    'middleware' => ['isUser', 'auth:sanctum'],
    function () {
        return ResponseFormatter::success('You are User');
}]);

Route::prefix('admin-content')->middleware(['auth:sanctum', 'isAdminContent'])->group(function () {
    // Category
    Route::prefix('category')->group(function () {
        Route::get('show-all', [CategoryController::class, 'showAll']);
        Route::get('show/{id}', [CategoryController::class, 'show']);
        Route::post('store', [CategoryController::class, 'store']);
        Route::post('update/{id}', [CategoryController::class, 'update']);
        Route::post('delete/{id}', [CategoryController::class, 'destroy']);
    });
    
    // Post
    Route::prefix('post')->group(function () {
        Route::get('show-all', [PostController::class, 'showAll']);
        Route::get('status-post', [PostController::class, 'showStatusPost']);
        Route::get('status-draft', [PostController::class, 'showStatusDraft']);
        Route::get('show/{id}', [PostController::class, 'show']);
        Route::post('store', [PostController::class, 'store']);
        Route::post('update/{id}', [PostController::class, 'update']);
        Route::post('delete/{id}', [PostController::class, 'destroy']);
    });
});

Route::middleware(['auth:sanctum', 'isAdminProposalSubmission'])->group(function () {
    
});

Route::prefix('admin-super')->middleware(['auth:sanctum', 'isAdminSuper'])->group(function () {
    // Category
    Route::prefix('category')->group(function () {
        Route::get('show-all', [CategoryController::class, 'showAll']);
        Route::get('show/{id}', [CategoryController::class, 'show']);
        Route::post('store', [CategoryController::class, 'store']);
        Route::post('update/{id}', [CategoryController::class, 'update']);
        Route::post('delete/{id}', [CategoryController::class, 'destroy']);
    });
    
    // Post
    Route::prefix('post')->group(function () {
        Route::get('show-all', [PostController::class, 'showAll']);
        Route::get('status-post', [PostController::class, 'showStatusPost']);
        Route::get('status-draft', [PostController::class, 'showStatusDraft']);
        Route::get('show/{id}', [PostController::class, 'show']);
        Route::post('store', [PostController::class, 'store']);
        Route::post('update/{id}', [PostController::class, 'update']);
        Route::post('delete/{id}', [PostController::class, 'destroy']);
    });
});

Route::middleware(['auth:sanctum', 'isUserExternal'])->group(function () {
    
});

Route::middleware(['auth:sanctum', 'isUserInternal'])->group(function () {

});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});