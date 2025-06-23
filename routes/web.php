<?php

use Illuminate\Support\Facades\Route;
use Mdkaif\ProUpdaterGit\Http\Controllers\UpdateController; // Updated namespace

// Define the route for triggering the update process via a POST request.
Route::post('update', [UpdateController::class, 'update'])->name('update');

// Define a GET route for directly accessing or displaying the setup modal.
Route::get('setup', function () {
    return view('pro-updater-git::setup-modal');
})->name('setup');
