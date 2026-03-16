<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    public const TYPE_SINGLE_CHOICE = 'single_choice';
    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    public const TYPE_TEXT_INPUT = 'text_input';
    public const TYPE_NUMERICAL = 'numerical';
    public const TYPE_BINARY = 'binary';

    protected $fillable = [
        'quiz_id',
        'type',
        'prompt',
        'points',
        'order_index',
        'accepted_text_aliases',
        'numerical_correct_answer',
        'numerical_tolerance',
        'binary_correct_answer',
    ];

    protected function casts(): array
    {
        return [
            'accepted_text_aliases' => 'array',
            'binary_correct_answer' => 'boolean',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order_index');
    }
}
