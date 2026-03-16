<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_attempt_id',
        'question_id',
        'answer_text',
        'answer_number',
        'answer_boolean',
        'selected_option_ids',
        'is_correct',
        'scored_points',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'answer_boolean' => 'boolean',
            'selected_option_ids' => 'array',
            'answered_at' => 'datetime',
            'is_correct' => 'boolean',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
