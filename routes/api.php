<?php

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProposalSubmissionController;
use App\Http\Controllers\UploadImageController;
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

// Route::post('/admin-content/post/upload-image', [PostController::class, 'uploadImage']);

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
        Route::get('select', [CategoryController::class, 'select']);
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
        Route::get('show/{id}/{slug}', [PostController::class, 'show']);
        Route::post('store', [PostController::class, 'store']);
        Route::post('update/{id}/{slug}', [PostController::class, 'update']);
        Route::post('draft/{id}/{slug}', [PostController::class, 'postToDraft']);
        Route::post('delete/{id}/{slug}', [PostController::class, 'destroy']);
    });
    
    // Content
    Route::prefix('content')->group(function () {
        Route::get('show-all-about', [ContentController::class, 'showAllAbout']);
        Route::get('show-all-service', [ContentController::class, 'showAllService']);
        Route::get('status-post-about', [ContentController::class, 'showStatusPostAbout']);
        Route::get('status-draft-service', [ContentController::class, 'showStatusDraftService']);
        Route::get('show/{id}/{slug}', [ContentController::class, 'show']);
        Route::post('store', [ContentController::class, 'store']);
        Route::post('update/{id}/{slug}', [ContentController::class, 'update']);
        Route::post('draft/{id}/{slug}', [ContentController::class, 'postToDraft']);
        Route::post('delete/{id}/{slug}', [ContentController::class, 'destroy']);
    });

    Route::post('upload-image', [UploadImageController::class, 'uploadImage']);
});

Route::prefix('admin-proposal')->middleware(['auth:sanctum', 'isAdminProposalSubmission'])->group(function () {
    // Facility
    Route::prefix('facility')->group(function () {
        Route::get('show-all', [FacilityController::class, 'showAll']);
        Route::get('show/{id}', [FacilityController::class, 'show']);
        Route::post('store', [FacilityController::class, 'store']);
        Route::post('update/{id}', [FacilityController::class, 'update']);
        Route::post('delete/{id}', [FacilityController::class, 'destroy']);
    });

    // Proposal Submission
    Route::prefix('proposal-submission')->group(function () {
        Route::get('show-all', [ProposalSubmissionController::class, 'showAll']);
        Route::get('show/{id}', [ProposalSubmissionController::class, 'show']);
        Route::post('approved/{id}', [ProposalSubmissionController::class, 'approved']);
        Route::post('rejected/{id}', [ProposalSubmissionController::class, 'rejected']);
        Route::post('finished/{id}', [ProposalSubmissionController::class, 'finished']);
        Route::post('store', [ProposalSubmissionController::class, 'store']);
        Route::post('update/{id}', [ProposalSubmissionController::class, 'update']);
        Route::post('delete/{id}', [ProposalSubmissionController::class, 'destroy']);
    });
});

Route::prefix('admin-super')->middleware(['auth:sanctum', 'isAdminSuper'])->group(function () {
    // Category
    Route::prefix('category')->group(function () {
        Route::get('show-all', [CategoryController::class, 'showAll']);
        Route::get('select', [CategoryController::class, 'select']);
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
        Route::get('show/{id}/{slug}', [PostController::class, 'show']);
        Route::post('store', [PostController::class, 'store']);
        Route::post('update/{id}/{slug}', [PostController::class, 'update']);
        Route::post('draft/{id}/{slug}', [PostController::class, 'postToDraft']);
        Route::post('delete/{id}/{slug}', [PostController::class, 'destroy']);
    });
    
    // Content
    Route::prefix('content')->group(function () {
        Route::get('show-all-about', [ContentController::class, 'showAllAbout']);
        Route::get('show-all-service', [ContentController::class, 'showAllService']);
        Route::get('status-post-about', [ContentController::class, 'showStatusPostAbout']);
        Route::get('status-draft-service', [ContentController::class, 'showStatusDraftService']);
        Route::get('show/{id}/{slug}', [ContentController::class, 'show']);
        Route::post('store', [ContentController::class, 'store']);
        Route::post('update/{id}/{slug}', [ContentController::class, 'update']);
        Route::post('draft/{id}/{slug}', [ContentController::class, 'postToDraft']);
        Route::post('delete/{id}/{slug}', [ContentController::class, 'destroy']);
    });

    // Facility
    Route::prefix('facility')->group(function () {
        Route::get('show-all', [FacilityController::class, 'showAll']);
        Route::get('show/{id}', [FacilityController::class, 'show']);
        Route::post('store', [FacilityController::class, 'store']);
        Route::post('update/{id}', [FacilityController::class, 'update']);
        Route::post('delete/{id}', [FacilityController::class, 'destroy']);
    });

    // Proposal Submission
    Route::prefix('proposal-submission')->group(function () {
        Route::get('show-all', [ProposalSubmissionController::class, 'showAll']);
        Route::get('show/{id}', [ProposalSubmissionController::class, 'show']);
        Route::post('approved/{id}', [ProposalSubmissionController::class, 'approved']);
        Route::post('rejected/{id}', [ProposalSubmissionController::class, 'rejected']);
        Route::post('finished/{id}', [ProposalSubmissionController::class, 'finished']);
        Route::post('store', [ProposalSubmissionController::class, 'store']);
        Route::post('update/{id}', [ProposalSubmissionController::class, 'update']);
        Route::post('delete/{id}', [ProposalSubmissionController::class, 'destroy']);
    });

    Route::post('upload-image', [UploadImageController::class, 'uploadImage']);
});

Route::prefix('user-external')->middleware(['auth:sanctum', 'isUserExternal'])->group(function () {
    // Facility
    Route::prefix('facility')->group(function () {
        Route::get('select', [FacilityController::class, 'select']);
        Route::get('show/{id}', [FacilityController::class, 'show']);
    });

    Route::prefix('proposal-submission')->group(function () {
        Route::get('show-all', [ProposalSubmissionController::class, 'showAllUser']);
        Route::get('show/{id}', [ProposalSubmissionController::class, 'show']);
        Route::post('store', [ProposalSubmissionController::class, 'store']);
        Route::post('update/{id}', [ProposalSubmissionController::class, 'update']);
        Route::post('delete/{id}', [ProposalSubmissionController::class, 'destroy']);
    });
});

Route::prefix('user-internal')->middleware(['auth:sanctum', 'isUserInternal'])->group(function () {
    // Facility
    Route::prefix('facility')->group(function () {
        Route::get('select', [FacilityController::class, 'select']);
        Route::get('show/{id}', [FacilityController::class, 'show']);
    });

    Route::prefix('proposal-submission')->group(function () {
        Route::get('show-all', [ProposalSubmissionController::class, 'showAllUser']);
        Route::get('show/{id}', [ProposalSubmissionController::class, 'show']);
        Route::post('store', [ProposalSubmissionController::class, 'store']);
        Route::post('update/{id}', [ProposalSubmissionController::class, 'update']);
        Route::post('delete/{id}', [ProposalSubmissionController::class, 'destroy']);
    });
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});