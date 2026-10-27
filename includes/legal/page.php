<?php
declare(strict_types=1);

/**
 * Standalone legal document page.
 *
 * @var string $legalTitle
 * @var string $legalBody  Absolute path to body partial
 */

require dirname(__DIR__) . '/env.php';
require dirname(__DIR__) . '/functions.php';

$homeUrl = assetUrl('/');
$pageTitle = '웨딩무드 · ' . $legalTitle;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f7f5f2">
    <meta name="robots" content="noindex,nofollow">
    <meta name="description" content="<?= e('웨딩무드 ' . $legalTitle) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gowun+Dodum&family=Noto+Sans+KR:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(assetUrl('/assets/css/common.css')) ?>">
    <link rel="stylesheet" href="<?= e(assetUrl('/assets/css/legal.css')) ?>">
</head>
<body class="filmBody legalBody">
<main class="legalPage">
    <header class="legalPage__header">
        <p class="legalPage__brand">웨딩무드</p>
        <h1 class="legalPage__title"><?= e($legalTitle) ?></h1>
    </header>
    <article class="legalPage__body">
        <?php require $legalBody; ?>
    </article>
    <a class="legalPage__back touchBtn" href="<?= e($homeUrl) ?>">청첩장으로 돌아가기</a>
</main>
</body>
</html>
