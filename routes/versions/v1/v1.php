<?php

use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\UserControllers\EmailController;
use App\Http\Controllers\V1\WorldController;
use Illuminate\Support\Facades\Route;



use Illuminate\Support\Facades\Mail;
use App\Mail\CampaignMail;



Route::get('/send-email-new', function () {
    try {
        Mail::to('hamzaa.bitsclan@gmail.com')->send(new CampaignMail(1, 'active'));
        return "Test email sent successfully!";
    } catch (\Exception $e) {
        return "Failed to send email: " . $e->getMessage();
    }
});



Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');

Route::middleware('auth:api')->group(function(){

    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('/change_password', [AuthController::class, 'change_password'])->name('auth.change_password');
    Route::get('/profile', [AuthController::class, 'profile'])->name('auth.profile');
    Route::post('/update_profile', [AuthController::class, 'update_profile'])->name('auth.update_profile');

    Route::get('/timezones/get', function () {
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        return $timezones;
    });

    Route::get('/get_countries', [WorldController::class, 'get_countries'])->name('get_countries');
    Route::get('/get_cities', [WorldController::class, 'get_cities'])->name('get_cities');

    Route::post('/send-email', [EmailController::class, 'send'])->name('auth.send');

    Route::prefix('user')->group(function () {
        include_once('user.php');
    });

    Route::prefix('admin')->group(function () {
        include_once('admin.php');
    });
});
