<?php
$footerCategories = $footerCategories ?? categories_with_counts();
?>
</main>
<footer class="site-footer">
    <div class="container footer__grid">
        <div class="footer__brand">
            <a class="brand brand--footer" href="/">
                <span class="brand__mark" aria-hidden="true">
                    <svg viewBox="0 0 28 28" fill="none"><path d="M15.8 2 6 16h6.6L11.2 26 22 11.4h-6.7L15.8 2Z" fill="currentColor"/></svg>
                </span>
                <span class="brand__copy">
                    <strong><?= e(SITE_NAME) ?></strong>
                    <small><?= e(SITE_TAGLINE) ?></small>
                </span>
            </a>
            <p>Техника, которая помогает работать, учиться и отдыхать. Подбираем устройства с гарантией и понятной поддержкой.</p>
            <a class="footer__phone" href="tel:+73835550880"><?= e(SITE_PHONE) ?></a>
            <span class="footer__hours">Ежедневно с 09:00 до 21:00</span>
        </div>
        <div class="footer__column">
            <h2>Каталог</h2>
            <?php foreach ($footerCategories as $category): ?>
                <a href="/catalog.php?category=<?= e($category['slug']) ?>"><?= e($category['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <div class="footer__column">
            <h2>Покупателям</h2>
            <a href="/account.php">Личный кабинет</a>
            <a href="/cart.php">Корзина</a>
            <a href="/info.php?page=delivery">Доставка и оплата</a>
            <a href="/info.php?page=warranty">Гарантия и возврат</a>
            <a href="/info.php?page=contacts">Контакты</a>
        </div>
        <div class="footer__column footer__column--contacts">
            <h2>Магазин</h2>
            <p>г. Новосибирск,<br>Красный проспект, 25</p>
            <a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a>
            <div class="payments" aria-label="Способы оплаты">
                <span>МИР</span><span>VISA</span><span>Mastercard</span>
            </div>
        </div>
    </div>
    <div class="container footer__bottom">
        <span>© <?= date('Y') ?> <?= e(SITE_NAME) ?>. Все права защищены.</span>
        <span>Информация на сайте не является публичной офертой.</span>
    </div>
</footer>
<script src="<?= asset('js/app.js') ?>?v=2"></script>
</body>
</html>
