<?php

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\AuthController;
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

Route::middleware(['auth:sanctum', 'isAdminContent'])->group(function () {

});

Route::middleware(['auth:sanctum', 'isAdminProposalSubmission'])->group(function () {
    
});

Route::middleware(['auth:sanctum', 'isAdminSuper'])->group(function () {
    
});

Route::middleware(['auth:sanctum', 'isUserExternal'])->group(function () {
    
});

Route::middleware(['auth:sanctum', 'isUserInternal'])->group(function () {

});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});