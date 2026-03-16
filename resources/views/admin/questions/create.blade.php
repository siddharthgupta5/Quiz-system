<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Question to {{ $quiz->title }}</h2>
            <a
                href="{{ route('admin.quizzes.edit', $quiz) }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
            >
                Back to Quiz
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.quizzes.questions.store', $quiz) }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label for="prompt" class="block text-sm font-medium text-gray-700">Question Prompt</label>
                                <textarea id="prompt" name="prompt" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('prompt') }}</textarea>
                                @error('prompt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Question Type</label>
                                <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select type</option>
                                    @foreach ($questionTypes as $key => $label)
                                        <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="points" class="block text-sm font-medium text-gray-700">Points</label>
                                <input id="points" name="points" type="number" step="0.01" min="0" value="{{ old('points', 1) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('points') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="order_index" class="block text-sm font-medium text-gray-700">Order Index</label>
                                <input id="order_index" name="order_index" type="number" min="1" value="{{ old('order_index') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('order_index') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-md p-4 space-y-3">
                            <h3 class="font-semibold text-gray-800">Choice Question Settings</h3>
                            <p class="text-xs text-gray-600">For single/multiple choice only. Add one option per line and provide correct option numbers using 1-based indexes, e.g., 1 or 1,3.</p>
                            <div>
                                <label for="options_text" class="block text-sm font-medium text-gray-700">Options (one per line)</label>
                                <textarea id="options_text" name="options_text" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('options_text', $formDefaults['options_text']) }}</textarea>
                                @error('options_text') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="correct_option_indexes" class="block text-sm font-medium text-gray-700">Correct Option Indexes</label>
                                <input id="correct_option_indexes" name="correct_option_indexes" type="text" value="{{ old('correct_option_indexes', $formDefaults['correct_option_indexes']) }}" placeholder="e.g. 1 or 1,3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('correct_option_indexes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-md p-4 space-y-3">
                            <h3 class="font-semibold text-gray-800">Text Input Settings</h3>
                            <p class="text-xs text-gray-600">For text input only. Add accepted aliases separated by comma or new line.</p>
                            <div>
                                <label for="accepted_aliases" class="block text-sm font-medium text-gray-700">Accepted Answer Aliases</label>
                                <textarea id="accepted_aliases" name="accepted_aliases" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('accepted_aliases', $formDefaults['accepted_aliases']) }}</textarea>
                                @error('accepted_aliases') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-md p-4 space-y-3">
                            <h3 class="font-semibold text-gray-800">Numerical Settings</h3>
                            <p class="text-xs text-gray-600">For numerical questions only.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="numerical_correct_answer" class="block text-sm font-medium text-gray-700">Correct Numerical Answer</label>
                                    <input id="numerical_correct_answer" name="numerical_correct_answer" type="number" step="any" value="{{ old('numerical_correct_answer') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('numerical_correct_answer') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="numerical_tolerance" class="block text-sm font-medium text-gray-700">Tolerance</label>
                                    <input id="numerical_tolerance" name="numerical_tolerance" type="number" step="any" min="0" value="{{ old('numerical_tolerance', 0.01) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('numerical_tolerance') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-md p-4 space-y-3">
                            <h3 class="font-semibold text-gray-800">Binary Settings</h3>
                            <p class="text-xs text-gray-600">For binary (yes/no or true/false) questions only.</p>
                            <div>
                                <label for="binary_correct_answer" class="block text-sm font-medium text-gray-700">Correct Binary Answer</label>
                                <select id="binary_correct_answer" name="binary_correct_answer" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select correct answer</option>
                                    <option value="1" @selected(old('binary_correct_answer', $formDefaults['binary_correct_answer']) === '1')>Yes / True</option>
                                    <option value="0" @selected(old('binary_correct_answer', $formDefaults['binary_correct_answer']) === '0')>No / False</option>
                                </select>
                                @error('binary_correct_answer') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-md border px-4 py-2 text-xs font-semibold uppercase tracking-widest transition"
                                style="background-color: #f59e0b; color: #111827; border-color: #111827;"
                            >
                                Save Question
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
