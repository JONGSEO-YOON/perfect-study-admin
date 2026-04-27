<?php

namespace App\Http\Controllers;

use App\Models\Scopes\AcademyScope;
use App\Models\Student;
use App\Models\TestSheet;
use App\Models\User;
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
    if (auth()->check()) {
      if (auth()->user()->userable instanceof Student) {
        return redirect()->route('main');
      } else {
        return redirect('/admin');
      }
    }
    return view('login');
  }

  public function login(Request $request)
  {

    $credentials = $request->validate([
      'username' => ['required'],
      'password' => ['required'],
    ]);

    // 서브도메인 학원 필터링
    $academy = app()->bound('current_academy') ? app('current_academy') : null;
    if ($academy) {
      $credentials['academy_id'] = $academy->id;
    }

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

    // 같은 사람(전화번호 기준)이 여러 학원에 등록된 경우 모두 통합
    $relatedStudents = $this->getRelatedStudents($student);
    $testSheetIds = $this->collectAvailableTestSheetIds($relatedStudents);

    $testsheets = TestSheet::query()
      ->withoutGlobalScopes([AcademyScope::class])
      ->whereIn('id', $testSheetIds)
      ->inProgressOrCompleted()
      ->hasQuestions()
      ->when($type === 'homework', function ($query) {
        return $query->whereJsonContains('tags', '숙제');
      })
      ->when($type !== 'homework', function ($query) {
        return $query->whereJsonDoesntContain('tags', '숙제');
      })
      ->latest('start_date')
      ->paginate($perPage);

    if (request()->ajax()) {
      if ($testsheets->isEmpty()) {
        return '';
      }
      return view('components.test-sheet-list', ['testsheets' => $testsheets]);
    }

    $remaining_count = $this->_getRemainingTestsCount($student);

    return view('main', compact('testsheets', 'type', 'perPage', 'page', 'remaining_count'));
  }

  /**
   * 같은 전화번호로 여러 학원에 등록된 학생 계정들을 반환.
   * 안전장치: 유효한 휴대전화 형식(010-XXXX-XXXX, 11자리)일 때만 다른 학원 매칭.
   * 그 외(빈 phone, 기본값 등)는 본인 계정만 반환 → 학원 간 데이터 누수 차단.
   */
  protected function getRelatedStudents(Student $student)
  {
    $phone = trim((string) auth()->user()->phone);
    $digits = preg_replace('/[^0-9]/', '', $phone);

    // 11자리 010 으로 시작하는 정상 휴대전화만 cross-academy 매칭
    if (strlen($digits) !== 11 || !str_starts_with($digits, '010')) {
      return collect([$student]);
    }

    // 정확히 같은 phone 문자열로 등록된 다른 학생 계정 조회
    $relatedStudentIds = User::query()
      ->where('phone', $phone)
      ->where('userable_type', Student::class)
      ->pluck('userable_id')
      ->push($student->id)
      ->unique()
      ->all();

    return Student::query()
      ->withoutGlobalScopes([AcademyScope::class])
      ->whereIn('id', $relatedStudentIds)
      ->get();
  }

  /**
   * 여러 학생 계정에 대해 availableFor()를 각각 호출하여
   * 노출 가능한 시험지 id들을 합집합으로 반환.
   */
  protected function collectAvailableTestSheetIds($relatedStudents): array
  {
    $ids = [];
    foreach ($relatedStudents as $rs) {
      $ids = array_merge($ids, TestSheet::query()
        ->withoutGlobalScopes([AcademyScope::class])
        ->availableFor($rs)
        ->pluck('id')
        ->all());
    }
    return array_values(array_unique($ids));
  }

  public function _getRemainingTestsCount($student)
  {
    $relatedStudents = $this->getRelatedStudents($student);

    // 안전장치 동일 적용: 유효 phone 일 때만 cross-academy 매칭
    $phone = trim((string) auth()->user()->phone);
    $digits = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($digits) === 11 && str_starts_with($digits, '010')) {
      $relatedUserIds = User::query()
        ->where('phone', $phone)
        ->where('userable_type', Student::class)
        ->pluck('id')
        ->push(auth()->id())
        ->unique()
        ->all();
    } else {
      $relatedUserIds = [auth()->id()];
    }
    $testSheetIds = $this->collectAvailableTestSheetIds($relatedStudents);

    $base_query = TestSheet::query()
      ->withoutGlobalScopes([AcademyScope::class])
      ->whereIn('id', $testSheetIds)
      ->inProgress()
      ->hasQuestions()
      ->whereDoesntHave('answers', function ($query) use ($relatedUserIds) {
        $query->whereIn('user_id', $relatedUserIds)
          ->where('status', 'completed');
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
