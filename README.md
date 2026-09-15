# Cinematic Love Letter · Mobile Wedding

김병진 & 최숙란 — 시네마틱 모바일 청첩장 (PHP 8+, Vanilla JS, MySQL Guestbook)

**Visual 80% · Playful 20%** · 공개 날짜는 **2027.09.25**만 사용

---

## 1. ENV

```bash
cp .env.example .env
```

```
KAKAO_JAVASCRIPT_KEY=
KAKAO_REST_API_KEY=
APP_KEY=랜덤한_긴_문자열

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=wedding
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
```

`APP_KEY`는 IP hash 등 서버 내부용이며 HTML에 노출되지 않습니다.  
DB 비밀번호는 로컬 MySQL 계정에 맞게 수정하세요.

## 2. MySQL 준비

```bash
php -m | grep pdo_mysql
```

`pdo_mysql`이 보여야 합니다.

스키마 파일: `scripts/schema.sql`  
또는 초기화 스크립트:

```bash
cd wedding
php scripts/init-db.php
```

`wedding` DB와 `guestbook` 테이블을 생성합니다.

## 3. 로컬 실행

```bash
php -S 127.0.0.1:8080
```

http://127.0.0.1:8080/

## 4. 공통 데이터

`includes/data.php`

## 5. 캐릭터 이미지 (Floating Outfit)

우측 하단 fixed couple.  
스크롤: casual → date → wedding → heart → ending

| state | file | outfit | pose |
|-------|------|--------|------|
| casual | `01-casual.png` | 니트·캐주얼 | 나란히 |
| date | `02-date.png` | 블레이저·핑크 원피스 | 손잡기 |
| wedding | `03-wedding.png` | 턱시도·드레스 | 웨딩 스탠딩 |
| heart | `04-heart.png` | 웨딩 유지 | 하트 제스처 |
| ending | `05-ending.png` | 웨딩 유지 | 마무리/인사 |

경로: `data.php` → `outfit.states`

## 6. Guestbook API

- `GET  /api/guestbook/list.php?limit=5&offset=0`
- `POST /api/guestbook/create.php` `{ name, message, password }`
- `POST /api/guestbook/delete.php` `{ id, password }`

비밀번호: `password_hash` / `password_verify`  
저장소: MySQL `wedding.guestbook`

## 7. 사진 · 초대문구 · 계좌 · 지도

`data.php`의 `gallery`, `invitation`, `accounts`, `location`

## 8. Kakao Map

`.env`의 `KAKAO_JAVASCRIPT_KEY` + Developers 허용 도메인 등록
