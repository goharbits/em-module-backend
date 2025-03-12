<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\AdminControllers\RoleController;
use App\Http\Controllers\V1\AdminControllers\AdminUserController;
use App\Http\Controllers\V1\AdminControllers\PermissionController;
use App\Http\Controllers\V1\AdminControllers\EmailTemplateTypeController;
use App\Http\Controllers\V1\AdminControllers\StatusController;

Route::middleware('dynamic.permission')->group(function () {

    Route::prefix('email_template_type')->group(function () {

        $module = 'email_template_type';

        Route::post('create', [EmailTemplateTypeController::class, 'store'])->name("create_$module");
        Route::post('update', [EmailTemplateTypeController::class, 'update'])->name("update_$module");
        Route::post('delete', [EmailTemplateTypeController::class, 'delete'])->name("delete_$module");
        Route::get('get', [EmailTemplateTypeController::class, 'get'])->name("get_$module");
    });

    Route::prefix('role')->group(function () {

        $module = 'role';

        Route::post('create', [RoleController::class, 'store'])->name("create_$module");
        Route::post('update', [RoleController::class, 'update'])->name("update_$module");
        Route::post('delete', [RoleController::class, 'delete'])->name("delete_$module");
        Route::get('get', [RoleController::class, 'get'])->name("get_$module");
        Route::post('assign_permissions', [RoleController::class, 'assign_permissions'])->name("assign_permissions_$module");
    });

    Route::prefix('permission')->group(function () {

        $module = 'permission';

        Route::get('get', [PermissionController::class, 'get'])->name("get_$module");
    });

    Route::prefix('admin_user')->group(function () {

        $module = 'admin_user';

        Route::post('create', [AdminUserController::class, 'store'])->name("create_$module");
        Route::post('update', [AdminUserController::class, 'update'])->name("update_$module");
        Route::post('delete', [AdminUserController::class, 'delete'])->name("delete_$module");
        Route::get('get', [AdminUserController::class, 'get'])->name("get_$module");
        Route::post('restore', [AdminUserController::class, 'restore'])->name("restore_$module");
        Route::post('change_password', [AdminUserController::class, 'change_password'])->name("change_password_$module");
        Route::post('assign_roles', [AdminUserController::class, 'assign_roles'])->name("assign_roles_$module");
    });

    Route::get('system_modules', [PermissionController::class, 'get_modules'])->name('get_modules');

    Route::prefix('status')->group(function () {

        $module = 'status';

        Route::post('create', [StatusController::class, 'store'])->name("create_$module");
        Route::post('update', [StatusController::class, 'update'])->name("update_$module");
        Route::post('delete', [StatusController::class, 'delete'])->name("delete_$module");
        Route::get('get', [StatusController::class, 'get'])->name("get_$module");
    });
});
