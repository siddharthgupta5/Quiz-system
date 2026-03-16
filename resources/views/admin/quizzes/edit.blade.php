<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Quiz: {{ $quiz->title }}</h2>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.quizzes.questions.create', $quiz) }}"
                    class="inline-flex items-center rounded-md border px-4 py-2 text-xs font-semibold uppercase tracking-widest transition"
                    style="background-color: #22d3ee; color: #111827; border-color: #111827;"
                >
                    Add Question
                </a>
                <a
                    href="{{ route('admin.quizzes.index') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                >
                    Back to Quizzes
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.quizzes.update', $quiz) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Quiz Title</label>
                            <input id="title" name="title" type="text" value="{{ old('title', $quiz->title) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $quiz->description) }}</textarea>
                            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="time_limit_seconds" class="block text-sm font-medium text-gray-700">Time Limit (seconds)</label>
                                <input id="time_limit_seconds" name="time_limit_seconds" type="number" min="30" value="{{ old('time_limit_seconds', $quiz->time_limit_seconds) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('time_limit_seconds') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="pass_percentage" class="block text-sm font-medium text-gray-700">Pass Percentage</label>
                                <input id="pass_percentage" name="pass_percentage" type="number" min="0" max="100" value="{{ old('pass_percentage', $quiz->pass_percentage) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('pass_percentage') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', $quiz->is_active))>
                                <span class="text-sm text-gray-700">Active quiz (visible on dashboard)</span>
                            </label>
                        </div>

                        <div>
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-md border px-4 py-2 text-xs font-semibold uppercase tracking-widest transition"
                                style="background-color: #f59e0b; color: #111827; border-color: #111827;"
                            >
                                Update Quiz
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <h3 class="text-lg font-semibold">Questions</h3>
                        <a
                            href="{{ route('admin.quizzes.questions.create', $quiz) }}"
                            class="inline-flex items-center rounded-md border px-3 py-1.5 text-xs font-semibold uppercase tracking-widest transition"
                            style="background-color: #22d3ee; color: #111827; border-color: #111827;"
                        >
                            Add Question
                        </a>
                    </div>

                    @if ($quiz->questions->isEmpty())
                        <p class="text-sm text-gray-600">No questions added yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($quiz->questions as $question)
                                <div class="border border-gray-200 rounded-md p-4">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="text-sm text-gray-600">#{{ $question->order_index }} | {{ $questionTypes[$question->type] ?? ucfirst($question->type) }}</p>
                                            <p class="font-medium text-gray-900 mt-1">{{ $question->prompt }}</p>
                                            <p class="text-sm text-gray-600 mt-1">Points: {{ $question->points }}</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a
                                                href="{{ route('admin.quizzes.questions.edit', ['quiz' => $quiz, 'question' => $question]) }}"
                                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                            >
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.quizzes.questions.destroy', ['quiz' => $quiz, 'question' => $question]) }}" onsubmit="return confirm('Delete this question?');">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center rounded-md border border-red-300 bg-red-50 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-red-700 transition hover:bg-red-100"
                                                >
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
