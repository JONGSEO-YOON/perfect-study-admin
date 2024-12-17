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

      // is_active 체크 추가
      if (!$user->is_active) {
        Auth::logout();
        return back()
          ->withErrors(['username' => '아직 승인 대기 중입니다. 관리자 승인 후 이용하실 수 있습니다.'])
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
    // 현재 로그인한 사용자의 student 정보 가져오기
    $student = auth()->user()->userable;
    if (!$student || !($student instanceof Student)) {
      return view('main', ['testsheets' => collect()]);
    }

    $perPage = request('per_page', 10);
    $page = request('page', 1);
    $type = request('type', 'test');

    $testsheets = TestSheet::inProgressOrCompleted()
      ->hasQuestions()
      ->availableFor($student)
      ->when($type === 'homework', function ($query) {
        return $query->whereJsonContains('tags', '숙제');
      })
      ->when($type !== 'homework', function ($query) {
        return $query->whereJsonDoesntContain('tags', '숙제');
      })
      ->latest('start_date')
      ->paginate($perPage)
      ->through(function ($testsheet) {
        return $testsheet;
      });

    if (request()->ajax()) {
      if ($testsheets->isEmpty()) {
        return '';
      }
      return view('components.test-sheet-list', ['testsheets' => $testsheets]);
    }

    $remaining_count = $this->_getRemainingTestsCount($student);

    return view('main', compact('testsheets', 'type', 'perPage', 'page', 'remaining_count'));
  }

  public function _getRemainingTestsCount($student)
  {
    $base_query = TestSheet::inProgress()
      ->availableFor($student)
      ->hasQuestions()
      ->whereDoesntHave('latestUserAnswer', function ($query) use ($student) {
        $query->where('status', 'completed');
      });

    return [
      'homework' => (clone $base_query)
        ->whereJsonContains('tags', '숙제')
        ->count(),
      'test' => (clone $base_query)
        ->whereJsonDoesntContain('tags', '숙제')
        ->count()
    ];
  }
}
