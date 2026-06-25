<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tymon\JWTAuth\Facades\JWTAuth;

    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('register', [AccountController::class, 'register'])->name('registeration');

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AccountController::class, 'me']); // Get single account
        Route::put('account', [AccountController::class, 'update']); // Update account
        //----------
    });
    
    Route::middleware(['auth:api', 'department.context'])->group(function () { 
        //========== Department =========
        Route::post('department', [DepartmentController::class, 'store'])->name("department_store");
        Route::get('departments', [DepartmentController::class, 'index'])->name("department_list");
        Route::get('department', [DepartmentController::class, 'create'])->name("department_create");
        Route::put('department/{department}', [DepartmentController::class, 'update'])->name("department_update");
        Route::get('department/memberships', [DepartmentController::class, 'colleague']);
        Route::get('department/{department}', [DepartmentController::class, 'show'])->name("department_detail");
        Route::get('sub/department/{department}', [DepartmentController::class, 'subDepartments'])->name("department_sub");
        Route::delete('department/{department}', [DepartmentController::class, 'destroy'])->name("department_remove");        
        Route::get('/departments/{department}/delete-preview', [DepartmentController::class, 'deletePreview']);

        //========== Membership =========
        Route::put('membership/{membership}', [MembershipController::class, 'update'])->name("membership_update");
        Route::get('membership', [MembershipController::class, 'index'])->name("membership_list");
        Route::get('membership/{membership}', [MembershipController::class, 'show'])->name("membership_detail");
        Route::delete('membership/{membership}', [MembershipController::class, 'destroy'])->name("membership_delete");

        //========== Account ===========
        Route::put('account/{account}', [AccountController::class, 'update'])->name("account_update");
        Route::get('account/{account}', [AccountController::class, 'show'])->name("account_detail");
        Route::get('account', [AccountController::class, 'index'])->name("account_list");
        Route::delete('account/{account}', [AccountController::class, 'destroy'])->name("account_delete"); // Delete account

        //=========== Role =============
        Route::get('role/{role}', [RoleController::class, 'show'])->name("role_detail");
        Route::get('role', [RoleController::class, 'index'])->name("role_list");
        Route::post('role', [RoleController::class, 'store'])->name("role_store");
        Route::put('role/{role}', [RoleController::class, 'update'])->name("role_update");
        Route::delete('role/{role}', [RoleController::class, 'destroy'])->name("role_delete");

        //=========== Task =============
        Route::post('task', [TaskController::class, 'store'])->name("task_store");
        Route::put('task/{task}', [TaskController::class, 'update'])->name("task_update");
        Route::delete('task/{task}', [TaskController::class, 'destroy'])->name("task_delete");
        Route::get('task/{task}', [TaskController::class, 'show'])->name("task_detail");
        Route::get('task', [TaskController::class, 'index'])->name("task_list");
        Route::get('tasky', [TaskController::class, 'myTasks'])->name("tasks_my");

        //============ Attachment =======
        Route::post('tasks/{task}/attachments', [AttachmentController::class, 'storeTaskAttachment']);
        Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);
        Route::get('/tasks/{task}/attachments', [TaskController::class, 'attachments']);
        
        //============ AuditLog =========
        Route::get('audit', [AuditLogController::class, 'index'])->name("audit_list");
    });
//