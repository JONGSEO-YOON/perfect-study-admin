<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class SignupController extends Controller
{
  public function signup(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'name' => ['required', 'string', 'max:255'],
      'birthday' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
      'username' => ['required', 'string', 'max:255', 'unique:users'],
      'password' => ['required', 'confirmed'],
    ]);

    if ($validator->fails()) {
      return response()->json([
        'message' => '입력 값을 확인해주세요.',
        'errors' => $validator->errors()
      ], 422);
    }

    $validated = $validator->validated();

    // 이름과 생년월일로 기존 회원 정보 확인
    // convert 6 digit birthday to carbon date
    // YYMMDD -> YYYY-MM-DD
    $validated['birthday'] = \Carbon\Carbon::createFromFormat('ymd', $validated['birthday'])->format('Y-m-d');
    $user = User::where('name', $validated['name'])
      ->where('birthed_at', 'LIKE', '%' . $validated['birthday'])
      ->first();

    if (!$user) {
      return response()->json([
        'message' => '등록된 회원 정보가 없습니다.'
      ], 404);
    }

    if ($user->username) {
      return response()->json([
        'message' => '이미 가입되었거나 가입 신청 상태입니다.'
      ], 400);
    }

    return DB::transaction(function () use ($user, $validated) {
      // 사용자 정보 업데이트
      $user->username = $validated['username'];
      $user->password = Hash::make($validated['password']);
      $user->save();

      $student = $user->userable;
      if (!$student) {
        return response()->json([
          'message' => '학생 정보가 없습니다.'
        ], 404);
      }

      $teacherUserIds = $student->classrooms()
        ->with('teacher.user')
        ->get()
        ->pluck('teacher.user.id')
        ->unique()
        ->values()
        ->all();

      if (!count($teacherUserIds)) {
        return response()->json([
          'message' => '담임 선생님 정보가 없습니다.'
        ], 404);
      }

      for ($i = 0; $i < count($teacherUserIds); $i++) {
        Notification::create([
          'user_id' => $teacherUserIds[$i],
          'type' => Notification::TYPE_MEMBERSHIP_APPROVAL,
          'title' => '회원 가입 신청',
          'content' =>  $user->name . '학생의 회원 가입 신청이 있습니다.',
          'data' => ['user_id' => $user->id, 'student_id' => $student->id]
        ]);
      }


      return response()->json([
        'message' => '회원가입이 신청되었습니다.'
      ]);
    });
  }
}
