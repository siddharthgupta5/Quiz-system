<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $quizzes = Quiz::query()
            ->where('is_active', true)
            ->withCount('questions')
            ->orderBy('id')
            ->get();

        $attempts = QuizAttempt::query()
            ->where('user_id', auth()->id())
            ->with('quiz')
            ->latest('id')
            ->get()
            ->groupBy('quiz_id');

        return view('dashboard', [
            'quizzes' => $quizzes,
            'attempts' => $attempts,
        ]);
    }
}
