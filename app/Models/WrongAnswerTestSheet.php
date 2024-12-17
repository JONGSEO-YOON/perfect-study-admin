<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrongAnswerTestSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_test_sheet_id',
        'test_sheet_id',
        'user_id',
        'retry_count',
        'is_linked_to_original',
        'wrong_answer_questions',
    ];

    protected $casts = [
        'wrong_answer_questions' => 'array',
        'is_linked_to_original' => 'boolean',
        'retry_count' => 'integer',
    ];

    /**
     * Get the original test sheet that was used to create this wrong answer test.
     */
    public function originalTestSheet()
    {
        return $this->belongsTo(TestSheet::class, 'original_test_sheet_id');
    }

    /**
     * Get the test sheet that contains the wrong answer questions.
     */
    public function testSheet()
    {
        return $this->belongsTo(TestSheet::class, 'test_sheet_id');
    }

    /**
     * Get the user (student) who takes this wrong answer test.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is a first retry test.
     */
    public function isFirstRetry(): bool
    {
        return $this->retry_count === 1;
    }

    /**
     * Check if this is a second retry test.
     */
    public function isSecondRetry(): bool
    {
        return $this->retry_count === 2;
    }

    /**
     * Get the original question ID for a given new question ID.
     */
    public function getOriginalQuestionId(int $newQuestionId): ?int
    {
        $mapping = collect($this->wrong_answer_questions)
            ->firstWhere('new_question_id', $newQuestionId);

        return $mapping ? $mapping['original_question_id'] : null;
    }

    /**
     * Get the new question ID for a given original question ID.
     */
    public function getNewQuestionId(int $originalQuestionId): ?int
    {
        $mapping = collect($this->wrong_answer_questions)
            ->firstWhere('original_question_id', $originalQuestionId);

        return $mapping ? $mapping['new_question_id'] : null;
    }

    /**
     * Get the mapped questions information.
     * Returns an array of [original_question => new_question] pairs
     */
    public function getMappedQuestions(): array
    {
        $originalTestSheet = $this->originalTestSheet;
        $newTestSheet = $this->testSheet;
        $mappedQuestions = [];

        foreach ($this->wrong_answer_questions as $mapping) {
            $originalQuestion = $originalTestSheet->questions[$mapping['original_question_seq']] ?? null;
            $newQuestion = $newTestSheet->questions[$mapping['new_question_seq']] ?? null;

            if ($originalQuestion && $newQuestion) {
                $mappedQuestions[] = [
                    'original_question' => $originalQuestion,
                    'new_question' => $newQuestion,
                    'original_seq' => $mapping['original_question_seq'],
                    'new_seq' => $mapping['new_question_seq'],
                ];
            }
        }

        return $mappedQuestions;
    }

    /**
     * Get the deadline for this wrong answer test.
     */
    public function getDeadline()
    {
        if ($this->is_linked_to_original) {
            return $this->originalTestSheet->end_date;
        }

        return $this->testSheet->end_date;
    }

    protected static function boot()
    {
        parent::boot();

        // 삭제 시 연결된 test_sheet도 함께 삭제
        static::deleting(function ($wrongAnswerTest) {
            $wrongAnswerTest->testSheet()->delete();
        });
    }
}
