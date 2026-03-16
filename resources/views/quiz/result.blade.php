<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $quiz->title }} - Result
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @php
                        $passed = $percentage >= $quiz->pass_percentage;
                    @endphp
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="text-xl font-semibold">Final Score: {{ $attempt->score }} / {{ $attempt->total_points }}</h3>
                            <p class="mt-1 text-gray-600">Percentage: {{ $percentage }}%</p>
                            <p class="mt-1 text-gray-600">
                                Status:
                                <span class="font-semibold {{ $passed ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $passed ? 'Pass' : 'Fail' }}
                                </span>
                                @if ($attempt->status === \App\Models\QuizAttempt::STATUS_TIMED_OUT)
                                    (Timed Out)
                                @endif
                            </p>
                        </div>

                        <div class="text-sm text-gray-700 space-y-1">
                            <p>Correct: <strong>{{ $attempt->correct_count }}</strong></p>
                            <p>Incorrect: <strong>{{ $attempt->incorrect_count }}</strong></p>
                            <p>Unanswered: <strong>{{ $attempt->unanswered_count }}</strong></p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                        >
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            @foreach ($questions as $index => $question)
                @php
                    $answer = $answersByQuestion->get($question->id);
                    $labelClass = match (true) {
                        $answer?->is_correct === true => 'bg-green-100 text-green-800',
                        $answer?->is_correct === false => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-700',
                    };

                    $statusText = match (true) {
                        $answer?->is_correct === true => 'Correct',
                        $answer?->is_correct === false => 'Incorrect',
                        default => 'Unanswered',
                    };

                    $submittedAnswer = 'Not answered';

                    if ($question->type === \App\Models\Question::TYPE_SINGLE_CHOICE) {
                        $selected = collect($answer?->selected_option_ids ?? []);
                        $submittedAnswer = $question->options
                            ->whereIn('id', $selected)
                            ->pluck('label')
                            ->implode(', ');
                        $submittedAnswer = $submittedAnswer !== '' ? $submittedAnswer : 'Not answered';
                    }

                    if ($question->type === \App\Models\Question::TYPE_MULTIPLE_CHOICE) {
                        $selected = collect($answer?->selected_option_ids ?? []);
                        $submittedAnswer = $question->options
                            ->whereIn('id', $selected)
                            ->pluck('label')
                            ->implode(', ');
                        $submittedAnswer = $submittedAnswer !== '' ? $submittedAnswer : 'Not answered';
                    }

                    if ($question->type === \App\Models\Question::TYPE_TEXT_INPUT) {
                        $submittedAnswer = $answer?->answer_text ?: 'Not answered';
                    }

                    if ($question->type === \App\Models\Question::TYPE_NUMERICAL) {
                        $submittedAnswer = $answer?->answer_number !== null
                            ? (string) $answer->answer_number
                            : 'Not answered';
                    }

                    if ($question->type === \App\Models\Question::TYPE_BINARY) {
                        $submittedAnswer = $answer?->answer_boolean === null
                            ? 'Not answered'
                            : ($answer->answer_boolean ? 'Yes / True' : 'No / False');
                    }
                @endphp

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-semibold text-lg">Q{{ $index + 1 }}. {{ $question->prompt }}</h3>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $labelClass }}">{{ $statusText }}</span>
                        </div>

                        <p class="text-sm text-gray-700">Your answer: <strong>{{ $submittedAnswer }}</strong></p>
                        <p class="text-sm text-gray-600">Score: {{ $answer?->scored_points ?? 0 }} / {{ $question->points }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
