<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AssignmentController;

Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::get('/student/assignments', [AssignmentController::class, 'index']);
});



