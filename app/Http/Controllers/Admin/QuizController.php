<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $quizzes = Quiz::query()
            ->withCount(['questions', 'attempts'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.quizzes.index', [
            'quizzes' => $quizzes,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.quizzes.create', [
            'quiz' => new Quiz([
                'time_limit_seconds' => 900,
                'pass_percentage' => 60,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateQuiz($request);

        $quiz = Quiz::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'time_limit_seconds' => (int) $validated['time_limit_seconds'],
            'pass_percentage' => (int) $validated['pass_percentage'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.quizzes.edit', $quiz)
            ->with('status', 'Quiz created. You can now add questions.');
    }

    public function edit(Quiz $quiz): View
    {
        $quiz->load(['questions.options']);

        return view('admin.quizzes.edit', [
            'quiz' => $quiz,
            'questionTypes' => $this->questionTypes(),
        ]);
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $validated = $this->validateQuiz($request);

        $quiz->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'time_limit_seconds' => (int) $validated['time_limit_seconds'],
            'pass_percentage' => (int) $validated['pass_percentage'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.quizzes.edit', $quiz)
            ->with('status', 'Quiz updated successfully.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $quiz->delete();

        return redirect()
            ->route('admin.quizzes.index')
            ->with('status', 'Quiz deleted successfully.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function validateQuiz(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'time_limit_seconds' => ['required', 'integer', 'min:30', 'max:86400'],
            'pass_percentage' => ['required', 'integer', 'between:0,100'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function questionTypes(): array
    {
        return [
            Question::TYPE_SINGLE_CHOICE => 'Single Choice',
            Question::TYPE_MULTIPLE_CHOICE => 'Multiple Choice',
            Question::TYPE_TEXT_INPUT => 'Text Input',
            Question::TYPE_NUMERICAL => 'Numerical',
            Question::TYPE_BINARY => 'Binary (Yes/No)',
        ];
    }
}
