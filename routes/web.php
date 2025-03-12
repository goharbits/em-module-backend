<?php

use App\Http\Controllers\V1\UserControllers\EmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('backend.auth.login');
});

Route::get('/track-email-open', [EmailController::class, 'track_open'])->name('track.email.open');
Route::get('/track-email-click', [EmailController::class, 'track_click'])->name('track.email.click');
Route::get('/get-emails', [EmailController::class, 'get_emails'])->name('emails.get');
