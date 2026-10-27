<?php

use App\Http\Controllers\Admin\AnalyticsDataController;
use App\Http\Controllers\Admin\AnalyticsReportController;
use App\Http\Controllers\Admin\CommonAnswersController;
use App\Http\Controllers\Admin\FeedbackReviewController;
use App\Http\Controllers\Admin\PublishedSurveyController;
use App\Http\Controllers\Admin\SurveyAlertReadAllController;
use App\Http\Controllers\Admin\SurveyAlertReadController;
use App\Http\Controllers\Admin\SurveyAlertResolutionController;
use App\Http\Controllers\Admin\SurveyPreviewController;
use App\Http\Controllers\Admin\SurveyQuestionController;
use App\Http\Controllers\Admin\SurveyQuestionDuplicateController;
use App\Http\Controllers\Admin\SurveyQuestionMoveController;
use App\Http\Controllers\Admin\SurveyQuestionOrderController;
use App\Http\Controllers\Admin\SurveySectionController;
use App\Http\Controllers\Admin\SurveySectionMoveController;
use App\Http\Controllers\Admin\SurveySectionOrderController;
use App\Http\Controllers\ConversationRoomSurveyController;
use App\Http\Controllers\DashboardController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->middleware('admin')->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('analytics', AnalyticsDataController::class)->name('analytics.show');
        Route::get('common-answers', CommonAnswersController::class)->name('common-answers');
        Route::post('reports/pdf', AnalyticsReportController::class)
            ->middleware('throttle:analytics-pdf')
            ->name('reports.pdf');
        Route::put('feedback/{answer}/review', FeedbackReviewController::class)
            ->name('feedback.review');
        Route::put('alerts/read-all', SurveyAlertReadAllController::class)
            ->name('alerts.read-all');
        Route::put('alerts/{alert}/read', SurveyAlertReadController::class)
            ->name('alerts.read');
        Route::put('alerts/{alert}/resolve', SurveyAlertResolutionController::class)
            ->name('alerts.resolve');
        Route::resource('questions', SurveyQuestionController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['questions' => 'question']);
        Route::post('questions/{question}/duplicate', SurveyQuestionDuplicateController::class)
            ->name('questions.duplicate');
        Route::patch('questions/{question}/move', SurveyQuestionMoveController::class)
            ->name('questions.move');
        Route::put('question-order', SurveyQuestionOrderController::class)
            ->name('question-order.update');
        Route::resource('sections', SurveySectionController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['sections' => 'section']);
        Route::patch('sections/{section}/move', SurveySectionMoveController::class)
            ->name('sections.move');
        Route::put('section-order', SurveySectionOrderController::class)
            ->name('section-order.update');
        Route::post('published-survey', PublishedSurveyController::class)
            ->name('published-survey.store');
        Route::get('survey-preview', SurveyPreviewController::class)
            ->name('survey-preview');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
