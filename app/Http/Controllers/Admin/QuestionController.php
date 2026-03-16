<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function create(Quiz $quiz): View
    {
        return view('admin.questions.create', [
            'quiz' => $quiz,
            'question' => new Question(),
            'questionTypes' => $this->questionTypes(),
            'formDefaults' => [
                'options_text' => '',
                'correct_option_indexes' => '',
                'accepted_aliases' => '',
                'binary_correct_answer' => '',
            ],
        ]);
    }

    public function store(Request $request, Quiz $quiz): RedirectResponse
    {
        $validated = $this->validateQuestion($request);

        $question = $quiz->questions()->create($this->buildQuestionPayload($quiz, $validated));

        $this->syncQuestionOptions($question, $validated);

        return redirect()
            ->route('admin.quizzes.edit', $quiz)
            ->with('status', 'Question added successfully.');
    }

    public function edit(Quiz $quiz, Question $question): View
    {
        $this->ensureQuestionBelongsToQuiz($quiz, $question);
        $question->load('options');

        return view('admin.questions.edit', [
            'quiz' => $quiz,
            'question' => $question,
            'questionTypes' => $this->questionTypes(),
            'formDefaults' => [
                'options_text' => $question->options->pluck('label')->implode(PHP_EOL),
                'correct_option_indexes' => $question->options
                    ->values()
                    ->map(function ($option, $index): ?int {
                        return $option->is_correct ? $index + 1 : null;
                    })
                    ->filter()
                    ->implode(','),
                'accepted_aliases' => collect($question->accepted_text_aliases ?? [])->implode(PHP_EOL),
                'binary_correct_answer' => $question->binary_correct_answer === null
                    ? ''
                    : ((bool) $question->binary_correct_answer ? '1' : '0'),
            ],
        ]);
    }

    public function update(Request $request, Quiz $quiz, Question $question): RedirectResponse
    {
        $this->ensureQuestionBelongsToQuiz($quiz, $question);
        $validated = $this->validateQuestion($request);

        $question->update($this->buildQuestionPayload($quiz, $validated, $question));
        $this->syncQuestionOptions($question, $validated);

        return redirect()
            ->route('admin.quizzes.edit', $quiz)
            ->with('status', 'Question updated successfully.');
    }

    public function destroy(Quiz $quiz, Question $question): RedirectResponse
    {
        $this->ensureQuestionBelongsToQuiz($quiz, $question);

        $question->delete();

        return redirect()
            ->route('admin.quizzes.edit', $quiz)
            ->with('status', 'Question deleted successfully.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function validateQuestion(Request $request): array
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', array_keys($this->questionTypes()))],
            'prompt' => ['required', 'string'],
            'points' => ['required', 'numeric', 'min:0'],
            'order_index' => ['nullable', 'integer', 'min:1'],
            'accepted_aliases' => ['nullable', 'string'],
            'numerical_correct_answer' => ['nullable', 'numeric'],
            'numerical_tolerance' => ['nullable', 'numeric', 'min:0'],
            'binary_correct_answer' => ['nullable', 'in:0,1'],
            'options_text' => ['nullable', 'string'],
            'correct_option_indexes' => ['nullable', 'string'],
        ]);

        if ($validated['type'] === Question::TYPE_TEXT_INPUT && $this->parseAliases($validated['accepted_aliases'] ?? '') === []) {
            throw ValidationException::withMessages([
                'accepted_aliases' => 'Please provide at least one accepted answer alias.',
            ]);
        }

        if ($validated['type'] === Question::TYPE_NUMERICAL && is_null($validated['numerical_correct_answer'] ?? null)) {
            throw ValidationException::withMessages([
                'numerical_correct_answer' => 'Please provide the correct numerical answer.',
            ]);
        }

        if ($validated['type'] === Question::TYPE_BINARY && ! in_array(($validated['binary_correct_answer'] ?? ''), ['0', '1'], true)) {
            throw ValidationException::withMessages([
                'binary_correct_answer' => 'Please choose the correct binary answer.',
            ]);
        }

        if (in_array($validated['type'], [Question::TYPE_SINGLE_CHOICE, Question::TYPE_MULTIPLE_CHOICE], true)) {
            $this->validateChoiceData($validated);
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function validateChoiceData(array $validated): void
    {
        $options = $this->parseOptions($validated['options_text'] ?? '');

        if (count($options) < 2) {
            throw ValidationException::withMessages([
                'options_text' => 'Please provide at least two options for choice questions.',
            ]);
        }

        $correctIndexes = $this->parseCorrectIndexes($validated['correct_option_indexes'] ?? '');
        if ($correctIndexes === []) {
            throw ValidationException::withMessages([
                'correct_option_indexes' => 'Please specify at least one correct option index.',
            ]);
        }

        if ($validated['type'] === Question::TYPE_SINGLE_CHOICE && count($correctIndexes) !== 1) {
            throw ValidationException::withMessages([
                'correct_option_indexes' => 'Single choice questions must have exactly one correct option index.',
            ]);
        }

        $maxIndex = count($options);
        foreach ($correctIndexes as $index) {
            if ($index < 1 || $index > $maxIndex) {
                throw ValidationException::withMessages([
                    'correct_option_indexes' => "Correct option indexes must be between 1 and {$maxIndex}.",
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function buildQuestionPayload(Quiz $quiz, array $validated, ?Question $existingQuestion = null): array
    {
        $orderIndex = (int) ($validated['order_index'] ?? ($existingQuestion?->order_index ?? ($quiz->questions()->max('order_index') + 1)));
        $type = $validated['type'];

        $payload = [
            'type' => $type,
            'prompt' => trim((string) $validated['prompt']),
            'points' => (float) $validated['points'],
            'order_index' => $orderIndex,
            'accepted_text_aliases' => null,
            'numerical_correct_answer' => null,
            'numerical_tolerance' => 0.01,
            'binary_correct_answer' => null,
        ];

        if ($type === Question::TYPE_TEXT_INPUT) {
            $payload['accepted_text_aliases'] = $this->parseAliases($validated['accepted_aliases'] ?? '');
        }

        if ($type === Question::TYPE_NUMERICAL) {
            $payload['numerical_correct_answer'] = (float) $validated['numerical_correct_answer'];
            $payload['numerical_tolerance'] = (float) ($validated['numerical_tolerance'] ?? 0.01);
        }

        if ($type === Question::TYPE_BINARY) {
            $payload['binary_correct_answer'] = ($validated['binary_correct_answer'] ?? '') === '1';
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncQuestionOptions(Question $question, array $validated): void
    {
        $question->options()->delete();

        if (! in_array($question->type, [Question::TYPE_SINGLE_CHOICE, Question::TYPE_MULTIPLE_CHOICE], true)) {
            return;
        }

        $options = $this->parseOptions($validated['options_text'] ?? '');
        $correctIndexes = $this->parseCorrectIndexes($validated['correct_option_indexes'] ?? '');

        $records = [];
        foreach ($options as $index => $label) {
            $order = $index + 1;
            $records[] = [
                'label' => $label,
                'value' => (string) $order,
                'is_correct' => in_array($order, $correctIndexes, true),
                'order_index' => $order,
            ];
        }

        $question->options()->createMany($records);
    }

    private function ensureQuestionBelongsToQuiz(Quiz $quiz, Question $question): void
    {
        abort_unless($question->quiz_id === $quiz->id, 404);
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

    /**
     * @return array<int, string>
     */
    private function parseOptions(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function parseCorrectIndexes(string $value): array
    {
        return collect(preg_split('/[\s,]+/', trim($value)) ?: [])
            ->map(fn (string $index): int => (int) $index)
            ->filter(fn (int $index): bool => $index > 0)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function parseAliases(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n|,/', $value) ?: [])
            ->map(fn (string $alias): string => trim($alias))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
