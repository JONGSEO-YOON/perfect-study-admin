<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\TestSheet;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

class UserWebController extends Controller implements HasMiddleware
{

  public static function middleware(): array
  {
    return [
      'auth' => new Middleware('auth', except: ['showLoginForm', 'login']),
      'student.check' => new Middleware('student.check', except: ['showLoginForm', 'login']),
    ];
  }

  public function showLoginForm()
  {
    return view('login');
  }

  public function login(Request $request)
  {
    // 로그인 로직 구현
    // 예: Auth::attempt() 등
    $credentials = $request->validate([
      'username' => ['required'],
      'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
      $user = Auth::user();

      // Student 타입 체크
      if ($user->userable_type !== Student::class) {
        Auth::logout();
        return back()
          ->withErrors(['username' => '학생 계정으로만 로그인이 가능합니다.'])
          ->withInput();
      }

      $request->session()->regenerate();
      return redirect()->intended('/');
    }

    return back()
      ->withErrors(['username' => '아이디 또는 비밀번호가 일치하지 않습니다.'])
      ->withInput();
  }

  public function main()
  {
    $testsheets = TestSheet::where('status', 'progress')
      ->orWhere('status', 'completed')
      ->orderBy('start_date', 'desc')
      ->get();
    return view('main', compact('testsheets'));
  }

  public function showTestSheet($id)
  {
    return view('test-sheet', compact('id'));
  }

  // public function showTestSheetResult($id)
  // {
  //   $testsheet = TestSheet::findOrFail($id);
  //   return view('test-sheet-result', compact('testsheet'));
  // }

  public function showTestSheetResultQuestion($id, $question_id)
  {
    return view('test-sheet-result-question', compact('id', 'question_id'));
  }
}
