<?php
$adminUser = current_user();
$adminPageTitle = $adminPageTitle ?? 'Панель управления';
$adminPageEyebrow = $adminPageEyebrow ?? 'Администрирование';
$adminFlashes = get_flashes();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($adminPageTitle) ?> — <?= e(SITE_NAME) ?> Admin</title>
    <link rel="icon" href="<?= asset('images/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=3">
</head>
<body class="admin-body">
<a class="skip-link" href="#admin-content">Перейти к содержимому</a>
<div class="admin-shell">
    <aside class="admin-sidebar" data-admin-sidebar>
        <a class="admin-brand" href="/admin/index.php">
            <span class="admin-brand__mark">
                <svg viewBox="0 0 28 28" fill="none"><path d="M15.8 2 6 16h6.6L11.2 26 22 11.4h-6.7L15.8 2Z" fill="currentColor"/></svg>
            </span>
            <span><strong><?= e(SITE_NAME) ?></strong><small>admin panel</small></span>
        </a>

        <nav class="admin-nav" aria-label="Административная навигация">
            <span class="admin-nav__label">Магазин</span>
            <a class="<?= admin_nav_active('index.php') ?>" href="/admin/index.php">
                <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/></svg>
                Обзор
            </a>
            <a class="<?= admin_nav_active('products.php') || admin_nav_active('product-edit.php') ? 'is-active' : '' ?>" href="/admin/products.php">
                <svg viewBox="0 0 24 24" fill="none"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z" stroke="currentColor" stroke-width="1.6"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9" stroke="currentColor" stroke-width="1.6"/></svg>
                Товары
            </a>
            <a class="<?= admin_nav_active('orders.php') || admin_nav_active('order-view.php') ? 'is-active' : '' ?>" href="/admin/orders.php">
                <svg viewBox="0 0 24 24" fill="none"><path d="M5 4h14v16l-3-2-2 2-2-2-2 2-2-2-3 2V4Z" stroke="currentColor" stroke-width="1.6"/><path d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                Заказы
            </a>
            <a class="<?= admin_nav_active('users.php') ?>" href="/admin/users.php">
                <svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3.2" stroke="currentColor" stroke-width="1.6"/><path d="M3 20a6 6 0 0 1 12 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M16 6.5a3 3 0 0 1 0 5.8M18 15.5a5 5 0 0 1 3 4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                Пользователи
            </a>
            <span class="admin-nav__label">Переходы</span>
            <a href="/" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" fill="none"><path d="M14 4h6v6M20 4l-9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M18 14v5H5V6h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                Открыть сайт
            </a>
        </nav>

        <div class="admin-sidebar__user">
            <span class="admin-avatar"><?= e(mb_strtoupper(mb_substr($adminUser['full_name'], 0, 1))) ?></span>
            <div><strong><?= e($adminUser['full_name']) ?></strong><span><?= e($adminUser['email']) ?></span></div>
            <form action="/logout.php" method="post">
                <?= csrf_field() ?>
                <button type="submit" aria-label="Выйти">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M14 5H6v14h8M17 8l4 4-4 4M21 12H10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </form>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="admin-menu-toggle" type="button" data-admin-toggle aria-expanded="false" aria-controls="admin-content">
                <span></span><span></span><span></span>
                <span class="sr-only">Меню</span>
            </button>
            <div>
                <span><?= e($adminPageEyebrow) ?></span>
                <h1><?= e($adminPageTitle) ?></h1>
            </div>
            <div class="admin-topbar__actions">
                <span class="admin-topbar__date"><?= date('d.m.Y') ?></span>
                <a class="admin-topbar__site" href="/" target="_blank" rel="noopener">На сайт</a>
            </div>
        </header>

        <?php if ($adminFlashes): ?>
            <div class="flash-stack flash-stack--admin" aria-live="polite">
                <?php foreach ($adminFlashes as $flash): ?>
                    <div class="flash flash--<?= e($flash['type']) ?>">
                        <span><?= e($flash['message']) ?></span>
                        <button type="button" class="flash__close" data-flash-close aria-label="Закрыть сообщение">×</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="admin-content" id="admin-content">
