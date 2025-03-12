<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// updateRolesAndPermissions();

Route::get('unauthenticated', function(){
    return sendError('Un-authenticated: Please login to continue.', false, 401);
})->name('unauthenticated');

Route::prefix('v1')->group(function () {
    include_once('versions/v1/v1.php');
});

Route::prefix('v2')->group(function () {
    include_once('versions/v2/v2.php');
});


