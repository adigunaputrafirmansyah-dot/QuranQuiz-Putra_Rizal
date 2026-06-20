<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'surah_number',
        'surah_name',
        'ayat_number',
        'ayat_text_arab',
        'ayat_text_translation',
        'question_text',
        'question_type',
        'points',
        'order',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function correctOption(): ?Option
    {
        return $this->options->firstWhere('is_correct', true);
    }
}
