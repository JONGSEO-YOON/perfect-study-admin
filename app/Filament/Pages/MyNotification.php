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

class MyNotification extends Page implements HasForms
{

    use InteractsWithForms;

    protected static string $view = 'filament.pages.my-notification';

    protected static ?string $navigationLabel = '내 알림';

    protected static ?string $title = '내 알림';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = '설정';

    // protected ?string $maxContentWidth = '2xl';

}
