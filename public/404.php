<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
http_response_code(404);
$pageTitle = 'Страница не найдена — ВОЛЬТ';
$bodyClass = 'page-error';
include APP_ROOT . '/app/views/header.php';
?>
<section class="error-page section">
    <div class="container">
        <div class="error-card">
            <span class="error-card__code">404</span>
            <span class="error-card__icon"><svg viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.7"/><path d="m16.5 16.5 4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
            <h1>Страница не найдена</h1>
            <p>Возможно, ссылка устарела или страница была перемещена.</p>
            <div class="error-card__actions">
                <a class="button" href="/">На главную</a>
                <a class="button button--secondary" href="/catalog.php">В каталог</a>
            </div>
        </div>
    </div>
</section>
<?php include APP_ROOT . '/app/views/footer.php'; ?>
