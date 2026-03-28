<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentHistoryResource\Pages;
use App\Models\Student;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use App\Models\Classroom;

class StudentHistoryResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationGroup = '교실 관리';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = '학생 이력관리';

    protected static ?string $slug = 'student-histories';

    protected static ?string $pluralModelLabel = '학생 이력관리';

    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function ($query) {
                if (!auth()->user()->isRoleAbove('manager', true)) {
                    $query->whereHas('classrooms', function ($q) {
                        $q->where('classrooms.teacher_id', auth()->user()->userable->id)
                            ->orWhere('classrooms.sub_teacher_id', auth()->user()->userable->id);
                    });
                }
                return $query;
            })
            ->columns([
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('user.name')
                    ->label('이름')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('school.name')
                    ->label('학교')
                    ->searchable(),
                TextColumn::make('gradeSystem.display_name')
                    ->label('학년'),
                TextColumn::make('status')
                    ->label('현재 상태')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'withdrawn' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => '재원',
                        'pending' => '승인예정',
                        'withdrawn' => '퇴원',
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->label('등록일')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('상태')
                    ->options([
                        'active' => '재원',
                        'pending' => '승인예정',
                        'withdrawn' => '퇴원',
                    ]),
                SelectFilter::make('classroom_id')
                    ->label('반')
                    ->options(function () {
                        return Classroom::query()
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->query(function (Builder $query, $data) {
                        $classroomId = $data["value"] ?? null;
                        $query->when($classroomId, function ($query, $classroomId) {
                            $query->whereHas('classrooms', function ($q) use ($classroomId) {
                                $q->where('classrooms.id', $classroomId);
                            });
                        });
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\Action::make('view-history')
                    ->label('이력 보기')
                    ->icon('heroicon-m-clock')
                    ->modalHeading(fn($record) => $record->user->name . ' 학생 이력')
                    ->modalSubmitAction(false)
                    ->modalWidth('3xl')
                    ->modalContent(function ($record) {
                        $timeline = self::buildTimeline($record);
                        return view('filament.components.modals.student-history', [
                            'timeline' => $timeline,
                            'student' => $record,
                        ]);
                    }),
            ])
            ->hiddenFilterIndicators(true)
            ->emptyStateHeading('학생이 없습니다.');
    }

    public static function buildTimeline(Student $student): array
    {
        $events = [];

        // 1. 등록 이벤트
        $events[] = [
            'date' => $student->created_at,
            'type' => 'register',
            'icon' => 'user-plus',
            'color' => 'blue',
            'title' => '학원 등록',
            'description' => $student->created_at->format('Y-m-d') . ' 등록',
        ];

        // 2. 최초 수강일
        if ($student->user?->initially_attended_at) {
            $events[] = [
                'date' => $student->user->initially_attended_at,
                'type' => 'first_attend',
                'icon' => 'academic-cap',
                'color' => 'green',
                'title' => '최초 수강',
                'description' => $student->user->initially_attended_at->format('Y-m-d') . ' 수강 시작',
            ];
        }

        // 3. 결제 이력
        $payments = $student->payments()
            ->whereIn('payment_status', ['paid', 'cancelled'])
            ->orderBy('created_at')
            ->get();

        foreach ($payments as $payment) {
            if ($payment->payment_status === 'paid') {
                $events[] = [
                    'date' => $payment->paid_at ?? $payment->created_at,
                    'type' => 'payment',
                    'icon' => 'credit-card',
                    'color' => 'emerald',
                    'title' => '결제 완료',
                    'description' => ($payment->billing_name ?: '수업료') . ' - ' . number_format($payment->amount) . '원',
                ];
            } elseif ($payment->payment_status === 'cancelled') {
                $events[] = [
                    'date' => $payment->cancelled_at ?? $payment->created_at,
                    'type' => 'payment_cancel',
                    'icon' => 'x-circle',
                    'color' => 'orange',
                    'title' => '결제 취소',
                    'description' => ($payment->billing_name ?: '수업료') . ' - ' . number_format($payment->amount) . '원'
                        . ($payment->cancel_reason ? ' (' . $payment->cancel_reason . ')' : ''),
                ];
            }
        }

        // 4. 상태 변경 이력 (퇴원/재등록)
        $statusHistories = $student->statusHistories()
            ->with('changedBy')
            ->orderBy('created_at')
            ->get();

        foreach ($statusHistories as $history) {
            if ($history->to_status === 'withdrawn') {
                $events[] = [
                    'date' => $history->created_at,
                    'type' => 'withdrawn',
                    'icon' => 'user-minus',
                    'color' => 'red',
                    'title' => '퇴원',
                    'description' => ($history->reason ?: '사유 없음')
                        . ($history->changedBy ? ' (처리: ' . $history->changedBy->name . ')' : ''),
                ];
            } elseif ($history->to_status === 'active' && $history->from_status === 'withdrawn') {
                $events[] = [
                    'date' => $history->created_at,
                    'type' => 're_register',
                    'icon' => 'arrow-path',
                    'color' => 'indigo',
                    'title' => '재등록',
                    'description' => ($history->reason ?: '')
                        . ($history->changedBy ? ' (처리: ' . $history->changedBy->name . ')' : ''),
                ];
            } elseif ($history->to_status === 'active' && $history->from_status === 'pending') {
                $events[] = [
                    'date' => $history->created_at,
                    'type' => 'approved',
                    'icon' => 'check-circle',
                    'color' => 'green',
                    'title' => '승인 완료',
                    'description' => $history->changedBy ? '처리: ' . $history->changedBy->name : '',
                ];
            }
        }

        // 날짜순 정렬 (최신이 위로)
        usort($events, fn($a, $b) => $b['date']->timestamp - $a['date']->timestamp);

        return $events;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentHistories::route('/'),
        ];
    }
}
