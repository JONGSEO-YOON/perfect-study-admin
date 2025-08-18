<?php

namespace App\Livewire\Parent;

use Livewire\Attributes\Validate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Student;

class Login extends Component
{
    public function mount()
    {
        if (session('parent_phone')) {
            return $this->redirect(route('parent.home'));
        }
    }

    #[Validate('required|numeric|min:10|max:11')]
    public $phone = '';

    #[Validate('required')]
    public $password = '';

    public function login()
    {
        // 1. 사용자 정의 유효성 검사 규칙을 추가합니다.
        // 이 규칙은 password가 phone의 뒷자리와 동일한지 확인합니다.
        $this->validate([
            'password' => [
                'required',
                // 클로저를 사용하여 사용자 정의 유효성 검사 규칙을 정의합니다.
                function ($attribute, $value, $fail) {
                    // 입력된 전화번호에서 숫자만 남깁니다.
                    $cleanPhone = preg_replace('/[^0-9]/', '', $this->phone);

                    // 전화번호의 길이에 따라 뒷자리를 추출합니다.
                    $expectedPassword = '';
                    if (strlen($cleanPhone) === 11) {
                        // 11자리 번호일 경우, 앞 3자리(010)를 제외한 8자리 추출
                        $expectedPassword = substr($cleanPhone, 3);
                    } else if (strlen($cleanPhone) === 10) {
                        // 10자리 번호일 경우, 앞 2자리(02)를 제외한 8자리 추출
                        $expectedPassword = substr($cleanPhone, 3);
                    }

                    // 입력된 비밀번호가 추출된 전화번호 뒷자리와 다르면 유효성 검사 실패
                    if ($value !== $expectedPassword) {
                        $fail('비밀번호가 일치하지 않습니다.');
                    }
                }
            ],
        ]);

        // 전화번호 형식 정리 로직을 수정합니다.
        $cleanPhone = preg_replace('/[^0-9]/', '', $this->phone);
        $formattedPhone = '';
        if (strlen($cleanPhone) === 11) {
            // 11자리 번호 (예: 01012345678) -> 010-1234-5678 형식으로 변환
            $formattedPhone = preg_replace('/(^0\d{2})(\d{4})(\d{4})$/', '$1-$2-$3', $cleanPhone);
        } else if (strlen($cleanPhone) === 10) {
            // 10자리 번호 (예: 0212345678) -> 02-1234-5678 또는 011-123-4567 형식으로 변환
            // 01로 시작하는 10자리 번호는 3-3-4 형식으로 변환
            if (preg_match('/^01[0-9]/', $cleanPhone)) {
                $formattedPhone = preg_replace('/(^0\d{2})(\d{3})(\d{4})$/', '$1-$2-$3', $cleanPhone);
            } else {
                // 그 외 10자리 번호는 2-4-4 형식으로 변환
                $formattedPhone = preg_replace('/(^0\d)(\d{4})(\d{4})$/', '$1-$2-$3', $cleanPhone);
            }
        } else {
            // 전화번호 형식이 맞지 않는 경우, 그대로 사용하거나 에러 처리 가능
            $formattedPhone = $cleanPhone;
        }

        // 3. 하이픈이 추가된 전화번호로 DB에서 학생을 조회합니다.
        $students = Student::where('phone_father', $formattedPhone)
            ->orWhere('phone_mother', $formattedPhone)
            ->get();
        // dd($students);

        if ($students->count() === 0) {
            session()->flash('error', '학생을 찾을 수 없습니다.');
        } else {
            session(['parent_phone' => $formattedPhone]);
            return $this->redirect(route('parent.home'));
        }



        // if ($student) {
        //     // 학생을 찾았을 경우의 처리
        //     session()->flash('success', '로그인 성공!');
        // } else {
        //     // 학생을 찾지 못했을 경우의 처리
        //     session()->flash('error', '전화번호가 일치하는 학생을 찾을 수 없습니다.');
        // }
    }

    #[Layout('components.layouts.simple')]
    public function render()
    {
        return view('livewire.parent.login');
    }
}
