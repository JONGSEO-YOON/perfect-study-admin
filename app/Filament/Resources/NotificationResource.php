<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Filament\Resources\NotificationResource\RelationManagers;
use App\Models\Notification;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotificationResource extends Resource
{
    protected static ?string $navigationLabel = '내 알림';

    protected static ?string $title = '내 알림';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = '설정';

    protected static ?string $model = Notification::class;

    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->unread();
            })
            ->defaultSort('id', 'desc')
            ->emptyStateHeading('알림이 없습니다.')
            ->columns([
                //
                TextColumn::make('type')
                    ->label('유형')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'membership_approval' => '회원 가입 승인',
                            'counseling_request' => '상담 요청',
                            'counseling_confirmation' => '상담 확인',
                            default => $state,
                        };
                    }),
                TextColumn::make('title')
                    ->label('제목')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('content')
                    ->label('내용')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // ?tableFilters[student_id][value]=2
                Tables\Actions\Action::make('manage-counseling')
                    ->label('상담 관리')
                    ->visible(fn(Notification $notification) => $notification->type === Notification::TYPE_COUNSELING_REQUEST || $notification->type === Notification::TYPE_COUNSELING_CONFIRMATION)
                    ->icon('heroicon-m-check-circle')
                    ->url(fn(Notification $notification) => '/admin/counselings?tableFilters[student_id][value]=' . $notification->data['student_id']),
                Tables\Actions\Action::make('approceMembership')
                    ->label('승인 처리')
                    ->visible(fn(Notification $notification) => $notification->type === Notification::TYPE_MEMBERSHIP_APPROVAL)
                    ->icon('heroicon-m-check-circle')
                    ->requiresConfirmation('승인 처리하시겠습니까?')
                    ->action(function (Notification $notification) {
                        $studentId = $notification->data['student_id'] ?? null;
                        if (!$studentId) {
                            return;
                        }
                        $student = Student::find($studentId);
                        if (!$student) {
                            return;
                        }
                        $student->user->is_active = true;
                        $student->user->save();

                        $notification->markAsRead();
                    }),
                Tables\Actions\Action::make('markAsRead')
                    ->label('읽음 처리')
                    ->icon('heroicon-m-x-mark')
                    ->requiresConfirmation('읽음 처리하시겠습니까?')
                    ->action(fn(Notification $notification) => $notification->markAsRead()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
            // 'create' => Pages\CreateNotification::route('/create'),
            // 'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
