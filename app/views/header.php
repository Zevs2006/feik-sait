<?php
$pageTitle = $pageTitle ?? (SITE_NAME . ' — электроника и компьютерная техника');
$metaDescription = $metaDescription ?? 'Ноутбуки, смартфоны, аудио и аксессуары. Официальная гарантия, быстрая доставка и удобная оплата.';
$bodyClass = $bodyClass ?? '';
$user = current_user();
$cartQty = cart_count();
$flashes = get_flashes();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($metaDescription) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" href="<?= asset('images/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=3">
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main">Перейти к содержимому</a>
<div class="topbar">
    <div class="container topbar__inner">
        <div class="topbar__note">
            <span class="topbar__dot"></span>
            Официальная гарантия и доставка по всей России
        </div>
        <div class="topbar__links">
            <a href="/info.php?page=delivery">Доставка и оплата</a>
            <a href="/info.php?page=warranty">Гарантия</a>
            <a href="tel:+73835550880"><?= e(SITE_PHONE) ?></a>
        </div>
    </div>
</div>
<header class="site-header">
    <div class="container header__inner">
        <a class="brand" href="/" aria-label="<?= e(SITE_NAME) ?> — на главную">
            <span class="brand__mark" aria-hidden="true">
                <svg viewBox="0 0 28 28" fill="none"><path d="M15.8 2 6 16h6.6L11.2 26 22 11.4h-6.7L15.8 2Z" fill="currentColor"/></svg>
            </span>
            <span class="brand__copy">
                <strong><?= e(SITE_NAME) ?></strong>
                <small><?= e(SITE_TAGLINE) ?></small>
            </span>
        </a>

        <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="mobile-nav">
            <span class="sr-only">Открыть меню</span>
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav" aria-label="Основная навигация">
            <a class="main-nav__link<?= nav_active('catalog.php') ?>" href="/catalog.php">Каталог</a>
            <a class="main-nav__link" href="/catalog.php?category=laptops">Ноутбуки</a>
            <a class="main-nav__link" href="/catalog.php?category=smartphones">Смартфоны</a>
            <a class="main-nav__link" href="/catalog.php?category=audio">Аудио</a>
            <a class="main-nav__link" href="/catalog.php?category=gaming">Гейминг</a>
        </nav>

        <form class="header-search" action="/catalog.php" method="get" role="search">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="m16.5 16.5 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <input type="search" name="q" placeholder="Найти технику" aria-label="Поиск по каталогу" value="<?= e($_GET['q'] ?? '') ?>">
            <button type="submit" class="sr-only">Найти</button>
        </form>

        <div class="header-actions">
            <a class="header-action header-action--account" href="<?= $user ? '/account.php' : '/login.php' ?>">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.7"/><path d="M5 20a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                <span><?= $user ? e(explode(' ', $user['full_name'])[0]) : 'Войти' ?></span>
            </a>
            <a class="header-action header-action--cart" href="/cart.php" aria-label="Корзина, товаров: <?= $cartQty ?>">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3.5 4.5h2l2 11h10.2l2-8H6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="19" r="1.4" fill="currentColor"/><circle cx="17.5" cy="19" r="1.4" fill="currentColor"/></svg>
                <span>Корзина</span>
                <?php if ($cartQty > 0): ?><b class="cart-badge"><?= $cartQty ?></b><?php endif; ?>
            </a>
        </div>
    </div>

    <div class="mobile-nav" id="mobile-nav" data-mobile-nav>
        <div class="container">
            <nav aria-label="Мобильная навигация">
                <a href="/catalog.php">Каталог</a>
                <a href="/catalog.php?category=laptops">Ноутбуки</a>
                <a href="/catalog.php?category=smartphones">Смартфоны</a>
                <a href="/catalog.php?category=audio">Аудио</a>
                <a href="/catalog.php?category=gaming">Гейминг</a>
                <a href="/catalog.php?category=accessories">Аксессуары</a>
                <a href="/info.php?page=delivery">Доставка и оплата</a>
            </nav>
        </div>
    </div>
</header>

<?php if ($flashes): ?>
    <div class="flash-stack" aria-live="polite">
        <?php foreach ($flashes as $flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>">
                <span><?= e($flash['message']) ?></span>
                <button type="button" class="flash__close" data-flash-close aria-label="Закрыть сообщение">×</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main id="main">
