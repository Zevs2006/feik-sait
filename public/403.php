<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
http_response_code(403);
$pageTitle = 'Доступ запрещён — ВОЛЬТ';
$bodyClass = 'page-error';
include APP_ROOT . '/app/views/header.php';
?>
<section class="error-page section">
    <div class="container">
        <div class="error-card">
            <span class="error-card__code">403</span>
            <span class="error-card__icon"><svg viewBox="0 0 24 24" fill="none"><rect x="4" y="10" width="16" height="11" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
            <h1>Доступ запрещён</h1>
            <p>У вашего аккаунта нет прав для просмотра этой страницы.</p>
            <div class="error-card__actions">
                <a class="button" href="/">На главную</a>
                <a class="button button--secondary" href="/account.php">В личный кабинет</a>
            </div>
        </div>
    </div>
</section>
<?php include APP_ROOT . '/app/views/footer.php'; ?>
