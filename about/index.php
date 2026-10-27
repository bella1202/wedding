<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/env.php';
require dirname(__DIR__) . '/includes/functions.php';
$weddingData = require dirname(__DIR__) . '/includes/data.php';

$g = $weddingData['groom'];
$b = $weddingData['bride'];
$homeUrl = assetUrl('/');
$pageTitle = '웨딩무드 · About';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f7f5f2">
    <meta name="robots" content="noindex,nofollow">
    <meta name="description" content="웨딩무드는 신랑·신부가 직접 만든 모바일 청첩장입니다.">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gowun+Dodum&family=Great+Vibes&family=Noto+Sans+KR:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(assetUrl('/assets/css/common.css')) ?>">
    <link rel="stylesheet" href="<?= e(assetUrl('/assets/css/about.css')) ?>">
</head>
<body class="filmBody aboutBody">
<div class="filmGrain" aria-hidden="true"></div>
<main class="aboutPage">
    <p class="aboutPage__brand">Wedding Mood</p>
    <h1 class="aboutPage__brandKo">웨딩무드</h1>

    <figure class="aboutPage__art">
        <img
            src="<?= e(assetUrl('/assets/images/about-couple.png')) ?>"
            alt=""
            width="1024"
            height="1024"
            decoding="async"
        >
    </figure>

    <p class="aboutPage__lead">
        웨딩무드는 <?= e($g['name']) ?>과 <?= e($b['name']) ?>이<br>
        직접 만든 모바일 청첩장입니다.
    </p>
    <p class="aboutPage__thanks">
        귀한 마음으로 축하해 주시는<br>
        모든 분들께 진심으로 감사드립니다.
    </p>

    <a class="aboutPage__cta touchBtn" href="<?= e($homeUrl) ?>">청첩장 보러 가기</a>
</main>
</body>
</html>
