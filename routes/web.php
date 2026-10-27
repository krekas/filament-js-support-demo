<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('events/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::post('events/{event:slug}/bookings', [BookingController::class, 'store'])->name('events.bookings.store');
Route::get('bookings/{booking:reference}', [BookingController::class, 'show'])->name('bookings.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
