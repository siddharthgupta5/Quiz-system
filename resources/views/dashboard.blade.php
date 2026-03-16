<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Quiz Dashboard') }}
            </h2>

            @if (auth()->user()?->is_admin)
                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('admin.quizzes.index') }}"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    >
                        Manage Quizzes
                    </a>
                    <a
                        href="{{ route('admin.quizzes.create') }}"
                        class="inline-flex items-center rounded-md border px-4 py-2 text-xs font-semibold uppercase tracking-widest transition"
                        style="background-color: #22d3ee; color: #111827; border-color: #111827;"
                    >
                        Create Quiz
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">
                @foreach ($quizzes as $quiz)
                    @php
                        $quizAttempts = $attempts->get($quiz->id) ?? collect();
                        $hasAttempted = $quizAttempts->isNotEmpty();
                        $activeAttempt = $quizAttempts->first(function ($attempt) {
                            return $attempt->status === \App\Models\QuizAttempt::STATUS_IN_PROGRESS && ! $attempt->isExpired();
                        });
                        $latestAttempt = $quizAttempts->first();
                        $statusLabel = $activeAttempt ? 'In Progress' : ($hasAttempted ? 'Already Attempted' : 'Not Attempted');
                        $statusClass = $activeAttempt
                            ? 'bg-yellow-100 text-yellow-700'
                            : ($hasAttempted ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700');
                    @endphp

                    <div class="bg-white overflow-hidden border border-slate-200 shadow-sm ring-1 ring-slate-100 sm:rounded-lg transition-shadow hover:shadow-md">
                        <div class="p-6 text-gray-900">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg font-semibold">{{ $quiz->title }}</h3>
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">{{ $quiz->description }}</p>
                                    <p class="mt-3 text-sm text-gray-700">
                                        Questions: <strong>{{ $quiz->questions_count }}</strong>
                                        | Time limit: <strong>{{ intdiv($quiz->time_limit_seconds, 60) }} min</strong>
                                        | Pass: <strong>{{ $quiz->pass_percentage }}%</strong>
                                    </p>
                                </div>

                                <div class="flex items-center gap-3">
                                    @if ($activeAttempt)
                                        <a
                                            href="{{ route('attempt.show', $activeAttempt) }}"
                                            class="inline-flex items-center rounded-md border px-4 py-2 text-xs font-semibold uppercase tracking-widest transition"
                                            style="background-color: #22d3ee; color: #111827; border-color: #111827;"
                                        >
                                            Continue Quiz
                                        </a>
                                    @else
                                        <form method="POST" action="{{ route('attempt.start', $quiz) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="inline-flex items-center rounded-md border px-4 py-2 text-xs font-semibold uppercase tracking-widest transition"
                                                style="background-color: #f59e0b; color: #111827; border-color: #111827;"
                                            >
                                                {{ $hasAttempted ? 'Attempt Again' : 'Attempt Quiz' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            @if ($latestAttempt)
                                <p class="mt-4 text-sm text-gray-600">
                                    Last attempt: {{ ucfirst(str_replace('_', ' ', $latestAttempt->status)) }}
                                    ({{ $latestAttempt->score }}/{{ $latestAttempt->total_points }})
                                    <a
                                        href="{{ route('attempt.result', $latestAttempt) }}"
                                        class="ml-2 inline-flex items-center rounded-md border px-3 py-1 text-xs font-semibold uppercase tracking-widest transition"
                                        style="background-color: #111827; color: #ffffff; border-color: #111827;"
                                    >
                                        View result
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if ($quizzes->isEmpty())
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            No active quizzes are available right now.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
