<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $quiz->title }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Page {{ $page }} of {{ $totalPages }} ({{ $totalQuestions }} questions)
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs uppercase tracking-wide text-gray-500">Time Remaining</p>
                <p id="timer" class="text-2xl font-bold text-red-600">00:00</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between gap-3">
                        <p id="save-status" class="text-sm text-gray-600">Responses are saved automatically.</p>
                        <div class="flex items-center gap-2">
                            <button
                                type="submit"
                                form="submit-form"
                                class="inline-flex items-center rounded-md border px-4 py-2 text-xs font-semibold uppercase tracking-widest transition"
                                style="background-color: #f59e0b; color: #111827; border-color: #111827;"
                            >
                                Submit Quiz
                            </button>
                        </div>
                        <form id="submit-form" method="POST" action="{{ route('attempt.submit', $attempt) }}" class="hidden">
                            @csrf
                            <input type="hidden" name="timed_out" id="timed_out" value="0">
                        </form>
                    </div>
                </div>
            </div>

            @foreach ($questions as $index => $question)
                @php
                    $answer = $answers->get($question->id);
                    $selectedOptionIds = $answer?->selected_option_ids ?? [];
                @endphp
                <section
                    class="question-card bg-white overflow-hidden shadow-sm sm:rounded-lg"
                    data-question-id="{{ $question->id }}"
                    data-question-type="{{ $question->type }}"
                >
                    <div class="p-6 text-gray-900 space-y-4">
                        <h3 class="font-semibold text-lg">
                            Q{{ (($page - 1) * 5) + $index + 1 }}. {{ $question->prompt }}
                            <span class="text-sm text-gray-500">({{ $question->points }} points)</span>
                        </h3>

                        @if ($question->type === \App\Models\Question::TYPE_SINGLE_CHOICE)
                            <div class="space-y-2">
                                @foreach ($question->options as $option)
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="single_{{ $question->id }}"
                                            value="{{ $option->id }}"
                                            class="autosave-input rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @checked(in_array($option->id, $selectedOptionIds, true))
                                        >
                                        <span>{{ $option->label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @elseif ($question->type === \App\Models\Question::TYPE_MULTIPLE_CHOICE)
                            <div class="space-y-2">
                                @foreach ($question->options as $option)
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            value="{{ $option->id }}"
                                            class="autosave-input rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @checked(in_array($option->id, $selectedOptionIds, true))
                                        >
                                        <span>{{ $option->label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @elseif ($question->type === \App\Models\Question::TYPE_TEXT_INPUT)
                            <input
                                type="text"
                                class="autosave-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                value="{{ $answer?->answer_text }}"
                                placeholder="Type your answer"
                            >
                        @elseif ($question->type === \App\Models\Question::TYPE_NUMERICAL)
                            <input
                                type="number"
                                step="any"
                                class="autosave-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                value="{{ $answer?->answer_number }}"
                                placeholder="Type your numerical answer"
                            >
                        @elseif ($question->type === \App\Models\Question::TYPE_BINARY)
                            <div class="space-y-2">
                                <label class="flex items-center gap-2">
                                    <input
                                        type="radio"
                                        name="binary_{{ $question->id }}"
                                        value="1"
                                        class="autosave-input rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        @checked($answer?->answer_boolean === true)
                                    >
                                    <span>Yes / True</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input
                                        type="radio"
                                        name="binary_{{ $question->id }}"
                                        value="0"
                                        class="autosave-input rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        @checked($answer?->answer_boolean === false)
                                    >
                                    <span>No / False</span>
                                </label>
                            </div>
                        @endif
                    </div>
                </section>
            @endforeach

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        @if ($page > 1)
                            <a
                                href="{{ route('attempt.show', ['attempt' => $attempt, 'page' => $page - 1]) }}"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                            >
                                Previous Page
                            </a>
                        @else
                            <span class="inline-flex cursor-not-allowed items-center rounded-md border border-gray-200 bg-gray-100 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-400">
                                Previous Page
                            </span>
                        @endif
                    </div>

                    <div>
                        <button
                            type="submit"
                            form="submit-form"
                            class="inline-flex items-center rounded-md border px-4 py-2 text-xs font-semibold uppercase tracking-widest transition"
                            style="background-color: #f59e0b; color: #111827; border-color: #111827;"
                        >
                            Submit Quiz
                        </button>
                    </div>

                    <div>
                        @if ($page < $totalPages)
                            <a
                                href="{{ route('attempt.show', ['attempt' => $attempt, 'page' => $page + 1]) }}"
                                class="inline-flex items-center rounded-md border px-4 py-2 text-xs font-semibold uppercase tracking-widest transition"
                                style="background-color: #22d3ee; color: #111827; border-color: #111827;"
                            >
                                Next Page
                            </a>
                        @else
                            <span class="inline-flex cursor-not-allowed items-center rounded-md border border-gray-200 bg-gray-100 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-400">
                                Next Page
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const autosaveUrl = @json(route('attempt.autosave', $attempt));
            const heartbeatUrl = @json(route('attempt.heartbeat', $attempt));
            const submitForm = document.getElementById('submit-form');
            const timedOutInput = document.getElementById('timed_out');
            const saveStatus = document.getElementById('save-status');
            const timerEl = document.getElementById('timer');
            const questionCards = Array.from(document.querySelectorAll('.question-card'));
            let remainingSeconds = Number(@json($remainingSeconds));

            const debounceMap = new Map();
            let timingOut = false;

            function formatTime(totalSeconds) {
                const mins = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
                const secs = (totalSeconds % 60).toString().padStart(2, '0');
                return `${mins}:${secs}`;
            }

            function setSaveStatus(message, isError = false) {
                saveStatus.textContent = message;
                saveStatus.classList.toggle('text-red-600', isError);
                saveStatus.classList.toggle('text-gray-600', !isError);
            }

            function queueSave(card, delayMs) {
                const key = card.dataset.questionId;
                const existing = debounceMap.get(key);
                if (existing) {
                    clearTimeout(existing);
                }

                const timeout = setTimeout(() => {
                    saveAnswer(card);
                    debounceMap.delete(key);
                }, delayMs);

                debounceMap.set(key, timeout);
            }

            function buildPayload(card) {
                const payload = {
                    question_id: Number(card.dataset.questionId),
                };

                switch (card.dataset.questionType) {
                    case 'single_choice': {
                        const selected = card.querySelector('input[type="radio"]:checked');
                        payload.selected_option_ids = selected ? [Number(selected.value)] : [];
                        break;
                    }
                    case 'multiple_choice': {
                        payload.selected_option_ids = Array.from(card.querySelectorAll('input[type="checkbox"]:checked'))
                            .map((input) => Number(input.value));
                        break;
                    }
                    case 'text_input': {
                        const input = card.querySelector('input[type="text"]');
                        payload.answer_text = input ? input.value : '';
                        break;
                    }
                    case 'numerical': {
                        const input = card.querySelector('input[type="number"]');
                        payload.answer_number = input && input.value !== '' ? Number(input.value) : null;
                        break;
                    }
                    case 'binary': {
                        const selected = card.querySelector('input[type="radio"]:checked');
                        payload.answer_boolean = selected ? selected.value === '1' : null;
                        break;
                    }
                    default:
                        break;
                }

                return payload;
            }

            async function saveAnswer(card) {
                const payload = buildPayload(card);

                try {
                    const response = await fetch(autosaveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    if (response.status === 409) {
                        const data = await response.json();
                        if (data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }
                    }

                    if (!response.ok) {
                        setSaveStatus('Could not save this response. Please try again.', true);
                        return;
                    }

                    setSaveStatus(`Saved at ${new Date().toLocaleTimeString()}`);
                } catch (error) {
                    setSaveStatus('Autosave failed due to a network issue.', true);
                }
            }

            questionCards.forEach((card) => {
                const delay = ['text_input', 'numerical'].includes(card.dataset.questionType) ? 500 : 0;

                card.querySelectorAll('.autosave-input').forEach((input) => {
                    const eventName = ['text', 'number'].includes(input.type) ? 'input' : 'change';
                    input.addEventListener(eventName, () => queueSave(card, delay));
                });
            });

            function submitForTimeout() {
                if (timingOut) {
                    return;
                }

                timingOut = true;
                timedOutInput.value = '1';
                submitForm.submit();
            }

            function renderTimer() {
                timerEl.textContent = formatTime(Math.max(0, remainingSeconds));
            }

            renderTimer();

            const secondTicker = setInterval(() => {
                remainingSeconds -= 1;
                renderTimer();

                if (remainingSeconds <= 0) {
                    clearInterval(secondTicker);
                    clearInterval(heartbeatTicker);
                    submitForTimeout();
                }
            }, 1000);

            const heartbeatTicker = setInterval(async () => {
                try {
                    const response = await fetch(heartbeatUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    const data = await response.json();

                    if ((response.status === 409 || data.expired || data.submitted) && data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }

                    if (typeof data.remaining_seconds === 'number') {
                        remainingSeconds = Math.min(remainingSeconds, data.remaining_seconds);
                    }
                } catch (error) {
                    // Keep local timer running even if heartbeat fails briefly.
                }
            }, 10000);
        });
    </script>
</x-app-layout>
