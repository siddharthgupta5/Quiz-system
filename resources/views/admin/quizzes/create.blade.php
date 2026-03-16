<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Quiz</h2>
            <a
                href="{{ route('admin.quizzes.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
            >
                Back to Quizzes
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.quizzes.store') }}" class="space-y-6">
                        @csrf

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

                        <div class="pt-2">
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-md border px-4 py-2 text-xs font-semibold uppercase tracking-widest transition"
                                style="background-color: #f59e0b; color: #111827; border-color: #111827;"
                            >
                                Save Quiz
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
