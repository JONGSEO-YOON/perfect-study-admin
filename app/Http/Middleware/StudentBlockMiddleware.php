<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Filament\Facades\Filament;

class StudentBlockMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
    $user = Filament::auth()->user();

    if (!$user) {
      return redirect('/admin/login');
    }

    // Student 타입의 사용자 접근 차단
    if ($user->userable_type === Student::class) {
      return redirect('/');
    }

    return $next($request);
  }
}
