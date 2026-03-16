<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin - Quiz Management</h2>
            <a
                href="{{ route('admin.quizzes.create') }}"
                class="inline-flex items-center rounded-md border px-4 py-2 text-xs font-semibold uppercase tracking-widest transition"
                style="background-color: #22d3ee; color: #111827; border-color: #111827;"
            >
                Create Quiz
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900">
                    <form method="GET" action="{{ route('admin.quizzes.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="w-full sm:max-w-md">
                            <label for="q" class="block text-xs font-semibold uppercase tracking-wider text-gray-600">Search Quizzes</label>
                            <input
                                id="q"
                                type="text"
                                name="q"
                                value="{{ $search }}"
                                placeholder="Search by title or description"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-md border px-4 py-2 text-xs font-semibold uppercase tracking-widest transition"
                                style="background-color: #22d3ee; color: #111827; border-color: #111827;"
                            >
                                Search
                            </button>
                            @if ($search !== '')
                                <a
                                    href="{{ route('admin.quizzes.index') }}"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                >
                                    Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($quizzes->isEmpty())
                        <p>
                            @if ($search !== '')
                                No quizzes found for "{{ $search }}".
                            @else
                                No quizzes found. Create your first quiz.
                            @endif
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Title</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Questions</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Attempts</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($quizzes as $quiz)
                                        <tr>
                                            <td class="px-3 py-3 text-sm text-gray-800">{{ $quiz->title }}</td>
                                            <td class="px-3 py-3 text-sm text-gray-700">{{ $quiz->questions_count }}</td>
                                            <td class="px-3 py-3 text-sm text-gray-700">{{ $quiz->attempts_count }}</td>
                                            <td class="px-3 py-3 text-sm">
                                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $quiz->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                                    {{ $quiz->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-3 text-right">
                                                <a
                                                    href="{{ route('admin.quizzes.edit', $quiz) }}"
                                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                                >
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('admin.quizzes.destroy', $quiz) }}" class="inline-block ml-2" onsubmit="return confirm('Delete this quiz and all related data?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center rounded-md border border-red-300 bg-red-50 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-red-700 transition hover:bg-red-100"
                                                    >
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $quizzes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
