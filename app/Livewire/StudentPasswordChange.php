<?php

namespace App\Livewire;

use App\Models\Lecture;
use App\Models\Notice;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

class StudentPasswordChange extends Component
{
  public $current_password;
  public $new_password;
  public $new_password_confirmation;

  public function mount() {}

  public function changePassword()
  {
    $this->validate([
      'current_password' => 'required',
      'new_password' => 'required|min:6|confirmed',
      'new_password_confirmation' => 'required'
    ], [
      'current_password.required' => '현재 비밀번호를 입력해주세요.',
      'new_password.required' => '새 비밀번호를 입력해주세요.',
      'new_password.min' => '비밀번호는 최소 6자 이상이어야 합니다.',
      'new_password.confirmed' => '새 비밀번호가 일치하지 않습니다.',
      'new_password_confirmation.required' => '새 비밀번호 확인을 입력해주세요.'
    ]);

    $user = auth()->user();

    if (!Hash::check($this->current_password, $user->password)) {
      $this->addError('current_password', '현재 비밀번호가 일치하지 않습니다.');
      return;
    }

    $user->password = Hash::make($this->new_password);
    $user->save();

    $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

    // 성공 메시지 표시
    session()->flash('message', '비밀번호가 성공적으로 변경되었습니다.');
  }

  #[Layout('components.layouts.student')]
  public function render()
  {
    return view('livewire.student-password-change');
  }
}
