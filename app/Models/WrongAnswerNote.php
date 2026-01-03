<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrongAnswerNote extends Model
{
  use HasFactory;

  protected $casts = [
    'question' => 'array',
  ];

  public function student()
  {
    return $this->belongsTo(Student::class);
  }

  /**
   * 주어진 기간 동안의 학생의 고유한 오답 문제들을 조회합니다.
   *
   * @param int $studentId 학생 ID
   * @param string $from 시작일자 (Y-m-d)
   * @param string $to 종료일자 (Y-m-d)
   * @return array 고유한 문제 배열
   */
  public static function getQuestions(int $studentId, string $from, string $to, bool $isDontKnowOnly = false): array
  {
    $wrongAnswers = self::where('student_id', $studentId)
      ->whereBetween('created_at', [
        Carbon::parse($from)->startOfDay(),
        Carbon::parse($to)->endOfDay()
      ])
      ->when($isDontKnowOnly == true, function ($query) {
        return $query->where('dont_know', true);
      })
      ->get();

    return $wrongAnswers->map(function ($wrongAnswer) {
      $question = $wrongAnswer->question;
      return $question;
    })
      ->unique('id')
      ->values()
      ->toArray();
  }
}
