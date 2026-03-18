<?php

namespace App\Filament\Resources\AcademyResource\RelationManagers;

use App\Models\Teacher;
use App\Models\Counselor;
use App\Models\User;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = '소속 사용자';

    protected static ?string $modelLabel = '사용자';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                // 강사, 상담실만 표시 (학생은 별도 관리)
                return $query->whereIn('userable_type', [
                    'App\\Models\\Teacher',
                    'App\\Models\\Counselor',
                ]);
            })
            ->columns([
                TextColumn::make('name')->label('이름')->searchable(),
                TextColumn::make('username')->label('계정'),
                TextColumn::make('userable_type')
                    ->label('유형')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'App\\Models\\Teacher' => '강사',
                        'App\\Models\\Counselor' => '상담실',
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('role')
                    ->label('권한')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'root_admin' => '최고관리자',
                        'admin' => '관리자',
                        'manager' => '중간관리자',
                        'general' => '일반강사',
                        'counselor' => '상담실',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'root_admin' => 'danger',
                        'admin' => 'warning',
                        'manager' => 'info',
                        'general' => 'gray',
                        'counselor' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('phone')->label('전화번호'),
                TextColumn::make('created_at')->label('생성일')->date('Y-m-d'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('createTeacher')
                    ->label('강사 추가')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->form([
                        TextInput::make('name')
                            ->label('이름')
                            ->required(),
                        TextInput::make('username')
                            ->label('계정 (로그인 ID)')
                            ->required()
                            ->unique('users', 'username'),
                        TextInput::make('phone')
                            ->label('전화번호'),
                        ToggleButtons::make('role')
                            ->label('권한')
                            ->required()
                            ->inline()
                            ->grouped()
                            ->options([
                                'general' => '일반 강사',
                                'manager' => '중간 관리자',
                                'admin' => '관리자',
                            ])
                            ->default('general'),
                        TextInput::make('password')
                            ->label('비밀번호')
                            ->password()
                            ->required()
                            ->confirmed(),
                        TextInput::make('password_confirmation')
                            ->label('비밀번호 확인')
                            ->password()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $academyId = $this->getOwnerRecord()->id;

                        $teacher = Teacher::create([
                            'role' => $data['role'],
                            'academy_id' => $academyId,
                        ]);

                        User::create([
                            'name' => $data['name'],
                            'username' => $data['username'],
                            'phone' => $data['phone'] ?? null,
                            'password' => Hash::make($data['password']),
                            'userable_type' => 'App\\Models\\Teacher',
                            'userable_id' => $teacher->id,
                            'academy_id' => $academyId,
                        ]);

                        Notification::make()->title('강사 계정이 생성되었습니다.')->success()->send();
                    }),
                Tables\Actions\Action::make('createCounselor')
                    ->label('상담실 추가')
                    ->icon('heroicon-m-plus')
                    ->color('success')
                    ->form([
                        TextInput::make('name')
                            ->label('이름')
                            ->required(),
                        TextInput::make('username')
                            ->label('계정 (로그인 ID)')
                            ->required()
                            ->unique('users', 'username'),
                        TextInput::make('phone')
                            ->label('전화번호'),
                        TextInput::make('password')
                            ->label('비밀번호')
                            ->password()
                            ->required()
                            ->confirmed(),
                        TextInput::make('password_confirmation')
                            ->label('비밀번호 확인')
                            ->password()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $academyId = $this->getOwnerRecord()->id;

                        $counselor = Counselor::create([
                            'academy_id' => $academyId,
                        ]);

                        User::create([
                            'name' => $data['name'],
                            'username' => $data['username'],
                            'phone' => $data['phone'] ?? null,
                            'password' => Hash::make($data['password']),
                            'userable_type' => 'App\\Models\\Counselor',
                            'userable_id' => $counselor->id,
                            'academy_id' => $academyId,
                        ]);

                        Notification::make()->title('상담실 계정이 생성되었습니다.')->success()->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('changeRole')
                    ->label('권한 변경')
                    ->icon('heroicon-m-shield-check')
                    ->visible(fn($record) => $record->userable_type === 'App\\Models\\Teacher')
                    ->form([
                        ToggleButtons::make('role')
                            ->label('권한')
                            ->required()
                            ->inline()
                            ->grouped()
                            ->options([
                                'general' => '일반 강사',
                                'manager' => '중간 관리자',
                                'admin' => '관리자',
                            ]),
                    ])
                    ->fillForm(fn($record) => [
                        'role' => $record->userable->role ?? 'general',
                    ])
                    ->action(function ($record, array $data) {
                        $record->userable->update(['role' => $data['role']]);
                        Notification::make()->title('권한이 변경되었습니다.')->success()->send();
                    }),
                Tables\Actions\Action::make('resetPassword')
                    ->label('비밀번호 초기화')
                    ->icon('heroicon-m-key')
                    ->color('warning')
                    ->form([
                        TextInput::make('password')
                            ->label('새 비밀번호')
                            ->password()
                            ->required()
                            ->confirmed(),
                        TextInput::make('password_confirmation')
                            ->label('비밀번호 확인')
                            ->password()
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['password' => Hash::make($data['password'])]);
                        Notification::make()->title('비밀번호가 변경되었습니다.')->success()->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        // userable도 함께 삭제
                        if ($record->userable) {
                            $record->userable->delete();
                        }
                    }),
            ]);
    }
}
