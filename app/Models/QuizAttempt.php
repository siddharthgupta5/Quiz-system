<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_TIMED_OUT = 'timed_out';

    protected $fillable = [
        'quiz_id',
        'user_id',
        'status',
        'started_at',
        'ends_at',
        'submitted_at',
        'score',
        'total_points',
        'correct_count',
        'incorrect_count',
        'unanswered_count',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }

    public function isExpired(): bool
    {
        return now()->greaterThanOrEqualTo($this->ends_at);
    }

    public function remainingSeconds(): int
    {
        return max(0, now()->diffInSeconds($this->ends_at, false));
    }
}
