# Perfect Study 배포 가이드

## 현재 버전 정보

| 구분 | 버전 | 설명 |
|------|------|------|
| **서버 이미지** | `perfectstudy:v1.0` | 빌드 완료 (2.31GB) |
| **현재 실행 중** | `sail-8.3/app` | 기존 개발용 이미지 |
| **로컬 개발** | `v1.1` ~ | 새로운 기능 개발 시작 버전 |

> **v1.0**은 2024년 12월 서버 코드를 이미지로 빌드한 기준 버전입니다.
> 로컬에서 새 기능을 개발할 때는 **v1.1**부터 시작하세요.

### 프로덕션 이미지(v1.0)로 전환하기

```bash
cd /home/ubuntu/admin
./vendor/bin/sail down
APP_VERSION=v1.0 docker compose -f docker-compose.prod.yml up -d
sudo supervisorctl restart laravel-worker:*
```

---

## 서버 구성

```
┌─────────────────────────────────────────────────────────────────┐
│                    Nginx (리버스 프록시)                          │
│  • 도메인: perfectstudy.co.kr                                    │
│  • HTTPS (Let's Encrypt) → localhost:8080                       │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                Docker Compose (Laravel Sail)                     │
├─────────────────────────────────────────────────────────────────┤
│  perfectstudy:버전태그       :8080 (Laravel PHP 8.3)             │
│  MySQL 8.0                  :3306                                │
│  Redis                      :6379                                │
│  Meilisearch                :7700                                │
└─────────────────────────────────────────────────────────────────┘
```

### 디렉토리 구조

| 경로 | 설명 |
|------|------|
| `/home/ubuntu/admin` | Laravel 메인 애플리케이션 |
| `/home/ubuntu/images` | Docker 이미지 파일 저장소 |
| `/home/ubuntu/db_backup` | MySQL 백업 저장소 |

---

## 배포 흐름 (Docker 이미지 방식)

```
┌─────────────────┐                    ┌─────────────────┐
│      로컬       │    VS Code SSH     │    AWS 서버     │
│                 │    파일 복사        │                 │
│  코드 수정      │                    │                 │
│  docker build   │ ──────────────────▶│  docker load    │
│  docker save    │   .tar.gz 파일     │  서비스 재시작   │
│                 │                    │                 │
└─────────────────┘                    └─────────────────┘
```

---

## 배포 방법 (로컬 → 서버)

### Step 1: 로컬에서 이미지 빌드

```bash
# 프로젝트 폴더에서 (v1.1부터 시작)
docker build -f Dockerfile.prod -t perfectstudy:v1.1 .
```

### Step 2: 이미지 파일로 저장

```bash
docker save perfectstudy:v1.1 | gzip > perfectstudy-v1.1.tar.gz
```

### Step 3: VS Code로 서버에 복사

VS Code SSH 연결 상태에서:
- `perfectstudy-v1.1.tar.gz` 파일을 `/home/ubuntu/images/` 폴더에 드래그앤드롭

### Step 4: 서버에서 이미지 로드 & 실행

```bash
# 이미지 로드
docker load < /home/ubuntu/images/perfectstudy-v1.1.tar.gz

# 현재 컨테이너 중지
cd /home/ubuntu/admin
./vendor/bin/sail down

# 새 버전으로 실행
APP_VERSION=v1.1 docker compose -f docker-compose.prod.yml up -d

# 마이그레이션 (필요시)
docker exec -it admin-laravel.test-1 php artisan migrate --force

# 워커 재시작
sudo supervisorctl restart laravel-worker:*
```

---

## 버전 관리

### 버전 태그 규칙 (예시)

| 태그 | 설명 |
|------|------|
| `v1.0` | 첫 번째 정식 버전 |
| `v1.1` | 마이너 업데이트 |
| `v2.0` | 메이저 업데이트 (멀티테넌시 등) |
| `v2.0-beta` | 베타 버전 |
| `latest` | 최신 버전 |

---

### 예시: v2.0 배포하기

#### 로컬에서

```bash
# 1. 이미지 빌드
docker build -f Dockerfile.prod -t perfectstudy:v2.0 .

# 2. 파일로 저장
docker save perfectstudy:v2.0 | gzip > perfectstudy-v2.0.tar.gz

# 3. VS Code로 /home/ubuntu/images/ 에 복사
```

#### 서버에서

