<?php

use App\Http\Controllers\Auth\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Post\PostController;
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::post('/', [HomeController::class, 'searchUser'])->name('index');

Route::get('/register', function () {
    return view('auth.register');
});

Route::post('/register', [RegisterController::class, 'register'])->name('register');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [LoginController::class, 'login'])->name('login');

Route::middleware('auth')->group(function () {

    // Authentication related routes
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // User profile related routes
    Route::get('/profile/{id}/show', [ProfileController::class, 'show'])->name('profile.show');

    Route::post('/profile/{id}/show', [ProfileController::class, 'searchUser'])->name('profile.show');

    Route::get('/edit-profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::post('/update-profile', [ProfileController::class, 'update'])->name('profile.update');

    // Route::post('/');

    Route::resource('posts', PostController::class);
    Route::post('/posts/{post}', [PostController::class, 'searchUser'])->name('posts.show');
});