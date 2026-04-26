<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademyResource\Pages;
use App\Models\Academy;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AcademyResource extends Resource
{
    protected static ?string $model = Academy::class;

    protected static ?string $navigationLabel = '학원 관리';

    protected static ?string $navigationGroup = '설정';

    protected static ?int $navigationSort = 10;

    // 설정 그룹에 그룹 아이콘이 있으므로 개별 아이콘 제거
    // protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'root_admin';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('기본 정보')
                ->schema([
                    TextInput::make('name')
                        ->label('학원 이름')
                        ->required()
                        ->afterStateUpdated(fn ($set, $state) => $set('slug', Str::slug($state)))
                        ->live(onBlur: true),
                    TextInput::make('slug')
                        ->label('슬러그')
                        ->required()
                        ->unique(ignoreRecord: true),
                    TextInput::make('phone')
                        ->label('전화번호'),
                    TextInput::make('address')
                        ->label('주소')
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label('활성화')
                        ->default(true),
                    Forms\Components\Select::make('student_limit')
                        ->label('승인 인원 제한')
                        ->options([
                            null => '무제한',
                            50 => '50명',
                            100 => '100명',
                            150 => '150명',
                            200 => '200명',
                            250 => '250명',
                            300 => '300명',
                            350 => '350명',
                            400 => '400명',
                            450 => '450명',
                            500 => '500명',
                        ])
                        ->helperText('재원생(승인된 학생) 수 제한. 초과 시 승인이 제한됩니다.'),
                ])->columns(2),

            Section::make('사업자 정보')
                ->description('부모님/학생 앱 하단 푸터에 표시됩니다.')
                ->schema([
                    TextInput::make('representative_name')
                        ->label('대표자명')
                        ->placeholder('예: 홍길동'),
                    TextInput::make('business_number')
                        ->label('사업자등록번호')
                        ->placeholder('예: 598-06-02832'),
                ])->columns(2),

            Section::make('브랜딩')
                ->description('로고, 색상, 로그인 페이지 커스터마이징')
                ->schema([
                    FileUpload::make('logo_path')
                        ->label('학원 로고')
                        ->image()
                        ->directory('academy-branding')
                        ->imageResizeMode('contain')
                        ->imageCropAspectRatio('16:9')
                        ->imageResizeTargetWidth('400')
                        ->imageResizeTargetHeight('200')
                        ->helperText('로그인 페이지, 사이드바 상단에 표시됩니다'),
                    FileUpload::make('favicon_path')
                        ->label('파비콘')
                        ->image()
                        ->directory('academy-branding')
                        ->imageResizeTargetWidth('64')
                        ->imageResizeTargetHeight('64')
                        ->helperText('브라우저 탭에 표시되는 작은 아이콘'),
                    ColorPicker::make('primary_color')
                        ->label('주요 색상')
                        ->helperText('버튼, 링크 등에 사용되는 메인 색상'),
                    FileUpload::make('login_background_path')
                        ->label('로그인 배경 이미지')
                        ->image()
                        ->directory('academy-branding')
                        ->helperText('로그인 페이지 배경으로 사용됩니다'),
                    Textarea::make('login_welcome_message')
                        ->label('로그인 환영 메시지')
                        ->rows(2)
                        ->placeholder('예: 퍼펙트 스터디에 오신 것을 환영합니다!')
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('메뉴 공개 설정')
                ->description('학원별로 자료실/강의실/결제 메뉴의 공개 여부를 설정합니다.')
                ->schema([
                    Toggle::make('settings.resources_visible')
                        ->label('자료실 공개')
                        ->default(true)
                        ->helperText('비활성화 시 해당 학원 사용자에게 자료실 메뉴가 표시되지 않습니다.'),
                    Toggle::make('settings.lectures_visible')
                        ->label('강의실 공개')
                        ->default(true)
                        ->helperText('비활성화 시 해당 학원 사용자에게 강의실 메뉴가 표시되지 않습니다.'),
                    Toggle::make('settings.payments_visible')
                        ->label('결제 공개')
                        ->default(true)
                        ->helperText('비활성화 시 해당 학원 admin에게 결제 메뉴가 표시되지 않습니다.'),
                ])->columns(3),

            Section::make('토스 페이먼츠 설정')
                ->description('학원별 결제 가맹점 키를 설정합니다.')
                ->schema([
                    TextInput::make('toss_client_key')
                        ->label('Client Key')
                        ->password()
                        ->revealable(),
                    TextInput::make('toss_secret_key')
                        ->label('Secret Key')
                        ->password()
                        ->revealable(),
                    TextInput::make('toss_customer_key')
                        ->label('Customer Key')
                        ->password()
                        ->revealable(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                ImageColumn::make('logo_path')->label('로고')->circular(),
                TextColumn::make('name')->label('학원 이름')->searchable(),
                TextColumn::make('slug')->label('슬러그'),
                IconColumn::make('is_active')->label('활성')->boolean(),
                TextColumn::make('users_count')
                    ->label('사용자 수')
                    ->counts('users'),
                TextColumn::make('students_count')
                    ->label('전체 학생')
                    ->counts('students'),
                TextColumn::make('enrolled_students_count')
                    ->label('재원생(승인)')
                    ->getStateUsing(fn ($record) => $record->students()->whereIn('status', ['enrolled', 'active'])->count())
                    ->color('success')
                    ->badge(),
                TextColumn::make('pending_students_count')
                    ->label('승인 대기')
                    ->getStateUsing(fn ($record) => $record->students()->where('status', 'pending')->count())
                    ->color('warning')
                    ->badge(),
                TextColumn::make('withdrawn_students_count')
                    ->label('퇴원생')
                    ->getStateUsing(fn ($record) => $record->students()->where('status', 'withdrawn')->count())
                    ->color('danger')
                    ->badge(),
                TextColumn::make('student_limit')
                    ->label('인원 제한')
                    ->formatStateUsing(fn ($state) => $state ? $state . '명' : '무제한')
                    ->color(fn ($record) => $record->student_limit && $record->students()->whereIn('status', ['enrolled', 'active'])->count() >= $record->student_limit ? 'danger' : 'gray'),
                TextColumn::make('created_at')
                    ->label('생성일')
                    ->date('Y-m-d'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademies::route('/'),
            'create' => Pages\CreateAcademy::route('/create'),
            'edit' => Pages\EditAcademy::route('/{record}/edit'),
        ];
    }
}
