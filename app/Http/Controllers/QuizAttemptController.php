<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\UserAnswer;
use App\Services\QuizScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuizAttemptController extends Controller
{
    public function __construct(private readonly QuizScoringService $quizScoringService)
    {
    }

    public function start(Quiz $quiz): RedirectResponse
    {
        $this->ensureActiveQuiz($quiz);

        $inProgressAttempt = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', auth()->id())
            ->where('status', QuizAttempt::STATUS_IN_PROGRESS)
            ->latest('id')
            ->first();

        if ($inProgressAttempt) {
            if ($inProgressAttempt->isExpired()) {
                $this->quizScoringService->scoreAttempt($inProgressAttempt, true);

                return redirect()->route('attempt.result', $inProgressAttempt);
            }

            return redirect()->route('attempt.show', $inProgressAttempt);
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => auth()->id(),
            'status' => QuizAttempt::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'ends_at' => now()->addSeconds((int) $quiz->time_limit_seconds),
        ]);

        return redirect()->route('attempt.show', $attempt);
    }

    public function show(Request $request, QuizAttempt $attempt): View|RedirectResponse
    {
        $this->authorizeAttempt($attempt);

        if ($attempt->status !== QuizAttempt::STATUS_IN_PROGRESS) {
            return redirect()->route('attempt.result', $attempt);
        }

        if ($attempt->isExpired()) {
            $this->quizScoringService->scoreAttempt($attempt, true);

            return redirect()->route('attempt.result', $attempt);
        }

        $attempt->load(['quiz.questions.options']);

        $questions = $attempt->quiz->questions;
        $perPage = 5;
        $totalQuestions = $questions->count();
        $totalPages = max(1, (int) ceil($totalQuestions / $perPage));
        $page = min(max(1, (int) $request->integer('page', 1)), $totalPages);

        $visibleQuestions = $questions
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        $answers = $attempt->answers()
            ->whereIn('question_id', $visibleQuestions->pluck('id'))
            ->get()
            ->keyBy('question_id');

        return view('quiz.attempt', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'questions' => $visibleQuestions,
            'answers' => $answers,
            'page' => $page,
            'totalPages' => $totalPages,
            'remainingSeconds' => $attempt->remainingSeconds(),
            'totalQuestions' => $totalQuestions,
        ]);
    }

    public function autosave(Request $request, QuizAttempt $attempt): JsonResponse
    {
        $this->authorizeAttempt($attempt);

        if ($attempt->status !== QuizAttempt::STATUS_IN_PROGRESS) {
            return response()->json([
                'message' => 'Attempt is already submitted.',
            ], 422);
        }

        if ($attempt->isExpired()) {
            $this->quizScoringService->scoreAttempt($attempt, true);

            return response()->json([
                'expired' => true,
                'redirect' => route('attempt.result', $attempt),
            ], 409);
        }

        $validated = $request->validate([
            'question_id' => ['required', 'integer'],
            'selected_option_ids' => ['nullable', 'array'],
            'selected_option_ids.*' => ['integer'],
            'answer_text' => ['nullable', 'string'],
            'answer_number' => ['nullable', 'numeric'],
            'answer_boolean' => ['nullable', 'boolean'],
        ]);

        $question = Question::query()
            ->where('quiz_id', $attempt->quiz_id)
            ->whereKey($validated['question_id'])
            ->with('options')
            ->firstOrFail();

        $payload = $this->buildAnswerPayload($question, $validated);

        DB::transaction(function () use ($attempt, $question, $payload): void {
            UserAnswer::updateOrCreate(
                [
                    'quiz_attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                ],
                array_merge($payload, [
                    'answered_at' => now(),
                ])
            );
        });

        return response()->json([
            'saved' => true,
            'saved_at' => now()->toISOString(),
        ]);
    }

    public function heartbeat(QuizAttempt $attempt): JsonResponse
    {
        $this->authorizeAttempt($attempt);

        if ($attempt->status !== QuizAttempt::STATUS_IN_PROGRESS) {
            return response()->json([
                'submitted' => true,
                'redirect' => route('attempt.result', $attempt),
            ]);
        }

        if ($attempt->isExpired()) {
            $this->quizScoringService->scoreAttempt($attempt, true);

            return response()->json([
                'expired' => true,
                'redirect' => route('attempt.result', $attempt),
            ], 409);
        }

        return response()->json([
            'remaining_seconds' => $attempt->remainingSeconds(),
        ]);
    }

    public function submit(Request $request, QuizAttempt $attempt): RedirectResponse
    {
        $this->authorizeAttempt($attempt);

        if ($attempt->status !== QuizAttempt::STATUS_IN_PROGRESS) {
            return redirect()->route('attempt.result', $attempt);
        }

        $timedOut = $attempt->isExpired() || $request->boolean('timed_out');
        $this->quizScoringService->scoreAttempt($attempt, $timedOut);

        return redirect()->route('attempt.result', $attempt);
    }

    public function result(QuizAttempt $attempt): View
    {
        $this->authorizeAttempt($attempt);

        if ($attempt->status === QuizAttempt::STATUS_IN_PROGRESS && $attempt->isExpired()) {
            $attempt = $this->quizScoringService->scoreAttempt($attempt, true);
        }

        $attempt->load([
            'quiz.questions.options',
            'answers.question',
        ]);

        $answersByQuestion = $attempt->answers->keyBy('question_id');

        return view('quiz.result', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'questions' => $attempt->quiz->questions,
            'answersByQuestion' => $answersByQuestion,
            'percentage' => $attempt->total_points > 0
                ? round(($attempt->score / $attempt->total_points) * 100, 2)
                : 0,
        ]);
    }

    private function authorizeAttempt(QuizAttempt $attempt): void
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
    }

    private function ensureActiveQuiz(Quiz $quiz): void
    {
        abort_unless($quiz->is_active, 404);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function buildAnswerPayload(Question $question, array $validated): array
    {
        return match ($question->type) {
            Question::TYPE_SINGLE_CHOICE => $this->buildSingleChoicePayload($question, $validated),
            Question::TYPE_MULTIPLE_CHOICE => $this->buildMultipleChoicePayload($question, $validated),
            Question::TYPE_TEXT_INPUT => [
                'answer_text' => isset($validated['answer_text']) ? trim((string) $validated['answer_text']) : null,
                'answer_number' => null,
                'answer_boolean' => null,
                'selected_option_ids' => null,
            ],
            Question::TYPE_NUMERICAL => [
                'answer_text' => null,
                'answer_number' => $validated['answer_number'] ?? null,
                'answer_boolean' => null,
                'selected_option_ids' => null,
            ],
            Question::TYPE_BINARY => [
                'answer_text' => null,
                'answer_number' => null,
                'answer_boolean' => array_key_exists('answer_boolean', $validated)
                    ? filter_var($validated['answer_boolean'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
                    : null,
                'selected_option_ids' => null,
            ],
            default => [
                'answer_text' => null,
                'answer_number' => null,
                'answer_boolean' => null,
                'selected_option_ids' => null,
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function buildSingleChoicePayload(Question $question, array $validated): array
    {
        $optionIds = collect($validated['selected_option_ids'] ?? [])
            ->map(fn (mixed $id): int => (int) $id)
            ->filter()
            ->values();

        $questionOptionIds = $question->options->pluck('id')->all();
        $filtered = $optionIds
            ->filter(fn (int $id): bool => in_array($id, $questionOptionIds, true))
            ->take(1)
            ->values()
            ->all();

        return [
            'answer_text' => null,
            'answer_number' => null,
            'answer_boolean' => null,
            'selected_option_ids' => $filtered === [] ? null : $filtered,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function buildMultipleChoicePayload(Question $question, array $validated): array
    {
        $optionIds = collect($validated['selected_option_ids'] ?? [])
            ->map(fn (mixed $id): int => (int) $id)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $questionOptionIds = $question->options->pluck('id')->all();
        $filtered = $optionIds
            ->filter(fn (int $id): bool => in_array($id, $questionOptionIds, true))
            ->values()
            ->all();

        return [
            'answer_text' => null,
            'answer_number' => null,
            'answer_boolean' => null,
            'selected_option_ids' => $filtered === [] ? null : $filtered,
        ];
    }
}
