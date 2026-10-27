# 게스트 스냅

기존 PHP 8+ 직접 경로 라우팅, `includes/env.php`, `getPdo()` PDO/MySQL 연결과 화면 스타일을 재사용합니다. 변경 범위는 `guest-snap/`입니다. 청첩장의 기존 게스트 스냅 링크로 진입합니다. 사진을 공개하거나 다운로드하는 API는 없습니다.

## 동작

- 이름(1~20자)과 전화번호(숫자 8~15자리) 필수 입력, 최대 장수(`SNAP_MAX_FILES`, 기본 20장) 선택, 미리보기, 사진별 순차 전송 진행률 및 서버 저장 확인, 전체 성공/일부 실패/전체 실패 안내.
- JPEG/PNG/WebP만 허용. 파일당 15MiB, 3,000만 화소. 서버에서 업로드 출처, 실제 바이트 수, fileinfo MIME, 이미지 헤더와 GD 디코딩 검사. 확장자나 브라우저 MIME만 신뢰하지 않습니다. 원본 파일명은 정리해 DB에 기록하고 저장명은 `이름_전화번호_날짜시간_난수.확장자`로 생성합니다. 전화번호의 하이픈·공백·괄호·국가번호 앞 `+`는 제거하고, 이름의 공백 및 파일명 구분 문자는 `_`로 바꿉니다.
- HEIC/HEIF는 현재 서버에 디코더가 없으므로 원본을 받지 않습니다. JPEG로 변환 후 업로드하도록 화면·서버에서 안내합니다. iPhone 사진 선택기가 JPEG로 변환해 전달하는 경우는 정상 지원합니다. 자동 변환을 전제로 하지 않습니다. 기존 HEIC는 사진/미리보기 앱에서 JPEG로 내보내기; 향후 촬영은 설정 → 카메라 → 포맷 → 높은 호환성. [Apple 형식 안내](https://support.apple.com/ko-kr/116944).
- 원본 사진의 EXIF/GPS 정보는 유지됩니다. Drive 폴더는 공유하지 마세요. 서버가 업로드 전에 폴더 권한을 검사하고 소유자 외 공유 권한이 있으면 차단합니다. 이후 소유자가 수동 공유하면 이 보호 범위 밖입니다.
- 서버 세션/CSRF 검증, 오픈 날짜와 활성화 스위치, IP당 시간당 120회 및 전체 하루 2,000회 시도 제한. 프록시 전달 헤더는 신뢰하지 않고 `REMOTE_ADDR` 사용. MySQL advisory lock으로 전체 업로드 1건씩 처리하며 경합 시 429를 반환합니다. 바쁜 경우 같은 화면에서 재시도하세요. 제한에는 실패 및 재시도도 포함됩니다.
- 한 요청에 한 장, 배치당 서버 예약 최대 `SNAP_MAX_FILES`건. 기존 항목을 삭제하고 교체해도 이미 서버 예약된 건수는 초기화되지 않습니다. 새 배치는 페이지 새로고침 또는 현재 배치 전체 완료 후 새 사진 선택으로 시작합니다.
- 브라우저는 배치/사진 ID를 유지하고 완료된 사진은 재전송하지 않습니다. 동일 화면/세션의 재시도만 중복 방지 대상입니다. 새로고침 후 새 선택은 새 업로드입니다. 오류가 나면 화면을 유지하세요. 자동 무한 재시도는 하지 않습니다.

## 필요한 환경변수

아래 표의 항목을 **프로젝트 루트 `.env`** 또는 서버 환경에 추가합니다. 기존 로더는 루트 `.env`에 같은 키가 있으면 서버 환경보다 우선하므로 한 곳에서 관리하세요.

| 변수 | 용도 |
|---|---|
| `GUEST_SNAP_ENABLED` | 기본 `0`, 실제 연결 점검 완료 후 `1` |
| `GUEST_SNAP_GOOGLE_CLIENT_ID` | 아래 Web application OAuth 클라이언트 ID |
| `GUEST_SNAP_GOOGLE_CLIENT_SECRET` | 같은 클라이언트의 비밀 값 |
| `GUEST_SNAP_GOOGLE_REFRESH_TOKEN` | 최초 연결 도구가 터미널에 출력하는 갱신 토큰 |
| `GUEST_SNAP_GOOGLE_FOLDER_ID` | 최초 연결 도구가 생성한 비공개 전용 폴더 ID |
| `APP_KEY` | 기존 서버 비밀 키. 충분히 긴 난수 필수. 운영 중 변경 시 재시도 식별도 바뀜 |
| `WEDDING_DATE` | 예식일(Y-m-d, Asia/Seoul). 당일 00:00부터 업로드 오픈 |
| `DB_*` | 기존 MySQL 연결 설정 재사용 |

실제 인증 정보나 임의 폴더 ID는 코드/예제에 포함하지 않았습니다. `.env`는 저장소 커밋 및 웹 접근 금지, 소유자만 읽을 수 있게 설정합니다.

## bin 관리 도구: 언제 실행하나요?

`bin/`은 운영자가 터미널에서 실행하는 도구입니다. 아래 명령은 프로젝트 루트(`wedding/`)에서 실행합니다. 두 파일 모두 브라우저로 접근하면 404를 반환하며, 하객의 사진 전송은 `api/upload.php`가 처리합니다.

| 명령 | 하는 일 | 실행 시점·장소 |
|---|---|---|
| `php guest-snap/bin/connect.php` | Google 계정 연결, 갱신 토큰 발급, 비공개 저장 폴더 생성 | 최초 설정 또는 재연결 시, 운영자 로컬 컴퓨터 |
| `php guest-snap/bin/manage.php init` | 업로드 기록·요청 제한 테이블 생성, 기존 테이블에 필요한 열 추가 | 최초 배포 또는 DB 구조 변경 배포 시, 대상 DB에 연결되는 환경 |
| `php guest-snap/bin/manage.php reconcile` | Drive에 저장된 파일을 확인해 미완료 DB 기록 복구, 만료된 요청 제한 기록 정리 | 운영 서버에서 주기적으로 또는 장애 후 수동 실행 |

### connect.php — Google 계정 연결

먼저 Google Client ID/Secret을 루트 `.env`에 설정한 뒤 실행합니다.

```sh
php guest-snap/bin/connect.php
```

출력된 Google 주소를 같은 컴퓨터의 브라우저에서 열고 사진 보관 계정으로 로그인·동의합니다. 터미널에 출력되는 `GUEST_SNAP_GOOGLE_REFRESH_TOKEN`과 `GUEST_SNAP_GOOGLE_FOLDER_ID`를 배포 서버의 환경변수 또는 루트 `.env`에 저장합니다. 도구가 `.env`를 자동 수정하지는 않습니다.

설정이 끝나면 사진을 올릴 때마다 실행할 필요는 없습니다. 평소 액세스 토큰 갱신은 업로드 서버가 자동 처리합니다. 연결 권한을 취소했거나 갱신 토큰이 무효화되어 재연결이 필요할 때 다시 실행합니다. 실행할 때마다 새 폴더를 생성하므로 같은 계정·OAuth 앱에서 기존 저장 폴더를 계속 사용하려면 기존 폴더 ID를 유지하세요. 자세한 Console 설정은 아래 최초 연결 절차를 참고하세요.

### manage.php init — DB 준비

```sh
php guest-snap/bin/manage.php init
```

`guest_snap`(사진 기록)과 `guest_snap_rate`(요청 제한) 테이블을 준비합니다. 기존 테이블은 삭제하지 않으며, 현재 코드가 지원하는 누락 열(`guest_phone`, `checked_at`)을 추가합니다. 전화번호 입력 기능을 기존 서버에 배포할 때도 이 명령을 실행하세요. 기존 이름·사진 파일명은 변경하지 않습니다.

Google 계정을 연결하거나 사진을 업로드하는 명령은 아닙니다. 대상 DB의 `DB_*` 설정과 CREATE/ALTER 권한이 필요하며, 초기화 후 일반 업로드마다 실행할 필요는 없습니다.

### manage.php reconcile — 미완료 기록 복구

```sh
php guest-snap/bin/manage.php reconcile
```

Drive 저장 직후 DB 완료 처리에 실패했거나 서버가 중단되면 기록이 `pending`으로 남을 수 있습니다. 이 명령은 해당 Drive 파일을 확인하고 DB를 `complete`로 갱신합니다. 파일이 없는 기록은 하객이 같은 화면에서 재시도할 수 있도록 남깁니다. 사진을 새로 업로드하거나 삭제하지 않습니다.

한 번에 최대 100건을 순환 점검하고 만료된 요청 제한 기록도 정리합니다. 운영 서버에서 cron 등으로 5분마다 실행하도록 설정하거나 장애 후 수동 실행할 수 있습니다. 업로드가 진행 중이면 `Upload active; retry later`로 종료하므로 다음 실행 때 다시 확인합니다. 이 명령에는 DB 연결과 Google 인증 환경변수가 모두 필요합니다.

## Google Cloud Console 및 최초 1회 연결

1. Google Cloud 프로젝트 생성/선택 → Google Drive API 활성화.
2. Google Auth Platform에서 Branding/동의 화면을 설정하고 Audience는 개인 계정에 맞게 External. Testing 상태에서는 연결할 **운영자 계정**을 Test users에 등록합니다. 하객은 이 계정 연결 과정에 참여하지 않습니다.
3. Data Access 범위는 `https://www.googleapis.com/auth/drive.file`만 사용합니다. 앱이 생성한 파일과 폴더만 접근합니다. 기존 임의 폴더 ID를 넣으면 권한이 없어 실패할 수 있습니다. 이 도구가 생성한 전용 폴더를 지정 폴더로 사용하세요. 전체 Drive 접근 권한은 요청하지 않습니다.
4. Clients에서 **Web application** OAuth 클라이언트를 만들고 Authorized redirect URI에 정확히 `http://127.0.0.1:8765/callback`을 추가합니다. JavaScript origin은 필요 없습니다. 서버에서 토큰을 교환합니다.
5. 운영자 로컬 컴퓨터의 루트 `.env`에 Client ID/Secret을 넣고 `php guest-snap/bin/connect.php` 실행. 포트 8765가 비어 있어야 합니다. 같은 컴퓨터 브라우저에서 출력된 Google URL을 열어 사진 보관 계정으로 로그인/동의합니다. 원격 서버에서 실행할 경우 이 루프백 방식 대신 운영자 로컬에서 실행하세요.
6. 도구는 state/PKCE를 검증하고 서버에서 코드를 교환합니다. 갱신 토큰과 새 비공개 `Wedding Guest Snap` 폴더 ID는 **터미널에만** 출력합니다. 이를 배포 서버 환경변수에 안전하게 옮깁니다. 터미널 출력·스크린샷·로그를 공유하지 마세요. 비밀 값이나 토큰을 하객 HTML/JS/응답에 넣지 않습니다.
7. Testing 상태의 Drive 범위 갱신 토큰은 7일 만료됩니다. 장기 운영 전에 Publishing status를 In production으로 전환하고 Console에서 요구하는 게시 조건을 확인한 뒤 다시 연결하세요. 계정 권한 취소 등으로 토큰이 무효화되면 재연결이 필요합니다. 재연결 도구는 새 폴더를 생성하므로 기존 폴더를 계속 쓸 경우 기존 환경변수의 폴더 ID를 유지하세요(동일 OAuth 앱).
8. `php guest-snap/bin/manage.php init`으로 두 테이블을 생성합니다. 기존 테이블은 삭제하지 않습니다. 활성화/오픈 날짜를 조정한 스테이징에서 실제 사진 1장을 업로드하고 Drive 비공개 상태 및 DB `complete` 상태를 확인한 뒤 운영 활성화합니다.

토큰은 PHP 요청 중 서버에서 갱신하며 메모리에만 보관합니다. Drive가 401을 반환하면 1회 새 토큰으로 재요청합니다. API 오류 내용/토큰은 클라이언트로 전달하지 않습니다.

공식 문서: [OAuth 서버 인증](https://developers.google.com/identity/protocols/oauth2/web-server), [Testing 토큰 만료](https://developers.google.com/identity/protocols/oauth2), [Drive 파일 생성·사전 ID](https://developers.google.com/workspace/drive/api/guides/create-file), [업로드](https://developers.google.com/workspace/drive/api/guides/manage-uploads).

## DB 실패·중복·복구

`guest_snap`에 Drive 사전 발급 ID, 폴더 ID, 원본명, 저장명, MIME, 크기, SHA-256, 이름, 예약 시각을 **업로드 전에** `pending`으로 커밋합니다. 예약 실패 시 Drive 파일은 만들지 않습니다. 업로드와 확인이 끝나면 `complete` 및 UTC `uploaded_at`(서버 저장 확인 시각)을 기록합니다. MySQL 세션 타임존과 무관하게 완료 시각은 UTC입니다.

Drive 성공 이후 DB 갱신 실패/프로세스 종료 시에도 파일은 영속적인 `pending` 행에 연결되어 있습니다. 재시도는 동일 Drive ID와 해시·크기·폴더를 확인하고 기존 파일을 재사용합니다. Drive에만 남는 무기록 파일을 만들지 않기 위한 복구 저널 방식이며, 실패 시 무조건 파일 삭제를 시도하는 방식이 아닙니다. Drive 업로드 응답 유실도 동일하게 처리합니다.

운영 cron(예: 5분마다) 및 장애 복구:

```sh
php guest-snap/bin/manage.php reconcile
```

한 번에 최근 확인한 지 오래된 pending 100개를 순환 확인해 실제 Drive 파일이 있으면 DB 완료 처리를 복구합니다. 파일이 없으면 하객 재시도용 기록을 유지합니다. pending 행을 자동 삭제하지 마세요. 재시도 식별 정보를 지우면 중복 방지 근거도 사라집니다. 만료된 요청 제한 버킷도 이 명령으로 삭제합니다.

## 배포 점검

- PHP 8.0+, `curl`, `openssl`, `fileinfo`, `gd`(JPEG/PNG/WebP), `mbstring`, `pdo_mysql`, `session`. 로컬 확인 버전 PHP 8.4.3. Composer/외부 PHP 패키지 없음.
- `upload_max_filesize=15M`, `post_max_size=17M` 이상, `memory_limit=256M` 이상, `max_execution_time=300`, `max_input_time=180`, FPM `request_terminate_timeout=300s` 이상. 쓰기 가능한 PHP 업로드 임시 디렉터리와 충분한 디스크 공간. 요청은 한 장씩이므로 `max_file_uploads>=1`.
- Nginx `client_max_body_size 17m`, `fastcgi_read_timeout 300s`; Apache도 본문 제한 17MiB 이상 및 타임아웃 확인. PHP 도달 전 차단되는 HTML 413/502도 화면에서는 재시도 오류로 처리합니다.
- HTTPS 필수. 프록시 뒤에서는 PHP가 HTTPS 상태와 실제 클라이언트 IP를 **신뢰하는 프록시에서만** 전달받도록 설정. 그렇지 않으면 세션 쿠키 Secure 설정/IP 제한이 달라집니다. 세션 저장소는 영속적이며 여러 서버에서 공유해야 합니다.
- 루트 `.env`, `.git`, `storage`, `guest-snap/lib/`, `guest-snap/bin/` 웹 접근 차단. PHP 소스가 정적으로 내려가지 않는지 확인. `index.php`가 DirectoryIndex로 작동해야 합니다. 서브 경로는 기존 `assetUrl()`을 따릅니다.
- 업로드 페이지와 API는 캐시하지 않도록 CDN 예외. API에 CORS를 열지 마세요. 앱 제한 전에 본문이 PHP에 도달하므로 웹서버/WAF에 요청 빈도·연결 수·본문 크기 제한도 설정합니다. 익명 업로드에서는 여러 IP의 악용을 완전히 막을 수 없으므로 전체 일일 제한 및 `GUEST_SNAP_ENABLED=0` 중단 스위치를 운영합니다.
- DB init 계정에는 CREATE/ALTER 권한, 운영에는 SELECT/INSERT/UPDATE, 복구 명령에는 rate 테이블 DELETE 권한. MySQL `GET_LOCK` 지원 필수. 모든 웹 인스턴스는 같은 MySQL 서버를 사용해야 합니다.
- 서버에서 `oauth2.googleapis.com`, `www.googleapis.com`으로 HTTPS 아웃바운드 및 CA 인증서 설정. Drive 계정 저장공간/일일 요청 제한 점검. 운영 `display_errors=Off`, 보호된 서버 로그에 `log_errors=On`.


## 하객 화면과 업로드 API

- 화면: `/guest-snap/`. 선택 장수·전체 크기, 사진 미리보기·삭제, 전송/저장 확인 상태, 성공·실패 안내를 표시합니다. 완료 후 ‘다른 사진 더 올리기’로 새 배치를 시작합니다.
- API: `POST /guest-snap/api/upload.php`, `multipart/form-data`.
- 필드: `photo`(사진 1장), `name`(필수, 최대 20자), `phone`(필수, 숫자 8~15자리), `csrf`(화면에서 발급), `batch`와 `item`(각각 32자리 소문자 hex). 여러 사진은 화면에서 순차 호출합니다.
- 성공: HTTP 200, `{"ok":true,"code":"uploaded","message":"사진이 비공개로 저장되었습니다."}`. 인증 정보와 Drive 파일 ID는 응답에 포함하지 않습니다.
- 실패: `ok:false`, `code`, `message`. 403은 세션 만료/업로드 비활성, 405는 메서드 오류, 413은 요청/PHP 크기 제한, 422는 입력 검증 실패, 429는 요청 제한/처리 중, 503은 저장소 장애/설정 미완료입니다. 429에는 `Retry-After: 30`과 `retryAfter:30`도 반환합니다.
- 연결 오류 또는 403/429/503이면 나머지 전송을 멈추고 미전송 사진을 표시합니다. 같은 화면에서 다시 누르면 완료된 사진을 제외하고 기존 식별자로 재시도합니다.
- `GUEST_SNAP_ENABLED=1` 및 `WEDDING_DATE` 당일 00:00 도달 조건은 화면과 API 모두 적용됩니다. Google 인증 값·폴더 ID와 DB 초기화가 준비되어야 실제 저장됩니다.

## 이름·전화번호 저장

새 사진은 `guest_name`, `guest_phone`에 입력 정보를 저장하며, Drive 파일명에도 이름과 숫자로 정리한 전화번호를 붙입니다. 예: `홍길동_01012345678_20270925_120000_난수.jpg`. 재시도는 최초 이름·전화번호와 동일해야 기존 파일을 재사용합니다.

기존 DB에도 전화번호 열을 추가하려면 배포 시 `php guest-snap/bin/manage.php init`을 한 번 실행하세요. 기존 사진·파일명은 변경하지 않습니다.
