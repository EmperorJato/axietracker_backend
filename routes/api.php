<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ScholarshipController;
use App\Http\Controllers\Api\AccountsController;
use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LeaderboardsController;
use App\Http\Controllers\Api\Web3Controller;
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

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::apiResource('user', UserController::class);
    Route::post('user-profile', [UserController::class, 'userProfile']);
    Route::post('user-stats', [UserController::class, 'userStats']);
    Route::put('change-password/{id}', [UserController::class, 'changePassword']);
    Route::put('saveImage/{id}', [UserController::class, 'saveImage']);

    //Dashboard
    Route::group(['middleware' => 'can:view-dashboard'], function () {
        Route::get('daily-stats', [DashboardController::class, 'index']);
        Route::get('result-stats', [DashboardController::class, 'resultStats']);
    });
   
    // Scholarship
    Route::group(['middleware' => 'can:view-scholarship'], function () {
        Route::apiResource('scholarship', ScholarshipController::class);
        Route::get('scholars', [ScholarshipController::class, 'scholars']);
        Route::get('applying-scholarship', [ScholarshipController::class, 'applyingScholarship']);
        Route::get('scholarship-update', [ScholarshipController::class, 'scholarshipUpdate']);
        Route::get('data-today', [ScholarshipController::class, 'getDataToday']);
        Route::post('update-token', [ScholarshipController::class, 'updateToken']);
    });


    // Leaderboards
    Route::group(['middleware' => 'can:view-leaderboards'], function () {
        Route::get('local-rank', [LeaderboardsController::class, 'localRank']);
        Route::get('world-rank', [LeaderboardsController::class, 'worldRank']);
    });

    // Accounts
    Route::group(['middleware' => 'can:view-accounts'], function () {
        Route::apiResource('accounts', AccountsController::class);
        Route::apiResource('roles', RolesController::class);
        Route::post('done-daily', [AccountsController::class, 'doneDaily']);
    });
  
    //Web3
    Route::post('random-message', [Web3Controller::class, 'randomMessage']);
    Route::post('signature', [Web3Controller::class, 'signature']);


    Route::post('logout', [AuthController::class, 'logout']);
});

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('forgot', [AuthController::class, 'sendResetLinkEmail']);
Route::post('reset', [AuthController::class, 'reset']);
