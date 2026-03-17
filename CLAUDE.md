# Claude Code 설정

## Git 커밋 메시지 규칙

- 커밋 메시지에 다음 내용을 **절대 포함하지 마세요**:
  - `🤖 Generated with [Claude Code](https://claude.com/claude-code)`
  - `Co-Authored-By: Claude`
  - 기타 Claude 관련 attribution

- 커밋 메시지는 **간결하게** 작성:
  ```
  기능 설명 (한 줄)

  - 세부 변경사항 (필요시)
  ```

## 예시

```bash
# 좋은 예
git commit -m "Add student withdrawal management"

# 나쁜 예 (절대 금지)
git commit -m "Add student withdrawal management

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude Opus 4.5 <noreply@anthropic.com>"
```

## 작업 이력

### 2026-02-26: v1.1 브랜치 롤백

- `v1.1` 브랜치를 `7db54b0` (2025-12-09, 어드민 > 결제 어드민 계정만 접근) 커밋으로 reset
- 롤백으로 제거된 커밋 4개:
  - `b908f65` — v1.1: 메뉴 재배치, 퇴원생 관리, 권한 축소, 학원 관리, 결산 및 부가세
  - `d40ead4` — Add Docker production deployment setup
  - `f1a1f0b` — Add firebase credentials to gitignore
  - `a7011ec` — Merge v2 into v1.1 - Add student enrollment management
- 백업 브랜치: `v1.1-backup-20260103` (복구 필요시 사용)
- GitHub에 force push 완료 (GitLab origin은 인증 미설정)

### 2026-03-18: 메뉴 재구성 및 결산 페이지 추가

- **좌측 메뉴 재구성**:
  - 교실 관리: 학생관리(1), 반관리(2), 강사관리(3), 상담관리(4), 보충달력(5), 공지사항(6), 학생공지(7)
  - 출결: 일별출결 현황(1), 월별출결 현황(2)
  - 문제 관리: 문제지 관리(1), 문제 은행(2)
  - 결제: 결제(1), 결산 및 부가세(2)
  - 자료실: 자료실(1), 강의실(2)
  - 설정: 내 알림(1), 마이페이지(2)
- **변경된 파일**: NoticeResource, StudentNoticeResource(자료실→교실관리), DailyAttendanceResource, MonthlyAttendance(교실관리→출결), TestSheetResource(교실관리→문제관리), 기타 navigationSort 조정
- **결산 및 부가세 신규 페이지**: `app/Filament/Pages/Settlement.php`
  - 토스 페이먼츠 API `/v1/transactions`로 기간별 거래 내역 직접 조회
  - 총 매출, 취소/환불, 공급가액, 부가세(10%) 자동 계산
  - 카드/기타 결제수단별 집계
  - 거래 내역 테이블 (일시, 주문번호, 주문명, 결제수단, 금액, 상태)
  - root_admin, admin만 접근 가능
  - 뷰: `resources/views/filament/pages/settlement.blade.php`

### 2026-03-18: 보충 달력 기능 추가

- **신규 테이블**: `supplementary_schedules`
  - `teacher_id` — 담당 선생님
  - `student_id` — 보충 학생 (nullable)
  - `classroom_id` — 반 (nullable)
  - `scheduled_date` — 보충 날짜
  - `start_time`, `end_time` — 시간
  - `room` — 강의실
  - `reason` — 보충 사유
  - `memo` — 메모
- **신규 파일**:
  - `app/Models/SupplementarySchedule.php`
  - `app/Filament/Resources/SupplementaryScheduleResource.php`
  - `app/Filament/Resources/SupplementaryScheduleResource/Pages/ListSupplementarySchedules.php`
  - `database/migrations/2026_03_18_000000_create_supplementary_schedules_table.php`
- **메뉴 위치**: 교실 관리 > 보충 달력 (navigationSort: 9)
- **권한**: general(일반 강사)은 자기 보충만 조회/입력, manager 이상은 전체 조회

### 2026-03-18: 대시보드 위젯 추가

- 출결 현황, 수납 현황, 신규/퇴원생 현황, 상담 현황 위젯 추가
- 위젯 파일: `app/Filament/Widgets/Daily*.php`, `StudentEnrollmentWidget.php`, `CounselingWidget.php`

### 2026-03-18: 상담 관리 필터 개선

- 기간(시작일/종료일), 상담자(선생님), 학생, 상태 필터 추가
- 상담 횟수 통계 위젯 추가 (`CounselingResource/Widgets/CounselingStatsWidget.php`)

### 2026-03-18: 버그 수정 4건

- 출석 체크 시 지각 자동 판단 (기존 하드코딩 false → 수업 시작시간 비교)
- 성적표 배점 합계 오류 (`count(questions)` → `total_score`)
- 문제 균등분배 시 부족분이 첫 번째 레벨에 몰리는 문제 수정
- 새 문제 추가 모달 500 에러 (division by zero) 수정

## 알려진 이슈 (TODO)

### MySQL sort_buffer_size 및 test_sheets 쿼리 최적화

- **증상**: 학생용 페이지 500 에러 (`SQLSTATE[HY001]: Memory allocation error: 1038 Out of sort memory`)
- **임시 조치**: `SET GLOBAL sort_buffer_size = 67108864` (8MB → 64MB). MySQL 컨테이너 재시작시 초기화됨
- **영구 조치 필요**:
  1. MySQL 설정 파일(my.cnf)에 `sort_buffer_size = 64M` 추가하거나 docker-compose.yml에 반영
  2. `test_sheets` 테이블 쿼리 최적화 — `json_contains()` 다중 호출 + ORDER BY 조합이 메모리 과다 사용
  - 대상 파일: `test_sheets` 관련 Livewire 컴포넌트 또는 쿼리 빌더
  - 고려사항: JSON 컬럼(`target_grades`, `target_classrooms`, `target_students`, `target_levels`)을 관계 테이블로 정규화하면 근본적 해결 가능