```bash
# 1. 이미지 로드
docker load < /home/ubuntu/images/perfectstudy-v2.0.tar.gz

# 2. 현재 컨테이너 중지
cd /home/ubuntu/admin
./vendor/bin/sail down

# 3. v2.0으로 실행
APP_VERSION=v2.0 docker compose -f docker-compose.prod.yml up -d

# 4. 마이그레이션 (DB 변경 있을 때)
docker exec -it admin-laravel.test-1 php artisan migrate --force

# 5. 워커 재시작
sudo supervisorctl restart laravel-worker:*
```

---

### 예시: v1.0으로 롤백하기

```bash
# 1. 현재 컨테이너 중지
cd /home/ubuntu/admin
./vendor/bin/sail down

# 2. v1.0으로 실행
APP_VERSION=v1.0 docker compose -f docker-compose.prod.yml up -d

# 3. 워커 재시작
sudo supervisorctl restart laravel-worker:*
```

> 롤백 전 `docker images perfectstudy`로 사용 가능한 버전 확인
> v1.0은 항상 서버에 보관하여 언제든 롤백 가능하도록 합니다.

---

### 예시: 특정 버전 삭제

```bash
# 단일 버전 삭제
docker rmi perfectstudy:v1.2

# 여러 버전 삭제
docker rmi perfectstudy:v1.2 perfectstudy:v1.3 perfectstudy:v1.4
```

> **주의**: v1.0은 기준 버전이므로 삭제하지 마세요!

---

### 현재 이미지 목록 확인

```bash
docker images perfectstudy

# 출력 예시:
# REPOSITORY     TAG       IMAGE ID       CREATED        SIZE
# perfectstudy   v1.3      abc123def456   2 hours ago    2.3GB
# perfectstudy   v1.2      789ghi012jkl   3 days ago     2.3GB
# perfectstudy   v1.1      456abc789def   1 week ago     2.3GB
# perfectstudy   v1.0      c78bb39510e8   기준 버전       2.3GB  ← 삭제 금지
```

---

### 현재 실행 중인 버전 확인

```bash
docker ps --format "table {{.Image}}\t{{.Status}}"

# 출력 예시:
# IMAGE                  STATUS
# perfectstudy:v1.1      Up 2 hours
# mysql/mysql-server     Up 2 hours
# redis:alpine           Up 2 hours
```

---

## 서버 관리 명령어

### Docker

```bash
# 상태 확인
docker ps

# 로그 확인
docker logs -f admin-laravel.test-1

# 컨테이너 재시작
docker restart admin-laravel.test-1
```

### 데이터베이스

```bash
# MySQL 접속
docker exec -it admin-mysql-1 mysql -usail -p laravel

# DB 백업
/home/ubuntu/db_backup_script.sh

# 마이그레이션
docker exec -it admin-laravel.test-1 php artisan migrate --force
docker exec -it admin-laravel.test-1 php artisan migrate:rollback
```

### 큐 워커

```bash
# 상태 확인
sudo supervisorctl status

# 재시작
sudo supervisorctl restart laravel-worker:*
```

### 캐시 클리어

```bash
docker exec -it admin-laravel.test-1 php artisan optimize:clear
```

---

## 트러블슈팅

### 502 Bad Gateway

```bash
docker ps                           # 컨테이너 확인
docker logs admin-laravel.test-1    # 로그 확인
./vendor/bin/sail up -d             # 재시작
```

### 워커 FATAL

```bash
docker ps                                      # Docker 실행 확인
sudo supervisorctl restart laravel-worker:*    # 워커 재시작
```

### 권한 오류

```bash
sudo chown -R ubuntu:ubuntu storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## 파일 위치 요약

| 파일 | 위치 |
|------|------|
| Dockerfile (프로덕션) | `/home/ubuntu/admin/Dockerfile.prod` |
| Docker Compose (프로덕션) | `/home/ubuntu/admin/docker-compose.prod.yml` |
| Docker Ignore | `/home/ubuntu/admin/.dockerignore` |
| 이미지 파일 | `/home/ubuntu/images/*.tar.gz` |
| DB 백업 | `/home/ubuntu/db_backup/` |
| Laravel 로그 | `/home/ubuntu/admin/storage/logs/` |
| Nginx 설정 | `/etc/nginx/sites-enabled/default` |
| Supervisor 설정 | `/etc/supervisor/conf.d/laravel-worker.conf` |
