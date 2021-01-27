#### config.php 생성
```
<?php

$client_id = '';
$client_secret = '';
$redirect_uri = 'https://example.com/oauth.php';

define('TOKEN_REFRESH_LIMIT', 50); // 일
define('MEDIA_REFRESH_LIMIT', 10); // 분

define('DATA_PATH', __DIR__ . '/data');

define('TOKEN_FILE', 'token.php');
define('MEDIA_FILE', 'media.php');
```

`token.php` 파일 생성을 위해 쓰기 권한이 있는지 체크

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