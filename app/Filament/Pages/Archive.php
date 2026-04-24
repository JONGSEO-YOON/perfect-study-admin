<?php

namespace App\Filament\Pages;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Resource;
use App\Models\ResourceCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Url;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class Archive extends Page implements HasForms, HasTable
{

    use InteractsWithForms, InteractsWithTable;

    protected static string $view = 'filament.pages.archive';

    protected static ?string $navigationLabel = '자료실';

    protected static ?string $title = '자료실';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = '자료실';


    #[Url]
    public ?array $tableFilters = null;

    public $currentSubCategoryId = null;

    public $resourceCategories;

    public $isMyResource;

    public static function _form(): array
    {
        return [
            //
            Hidden::make('resource_sub_category_id'),
            Grid::make(2)
                ->schema([
                    TextInput::make('title')
                        ->label('제목')
                        ->required(),
                    RichEditor::make('content')
                        ->label('내용')
                        ->required()
                        ->columnSpanFull(),
                    FileUpload::make('attachments')
                        ->label('첨부 파일')
                        ->multiple()
                        ->placeholder('클릭하거나 파일을 드래그하여 업로드')
                        ->previewable(false)
                        ->downloadable(true)
                        ->columnSpanFull()
                ]),
            Toggle::make('pinned_at')
                ->mutateDehydratedStateUsing(function ($state) {
                    if (!$state) {
                        return null;
                    }
                    return now();
                })
                ->label('상단 고정'),
        ];
    }

    public function table(Table $table): Table
    {
        $canManage = self::canManageResources();

        return $table
            ->recordAction($canManage ? 'edit' : null)
            ->query(Resource::query()->with('author'))
            ->emptyStateHeading('자료가 없습니다.')
            ->emptyStateDescription($canManage ? '자료를 등록해주세요.' : '아직 공유된 자료가 없습니다.')
            ->modifyQueryUsing(function (Builder $query) {
                $query->orderBy('pinned_at', 'desc');
            })
            ->columns([
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('title')
                    ->label('제목')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        return new HtmlString($record->pinned_at ? '<div class="flex items-center gap-x-1"><img class="size-4" src="/icons/pin.svg" />' . $record->title . '</div>' : $record->title);
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('author.name')
                    ->label('작성자')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->label('작성일')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('resource_sub_category_id')
                    ->form([
                        Hidden::make('resource_sub_category_id'),
                    ])
                    ->query(function ($query, $data) {
                        $query->where('resource_sub_category_id', $data['resource_sub_category_id']);
                    }),
            ], layout: FiltersLayout::Hidden)
            ->headerActions([
                CreateAction::make()
                    ->form(self::_form())
                    ->modalHeading('자료 등록하기')
                    ->createAnother(false)
                    ->modalWidth('4xl')
                    ->icon('heroicon-m-plus-circle')
                    ->label('자료 등록하기')
                    ->modalWidth('4xl')
                    ->fillForm([
                        'resource_sub_category_id' => $this->currentSubCategoryId,
                    ])
                    ->modalSubmitActionLabel('저장')
                    ->visible(fn() => $canManage),
            ])
            ->actions([
                // 자료 다운로드 (모든 학원 사용 가능)
                \Filament\Tables\Actions\Action::make('download')
                    ->label('다운로드')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('success')
                    ->visible(fn($record) => !empty($record->attachments))
                    ->modalHeading(fn($record) => $record->title . ' - 첨부 파일')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('닫기')
                    ->modalContent(fn($record) => view('filament.components.modals.resource-downloads', [
                        'record' => $record,
                    ])),
                // 수정 (퍼펙트 스터디만)
                EditAction::make()
                    ->form(self::_form())
                    ->modalHeading('수정하기')
                    ->modalWidth('4xl')
                    ->visible(fn() => $canManage),
            ])
            ->bulkActions(
                $canManage ? [
                    BulkActionGroup::make([
                        DeleteBulkAction::make()
                            ->modalHeading('자료 삭제'),
                    ]),
                ] : []
            )
            ->defaultSort('created_at', 'desc');
    }

    /**
     * 자료 등록/수정/삭제/분류관리 가능 여부
     * - root_admin: 항상 가능
     * - 퍼펙트 스터디 (academy_id=1): 가능
     * - 그 외 학원: 불가 (다운로드만 가능)
     */
    public static function canManageResources(): bool
    {
        if (!auth()->check()) return false;

        $user = auth()->user();
        if ($user->role === 'root_admin') return true;

        return $user->academy_id === 1;
    }

    public static function canAccess(): bool
    {
        if (!auth()->user()->isRoleAbove('admin', true)) {
            return false;
        }

        $user = auth()->user();

        if ($user->role === 'root_admin') {
            return true;
        }

        $academy = $user->academy;

        if ($academy) {
            $settings = $academy->settings ?? [];
            if (isset($settings['resources_visible']) && $settings['resources_visible'] === false) {
                return false;
            }
        }

        return true;
    }

    public function mount()
    {
        $this->resourceCategories = ResourceCategory::with('subCategories')->get();
        $this->isMyResource = request()->query('type') === 'my-resource';
        if (!$this->isMyResource) {
            if ($this->tableFilters['resource_sub_category_id']['resource_sub_category_id'] ?? null) {
            } else {
                $this->tableFilters = [
                    'resource_sub_category_id' => [
                        'resource_sub_category_id' => $this->resourceCategories->first()->subCategories->first()?->id,
                    ],
                ];
            }
            $this->currentSubCategoryId = $this->tableFilters['resource_sub_category_id']['resource_sub_category_id'];
        }
    }
}
