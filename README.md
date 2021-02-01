#### config.php 생성
```
<?php
$client_id = '';
$client_secret = '';
$redirect_uri = 'https://example.com/oauth.php';

define('TOKEN_REFRESH_LIMIT', 50); // 일
define('MEDIA_REFRESH_LIMIT', 1); // 시간

define('DATA_PATH', __DIR__ . '/data');

define('TOKEN_FILE', 'token.php');
define('MEDIA_FILE', 'media.php');
```

`TOKEN_FILE` 등의 파일 생성을 위해 `DATA_PATH` 디렉토리에 쓰기 권한이 있는지 체크

##### 파일구조
```
.
├── INSTAGRAM.php
├── README.md
├── common.php
├── config.php
├── data
│   ├── media.php
│   └── token.php
├── media.php
└── oauth.php
```