<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\UserAnswer;
use Illuminate\Support\Facades\DB;

class QuizScoringService
{
    public function scoreAttempt(QuizAttempt $attempt, bool $timedOut = false): QuizAttempt
    {
        if ($attempt->status !== QuizAttempt::STATUS_IN_PROGRESS) {
            return $attempt;
        }

        $attempt->loadMissing(['quiz.questions.options', 'answers']);

        $questions = $attempt->quiz->questions;
        $answersByQuestion = $attempt->answers->keyBy('question_id');

        $score = 0.0;
        $totalPoints = 0.0;
        $correctCount = 0;
        $incorrectCount = 0;
        $unansweredCount = 0;

        DB::transaction(function () use (
            $attempt,
            $questions,
            $answersByQuestion,
            &$score,
            &$totalPoints,
            &$correctCount,
            &$incorrectCount,
            &$unansweredCount,
            $timedOut
        ): void {
            foreach ($questions as $question) {
                $points = (float) $question->points;
                $totalPoints += $points;

                /** @var UserAnswer|null $answer */
                $answer = $answersByQuestion->get($question->id);

                [$isAnswered, $isCorrect] = $this->evaluateAnswer($question, $answer);

                if (! $isAnswered) {
                    $unansweredCount++;
                } elseif ($isCorrect) {
                    $correctCount++;
                    $score += $points;
                } else {
                    $incorrectCount++;
                }

                if ($answer) {
                    $answer->update([
                        'is_correct' => $isAnswered ? $isCorrect : null,
                        'scored_points' => $isCorrect ? $points : 0,
                    ]);
                }
            }

            $attempt->update([
                'status' => $timedOut ? QuizAttempt::STATUS_TIMED_OUT : QuizAttempt::STATUS_SUBMITTED,
                'submitted_at' => now(),
                'score' => $score,
                'total_points' => $totalPoints,
                'correct_count' => $correctCount,
                'incorrect_count' => $incorrectCount,
                'unanswered_count' => $unansweredCount,
            ]);
        });

        return $attempt->fresh(['quiz.questions.options', 'answers.question']);
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private function evaluateAnswer(Question $question, ?UserAnswer $answer): array
    {
        if (! $answer) {
            return [false, false];
        }

        return match ($question->type) {
            Question::TYPE_SINGLE_CHOICE => $this->scoreSingleChoice($question, $answer),
            Question::TYPE_MULTIPLE_CHOICE => $this->scoreMultipleChoice($question, $answer),
            Question::TYPE_TEXT_INPUT => $this->scoreTextInput($question, $answer),
            Question::TYPE_NUMERICAL => $this->scoreNumerical($question, $answer),
            Question::TYPE_BINARY => $this->scoreBinary($question, $answer),
            default => [false, false],
        };
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private function scoreSingleChoice(Question $question, UserAnswer $answer): array
    {
        $selected = $this->normalizeOptionIds($answer->selected_option_ids ?? []);
        if (count($selected) !== 1) {
            return [false, false];
        }

        $correctOptionId = (int) $question->options->firstWhere('is_correct', true)?->id;

        return [true, $selected[0] === $correctOptionId];
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private function scoreMultipleChoice(Question $question, UserAnswer $answer): array
    {
        $selected = $this->normalizeOptionIds($answer->selected_option_ids ?? []);
        if ($selected === []) {
            return [false, false];
        }

        $correct = $question->options
            ->where('is_correct', true)
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->sort()
            ->values()
            ->all();

        return [true, $selected === $correct];
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private function scoreTextInput(Question $question, UserAnswer $answer): array
    {
        $submitted = $this->normalizeText($answer->answer_text);
        if ($submitted === '') {
            return [false, false];
        }

        $aliases = collect($question->accepted_text_aliases ?? [])
            ->map(fn (mixed $alias): string => $this->normalizeText((string) $alias))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [true, in_array($submitted, $aliases, true)];
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private function scoreNumerical(Question $question, UserAnswer $answer): array
    {
        if ($answer->answer_number === null || $question->numerical_correct_answer === null) {
            return [false, false];
        }

        $submitted = (float) $answer->answer_number;
        $correct = (float) $question->numerical_correct_answer;
        $tolerance = (float) ($question->numerical_tolerance ?? 0.01);

        return [true, abs($submitted - $correct) <= $tolerance];
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private function scoreBinary(Question $question, UserAnswer $answer): array
    {
        if ($answer->answer_boolean === null || $question->binary_correct_answer === null) {
            return [false, false];
        }

        return [true, (bool) $answer->answer_boolean === (bool) $question->binary_correct_answer];
    }

    /**
     * @param  array<int, mixed>  $optionIds
     * @return array<int, int>
     */
    private function normalizeOptionIds(array $optionIds): array
    {
        return collect($optionIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function normalizeText(?string $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return '';
        }

        $singleSpaced = preg_replace('/\s+/', ' ', $trimmed) ?? $trimmed;

        return mb_strtolower($singleSpaced);
    }
}
