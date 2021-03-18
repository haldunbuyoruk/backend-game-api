<?php

use App\Http\Controllers\UserController;

Route::get('/api/v1/users', [UserController::class, 'show']);
