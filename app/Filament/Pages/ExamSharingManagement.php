<?php

namespace App\Filament\Pages;

use App\Models\Academy;
use App\Models\ExamSharingRule;
use App\Models\QuestionCategory;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExamSharingManagement extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = null;

    protected static ?string $navigationLabel = '기출 공유 권한';

    protected static ?string $navigationGroup = '설정';

    protected static ?int $navigationSort = 11;

    protected static ?string $title = '기출 공유 권한';

    protected static ?string $slug = 'exam-sharing-rules';

    protected static string $view = 'filament.pages.exam-sharing-management';

    public static function canAccess(): bool
    {
        $role = auth()->user()->role ?? null;
        return $role === 'root_admin';
    }

    protected static function getSubjectOptions(): array
    {
        $rootIds = QuestionCategory::where('depth', 0)
            ->whereRaw("REPLACE(REPLACE(name, '<p>', ''), '</p>', '') IN ('고', '고3')")
            ->pluck('id');

        return QuestionCategory::where('depth', 1)
            ->whereIn('id', function ($q) use ($rootIds) {
                $q->select('descendant_id')
                    ->from('question_category_closure')
                    ->where('depth', 1)
                    ->whereIn('ancestor_id', $rootIds);
            })
            ->orderBy('id')
            ->pluck('name')
            ->map(fn($name) => strip_tags(trim($name)))
            ->filter(fn($name) => !in_array($name, ['교과외', '연산문제']))
            ->unique()
            ->map(fn($name) => trim(str_replace('(2025개정)', '', $name)))
            ->unique()
            ->mapWithKeys(fn($name) => [$name => $name])
            ->toArray();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ExamSharingRule::query())
            ->columns([
                TextColumn::make('academy.name')
                    ->label('대상 학원')
                    ->searchable(),
                TextColumn::make('source_type')
                    ->label('출처')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'mock_exam' => '모의고사',
                        'school_exam' => '학교 기출',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'mock_exam' => 'info',
                        'school_exam' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('school.name')
                    ->label('학교')
                    ->placeholder('전체'),
                TextColumn::make('exam_year')
                    ->label('년도')
                    ->placeholder('전체'),
                TextColumn::make('exam_month')
                    ->label('월')
                    ->formatStateUsing(fn($state) => $state ? $state . '월' : '')
                    ->placeholder('전체'),
                TextColumn::make('exam_semester')
                    ->label('학기')
                    ->formatStateUsing(fn($state) => match ($state) {
                        1 => '1학기',
                        2 => '2학기',
                        default => '',
                    })
                    ->placeholder('전체'),
                TextColumn::make('exam_type')
                    ->label('시험유형')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'midterm' => '중간고사',
                        'final' => '기말고사',
                        default => '',
                    })
                    ->placeholder('전체'),
                TextColumn::make('exam_subject')
                    ->label('과목')
                    ->placeholder('전체'),
                IconColumn::make('is_allowed')
                    ->label('허용')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label('등록일')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('academy_id')
                    ->label('학원')
                    ->options(Academy::pluck('name', 'id')),
                SelectFilter::make('source_type')
                    ->label('출처')
                    ->options([
                        'mock_exam' => '모의고사 기출',
                        'school_exam' => '학교 기출',
                    ]),
                SelectFilter::make('is_allowed')
                    ->label('허용 상태')
                    ->options([1 => '허용', 0 => '차단']),
            ])
            ->headerActions([
                Action::make('create_rule')
                    ->label('공유 규칙 추가')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('기출 공유 규칙 추가')
                    ->modalWidth('lg')
                    ->form($this->getRuleFormSchema())
                    ->action(function (array $data): void {
                        $this->saveRules($data);
                    }),
            ])
            ->actions([
                Action::make('edit_rule')
                    ->label('수정')
                    ->icon('heroicon-o-pencil')
                    ->modalHeading('기출 공유 규칙 수정')
                    ->modalWidth('lg')
                    ->fillForm(function (ExamSharingRule $record): array {
                        return [
                            'academy_id' => $record->academy_id,
                            'source_type' => $record->source_type,
                            'school_id' => $record->school_id,
                            'year_mode' => $record->exam_year ? 'specific' : 'all',
                            'exam_years' => $record->exam_year ? [$record->exam_year] : [],
                            'month_mode' => $record->exam_month ? 'specific' : 'all',
                            'exam_months' => $record->exam_month ? [$record->exam_month] : [],
                            'semester_mode' => $record->exam_semester ? 'specific' : 'all',
                            'exam_semester' => $record->exam_semester ? (string) $record->exam_semester : 'all',
                            'exam_type' => $record->exam_type ?? 'all',
                            'subject_mode' => $record->exam_subject ? 'specific' : 'all',
                            'exam_subject' => $record->exam_subject,
                            'is_allowed' => $record->is_allowed,
                        ];
                    })
                    ->form($this->getRuleFormSchema())
                    ->action(function (ExamSharingRule $record, array $data): void {
                        $record->update($this->buildRuleData($data));

                        Notification::make()
                            ->title('공유 규칙이 수정되었습니다.')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->label('삭제'),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    protected function getRuleFormSchema(): array
    {
        $years = array_combine(range(date('Y'), 2010, -1), range(date('Y'), 2010, -1));
        $subjectOptions = self::getSubjectOptions();

        return [
            Select::make('academy_id')
                ->label('대상 학원')
                ->options(Academy::pluck('name', 'id'))
                ->required()
                ->searchable()
                ->helperText('이 학원에 기출 문제 은행 접근 권한을 부여합니다.'),

            Select::make('source_type')
                ->label('문제 출처')
                ->options([
                    'mock_exam' => '모의고사 기출',
                    'school_exam' => '학교 기출',
                ])
                ->required()
                ->live(),

            // 학교 (학교 기출)
            Select::make('school_id')
                ->label('학교')
                ->searchable()
                ->getSearchResultsUsing(fn(string $search): array =>
                    \App\Models\School::where('name', 'like', "%{$search}%")
                        ->limit(50)
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->getOptionLabelUsing(fn($value): ?string =>
                    \App\Models\School::find($value)?->name
                )
                ->helperText('비워두면 모든 학교 기출에 적용')
                ->visible(fn(Get $get) => $get('source_type') === 'school_exam'),

            // 년도
            Radio::make('year_mode')
                ->label('년도')
                ->options(['all' => '전체', 'specific' => '특정 년도 선택'])
                ->default('all')
                ->live()
                ->inline(),
            CheckboxList::make('exam_years')
                ->label('년도 선택')
                ->options($years)
                ->columns(5)
                ->visible(fn(Get $get) => ($get('year_mode') ?? 'all') === 'specific'),

            // 월 (모의고사)
            Radio::make('month_mode')
                ->label('월')
                ->options(['all' => '전체', 'specific' => '특정 월 선택'])
                ->default('all')
                ->live()
                ->inline()
                ->visible(fn(Get $get) => $get('source_type') === 'mock_exam'),
            CheckboxList::make('exam_months')
                ->label('월 선택')
                ->options([3 => '3월', 4 => '4월', 5 => '5월', 6 => '6월', 7 => '7월', 9 => '9월', 10 => '10월', 11 => '11월'])
                ->columns(4)
                ->visible(fn(Get $get) => $get('source_type') === 'mock_exam' && ($get('month_mode') ?? 'all') === 'specific'),

            // 학기 (학교 기출)
            Select::make('exam_semester')
                ->label('학기')
                ->options(['all' => '전체', '1' => '1학기', '2' => '2학기'])
                ->default('all')
                ->visible(fn(Get $get) => $get('source_type') === 'school_exam'),

            // 시험유형 (학교 기출)
            Select::make('exam_type')
                ->label('시험 유형')
                ->options(['all' => '전체', 'midterm' => '중간고사', 'final' => '기말고사'])
                ->default('all')
                ->visible(fn(Get $get) => $get('source_type') === 'school_exam'),

            // 과목
            Radio::make('subject_mode')
                ->label('과목')
                ->options(['all' => '전체', 'specific' => '특정 과목 선택'])
                ->default('all')
                ->live()
                ->inline(),
            Select::make('exam_subject')
                ->label('과목 선택')
                ->options($subjectOptions)
                ->searchable()
                ->visible(fn(Get $get) => ($get('subject_mode') ?? 'all') === 'specific'),

            Toggle::make('is_allowed')
                ->label('접근 허용')
                ->helperText('ON: 해당 기출 문제 은행에 접근 허용')
                ->default(true),
        ];
    }

    protected function buildRuleData(array $data): array
    {
        $sourceType = $data['source_type'];

        return [
            'academy_id' => $data['academy_id'],
            'source_type' => $sourceType,
            'is_allowed' => $data['is_allowed'] ?? true,
            'school_id' => ($sourceType === 'school_exam') ? ($data['school_id'] ?? null) : null,
            'exam_year' => ($data['year_mode'] ?? 'all') === 'specific' ? ($data['exam_years'][0] ?? null) : null,
            'exam_month' => ($sourceType === 'mock_exam' && ($data['month_mode'] ?? 'all') === 'specific')
                ? ($data['exam_months'][0] ?? null) : null,
            'exam_semester' => ($sourceType === 'school_exam' && ($data['exam_semester'] ?? 'all') !== 'all')
                ? (int) $data['exam_semester'] : null,
            'exam_type' => ($sourceType === 'school_exam' && ($data['exam_type'] ?? 'all') !== 'all')
                ? $data['exam_type'] : null,
            'exam_subject' => ($data['subject_mode'] ?? 'all') === 'specific'
                ? $data['exam_subject'] : null,
        ];
    }

    protected function saveRules(array $data): void
    {
        $sourceType = $data['source_type'];
        $isAllowed = $data['is_allowed'] ?? true;
        $yearMode = $data['year_mode'] ?? 'all';
        $monthMode = $data['month_mode'] ?? 'all';
        $schoolId = ($sourceType === 'school_exam') ? ($data['school_id'] ?? null) : null;

        $years = ($yearMode === 'specific' && !empty($data['exam_years']))
            ? $data['exam_years'] : [null];

        $months = ($sourceType === 'mock_exam' && $monthMode === 'specific' && !empty($data['exam_months']))
            ? $data['exam_months'] : [null];

        $semester = ($sourceType === 'school_exam' && ($data['exam_semester'] ?? 'all') !== 'all')
            ? (int) $data['exam_semester'] : null;

        $examType = ($sourceType === 'school_exam' && ($data['exam_type'] ?? 'all') !== 'all')
            ? $data['exam_type'] : null;

        $subject = ($data['subject_mode'] ?? 'all') === 'specific'
            ? $data['exam_subject'] : null;

        $created = 0;
        $skipped = 0;

        // 년도 x 월 조합으로 규칙 생성
        foreach ($years as $year) {
            foreach ($months as $month) {
                $exists = ExamSharingRule::where('academy_id', $data['academy_id'])
                    ->where('source_type', $sourceType)
                    ->where('school_id', $schoolId)
                    ->where('exam_year', $year)
                    ->where('exam_month', $month)
                    ->where('exam_semester', $semester)
                    ->where('exam_type', $examType)
                    ->where('exam_subject', $subject)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                ExamSharingRule::create([
                    'academy_id' => $data['academy_id'],
                    'source_type' => $sourceType,
                    'is_allowed' => $isAllowed,
                    'school_id' => $schoolId,
                    'exam_year' => $year,
                    'exam_month' => $month,
                    'exam_semester' => $semester,
                    'exam_type' => $examType,
                    'exam_subject' => $subject,
                ]);
                $created++;
            }
        }

        $message = "{$created}개 규칙이 추가되었습니다.";
        if ($skipped > 0) {
            $message .= " (중복 {$skipped}개 건너뜀)";
        }

        Notification::make()
            ->title($message)
            ->success()
            ->send();
    }
}
