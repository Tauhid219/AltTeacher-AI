<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('district_admin')) {
        return redirect()->route('district.dashboard');
    } elseif ($user->hasRole('school_admin')) {
        return redirect()->route('school.dashboard');
    } elseif ($user->hasRole('teacher')) {
        return redirect()->route('teacher.dashboard');
    }
    abort(403, 'Unauthorized action.');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:district_admin'])->prefix('district')->name('district.')->group(function () {
    Route::get('/dashboard', function () {
        return "District Admin Dashboard - Layout & Dark/Light theme will be integrated in Phase 3";
    })->name('dashboard');
});

Route::middleware(['auth', 'role:school_admin'])->prefix('school')->name('school.')->group(function () {
    Route::get('/dashboard', function () {
        return "School Admin Dashboard - Layout & Dark/Light theme will be integrated in Phase 3";
    })->name('dashboard');
});

Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', function () {
        return "Substitute Teacher Dashboard - Layout & Dark/Light theme will be integrated in Phase 3";
    })->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
