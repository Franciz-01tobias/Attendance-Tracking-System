<?php
/** @var string $content */
/** @var string|null $title */

use App\Core\Auth;

$user = Auth::user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title ?? config('app.name')) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>
<header class="topbar">
    <div class="brand-wrap">
        <div class="brand-dot"></div>
        <div>
            <h1><?= h((string) config('app.name')) ?></h1>
            <p>Professional Attendance Workflow</p>
        </div>
    </div>
    <?php if ($user): ?>
        <nav class="nav-actions">
            <span class="user-chip"><?= h(strtoupper((string) $user['role'])) ?>: <?= h((string) $user['name']) ?></span>
            <a class="btn subtle" href="/dashboard/<?= h((string) $user['role']) ?>">Dashboard</a>
            <form method="post" action="/auth/logout">
                <input type="hidden" name="_csrf" value="<?= h(App\Core\Csrf::token()) ?>">
                <button class="btn danger" type="submit">Logout</button>
            </form>
        </nav>
    <?php endif; ?>
</header>

<main class="container">
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert success"><?= h((string) $_SESSION['flash_success']) ?></div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert error"><?= h((string) $_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <?= $content ?>
</main>

<script src="/assets/js/app.js"></script>
</body>
</html>
