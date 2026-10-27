<?php

use App\Http\Controllers\ConversationRoomSurveyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ConversationRoomSurveyController::class, 'create'])
    ->name('conversation-room.create');

Route::post('/conversation-room/responses', [ConversationRoomSurveyController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('conversation-room.store');

Route::redirect('/conversation-room', '/');

Route::get('/conversation-room/thank-you', [ConversationRoomSurveyController::class, 'thankYou'])
    ->name('conversation-room.thank-you');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
