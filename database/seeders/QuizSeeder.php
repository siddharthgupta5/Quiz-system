<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quiz = Quiz::updateOrCreate([
            'title' => 'General Knowledge Challenge',
        ], [
            'description' => 'A mixed quiz with single, multiple, text, numerical and binary questions.',
            'time_limit_seconds' => 900,
            'pass_percentage' => 60,
            'is_active' => true,
        ]);

        $quiz->questions()->delete();

        $q1 = $quiz->questions()->create([
            'type' => Question::TYPE_SINGLE_CHOICE,
            'prompt' => 'What is the capital city of France?',
            'points' => 1,
            'order_index' => 1,
        ]);
        $q1->options()->createMany([
            ['label' => 'Paris', 'is_correct' => true, 'order_index' => 1],
            ['label' => 'Berlin', 'is_correct' => false, 'order_index' => 2],
            ['label' => 'Madrid', 'is_correct' => false, 'order_index' => 3],
            ['label' => 'Rome', 'is_correct' => false, 'order_index' => 4],
        ]);

        $q2 = $quiz->questions()->create([
            'type' => Question::TYPE_MULTIPLE_CHOICE,
            'prompt' => 'Select all prime numbers below.',
            'points' => 2,
            'order_index' => 2,
        ]);
        $q2->options()->createMany([
            ['label' => '2', 'is_correct' => true, 'order_index' => 1],
            ['label' => '3', 'is_correct' => true, 'order_index' => 2],
            ['label' => '4', 'is_correct' => false, 'order_index' => 3],
            ['label' => '5', 'is_correct' => true, 'order_index' => 4],
        ]);

        $quiz->questions()->create([
            'type' => Question::TYPE_TEXT_INPUT,
            'prompt' => 'Name the planet known as the Red Planet.',
            'points' => 1,
            'order_index' => 3,
            'accepted_text_aliases' => ['mars', 'the red planet'],
        ]);

        $quiz->questions()->create([
            'type' => Question::TYPE_NUMERICAL,
            'prompt' => 'What is pi rounded to two decimal places?',
            'points' => 1,
            'order_index' => 4,
            'numerical_correct_answer' => 3.14,
            'numerical_tolerance' => 0.01,
        ]);

        $quiz->questions()->create([
            'type' => Question::TYPE_BINARY,
            'prompt' => 'PHP is primarily a server-side scripting language.',
            'points' => 1,
            'order_index' => 5,
            'binary_correct_answer' => true,
        ]);

        $q6 = $quiz->questions()->create([
            'type' => Question::TYPE_SINGLE_CHOICE,
            'prompt' => 'Which data structure works on a FIFO basis?',
            'points' => 1,
            'order_index' => 6,
        ]);
        $q6->options()->createMany([
            ['label' => 'Stack', 'is_correct' => false, 'order_index' => 1],
            ['label' => 'Queue', 'is_correct' => true, 'order_index' => 2],
            ['label' => 'Tree', 'is_correct' => false, 'order_index' => 3],
            ['label' => 'Graph', 'is_correct' => false, 'order_index' => 4],
        ]);

        $q7 = $quiz->questions()->create([
            'type' => Question::TYPE_MULTIPLE_CHOICE,
            'prompt' => 'Which of the following are Laravel features?',
            'points' => 2,
            'order_index' => 7,
        ]);
        $q7->options()->createMany([
            ['label' => 'Eloquent ORM', 'is_correct' => true, 'order_index' => 1],
            ['label' => 'Blade templating', 'is_correct' => true, 'order_index' => 2],
            ['label' => 'Java bytecode compilation', 'is_correct' => false, 'order_index' => 3],
            ['label' => 'Queues and jobs', 'is_correct' => true, 'order_index' => 4],
        ]);

        $quiz->questions()->create([
            'type' => Question::TYPE_TEXT_INPUT,
            'prompt' => 'Type one alias for hypertext transfer protocol.',
            'points' => 1,
            'order_index' => 8,
            'accepted_text_aliases' => ['http', 'hypertext transfer protocol'],
        ]);
    }
}
