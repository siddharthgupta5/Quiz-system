<?php

use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizAttemptController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('quizzes', AdminQuizController::class)->except('show');

        Route::get('quizzes/{quiz}/questions/create', [AdminQuestionController::class, 'create'])->name('quizzes.questions.create');
        Route::post('quizzes/{quiz}/questions', [AdminQuestionController::class, 'store'])->name('quizzes.questions.store');
        Route::get('quizzes/{quiz}/questions/{question}/edit', [AdminQuestionController::class, 'edit'])->name('quizzes.questions.edit');
        Route::put('quizzes/{quiz}/questions/{question}', [AdminQuestionController::class, 'update'])->name('quizzes.questions.update');
        Route::delete('quizzes/{quiz}/questions/{question}', [AdminQuestionController::class, 'destroy'])->name('quizzes.questions.destroy');
    });

    Route::post('/quizzes/{quiz}/start', [QuizAttemptController::class, 'start'])->name('attempt.start');
    Route::get('/attempts/{attempt}', [QuizAttemptController::class, 'show'])->name('attempt.show');
    Route::post('/attempts/{attempt}/autosave', [QuizAttemptController::class, 'autosave'])->name('attempt.autosave');
    Route::post('/attempts/{attempt}/heartbeat', [QuizAttemptController::class, 'heartbeat'])->name('attempt.heartbeat');
    Route::post('/attempts/{attempt}/submit', [QuizAttemptController::class, 'submit'])->name('attempt.submit');
    Route::get('/attempts/{attempt}/result', [QuizAttemptController::class, 'result'])->name('attempt.result');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
