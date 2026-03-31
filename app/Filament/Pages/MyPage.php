<?php

namespace App\Filament\Pages;

use App\Forms\Components\AddressInput;
use App\Forms\Components\PhoneInput;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Hash;

class MyPage extends Page implements HasForms
{

    use InteractsWithForms;

    protected static string $view = 'filament.pages.my-page';

    protected static ?string $navigationLabel = '마이페이지';

    protected static ?string $title = '마이페이지';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = '설정';

    protected static ?string $navigationIcon = null;

    protected ?string $maxContentWidth = '2xl';

    public $data = [];

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Section::make('mypage')
                ->label(false)
                ->heading(false)
                ->schema([
                    Grid::make(2)
                        ->schema([
                            FileUpload::make('profile_photo_path')
                                ->extraAttributes([
                                    'class' => '!items-center'
                                ])
                                ->label('사진')
                                ->image()
                                ->avatar()
                                ->placeholder('사진 업로드')
                                ->columnSpanFull(),
                            TextInput::make('name')
                                ->label('이름')
                                ->required(),
                            DatePicker::make('birthed_at')
                                ->label('생년월일')
                                ->required(),
                            Grid::make(2)
                                ->schema([
                                    Radio::make('gender')
                                        ->label('성별')
                                        ->inlineLabel()
                                        ->inline()
                                        ->required()
                                        ->options([
                                            '남' => '남',
                                            '여' => '여',
                                        ]),
                                ]),
                            Hidden::make('address'),
                            Hidden::make('postal_code'),
                            AddressInput::make('address-input')
                                ->label('주소')
                                ->columnSpanFull(),
                            TextInput::make('username')
                                ->label('계정')
                                ->readOnly(true)
                                ->required(),
                            PhoneInput::make('phone')
                                ->label('전화번호')
                                ->required(),

                            TextInput::make('password')
                                ->confirmed()
                                ->password()
                                ->label('비밀번호')
                                ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                                ->dehydrated(fn(?string $state): bool => filled($state))
                                ->required(fn(string $operation): bool => $operation === 'create'),
                            TextInput::make('password_confirmation')
                                ->password()
                                ->dehydrated(false)
                                ->label('비밀번호 확인')
                                ->required(fn(string $operation): bool => $operation === 'create'),

                            TextInput::make('email')
                                ->label('이메일'),

                        ])
                ])
        ]);
    }


    public function mount()
    {
        $user = User::find(auth()->id());
        $this->data = [
            ...$user->toArray(),
            'birthed_at' => $user->birthed_at?->format('Y-m-d'),
        ];
    }

    public function updateUser()
    {
        $phone = $this->data['phone'];
        if ($phone && is_array($phone)) {
            $phone = implode('-', $phone);
        }
        User::find(auth()->id())->update([
            'name' => $this->data['name'],
            'birthed_at' => $this->data['birthed_at'],
            'address' => $this->data['address'],
            'postal_code' => $this->data['postal_code'],
            'phone' => $phone,
            'email' => $this->data['email'],
            'gender' => $this->data['gender'],
            'profile_photo_path' => $this->data['profile_photo_path'],
        ]);
        if ($this->data['password'] ?? false) {
            User::find(auth()->id())->update([
                'password' => $this->data['password'],
            ]);
        }
        Notification::make()
            ->title('성공적으로 저장되었습니다.')
            ->success()
            ->send();
    }
}
