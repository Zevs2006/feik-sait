<?php
require_login();
$user = current_user();
$orderId = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$orderId, (int) $user['id']]);
$order = $stmt->fetch();

$pageTitle = $order ? 'Заказ ' . $order['order_number'] . ' — ВОЛЬТ' : 'Заказ не найден — ВОЛЬТ';
$bodyClass = 'page-order';
include APP_ROOT . '/app/views/header.php';

if (!$order) {
    ?>
    <div class="container section">
        <div class="empty-state empty-state--page">
            <span class="empty-state__icon"><svg viewBox="0 0 24 24" fill="none"><path d="M5 4h14v16l-3-2-2 2-2-2-2 2-2-2-3 2V4Z" stroke="currentColor" stroke-width="1.6"/><path d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></span>
            <h1>Заказ не найден</h1>
            <p>Проверьте номер заказа или вернитесь в личный кабинет.</p>
            <a class="button" href="/account.php">В личный кабинет</a>
        </div>
    </div>
    <?php
    include APP_ROOT . '/app/views/footer.php';
    return;
}

$itemStmt = db()->prepare(
    'SELECT oi.*, p.image, p.price AS current_price
     FROM order_items oi
     LEFT JOIN products p ON p.id = oi.product_id
     WHERE oi.order_id = ?
     ORDER BY oi.id'
);
$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll();
$displaySubtotal = 0;
foreach ($items as $item) {
    $displayPrice = $item['current_price'] !== null ? (int) $item['current_price'] : (int) $item['unit_price'];
    $displaySubtotal += $displayPrice * (int) $item['quantity'];
}

$statusSteps = ['new', 'processing', 'shipped', 'completed'];
$currentStep = array_search($order['status'], $statusSteps, true);
?>
<div class="page-head page-head--compact">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="/">Главная</a><span>/</span><a href="/account.php">Личный кабинет</a><span>/</span><span>Заказ <?= e($order['order_number']) ?></span>
        </nav>
        <div class="page-head__row">
            <div>
                <div class="eyebrow"><span></span> Заказ от <?= format_date($order['created_at']) ?></div>
                <h1>Заказ <?= e($order['order_number']) ?></h1>
            </div>
            <span class="<?= e(order_status_class($order['status'])) ?> status--lg"><?= e(order_status_label($order['status'])) ?></span>
        </div>
    </div>
</div>

<section class="order section section--flush">
    <div class="container">
        <?php if ($order['status'] !== 'cancelled'): ?>
            <div class="order-timeline">
                <?php foreach ($statusSteps as $index => $step): ?>
                    <div class="order-timeline__step <?= $index <= (int) $currentStep ? 'is-done' : '' ?> <?= $step === $order['status'] ? 'is-current' : '' ?>">
                        <span class="order-timeline__dot"><?= $index + 1 ?></span>
                        <div><strong><?= e(order_status_label($step)) ?></strong><small><?= $index === 0 ? format_date($order['created_at']) : '' ?></small></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="order-cancelled">Заказ отменён. Если вы хотите оформить новый, вернитесь в каталог.</div>
        <?php endif; ?>

        <div class="order-layout">
            <div class="order-main">
                <div class="order-card">
                    <div class="order-card__head"><h2>Состав заказа</h2><span><?= count($items) ?> <?= plural_ru(count($items), 'позиция', 'позиции', 'позиций') ?></span></div>
                    <div class="order-items">
                        <?php foreach ($items as $item): ?>
                            <?php
                            $displayPrice = $item['current_price'] !== null ? (int) $item['current_price'] : (int) $item['unit_price'];
                            $lineTotal = $displayPrice * (int) $item['quantity'];
                            ?>
                            <div class="order-item">
                                <div class="order-item__image">
                                    <img src="<?= e(product_image($item['image'] ?? 'placeholder.svg')) ?>" alt="<?= e($item['product_name']) ?>" width="120" height="90">
                                </div>
                                <div class="order-item__info">
                                    <strong><?= e($item['product_name']) ?></strong>
                                    <span><?= format_price($displayPrice) ?> × <?= (int) $item['quantity'] ?></span>
                                    <?php if ($item['product_id']): ?><a href="/product.php?id=<?= (int) $item['product_id'] ?>">Перейти к товару</a><?php endif; ?>
                                </div>
                                <strong class="order-item__total"><?= format_price($lineTotal) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="order-card">
                    <div class="order-card__head"><h2>Данные доставки</h2></div>
                    <div class="order-info-grid">
                        <div><span>Получатель</span><strong><?= e($order['full_name']) ?></strong></div>
                        <div><span>Телефон</span><strong><?= e($order['phone']) ?></strong></div>
                        <div><span>Email</span><strong><?= e($order['email']) ?></strong></div>
                        <div><span>Способ доставки</span><strong><?= e(delivery_label($order['delivery_method'])) ?></strong></div>
                        <div class="order-info-grid__wide"><span>Адрес</span><strong><?= e($order['address']) ?></strong></div>
                        <div><span>Оплата</span><strong><?= e(payment_label($order['payment_method'])) ?></strong></div>
                        <div class="order-info-grid__wide"><span>Комментарий</span><strong><?= $order['comment'] !== '' ? e($order['comment']) : 'Комментарий не указан' ?></strong></div>
                    </div>
                </div>
            </div>

            <aside class="order-summary">
                <h2>Сумма заказа</h2>
                <div class="order-summary__row"><span>Стоимость товаров</span><strong><?= format_price($displaySubtotal) ?></strong></div>
                <div class="order-summary__row"><span>Доставка</span><strong><?= (int) $order['delivery_cost'] === 0 ? 'Бесплатно' : format_price($order['delivery_cost']) ?></strong></div>
                <div class="order-summary__total"><span>Итого</span><strong><?= format_price($order['total']) ?></strong></div>
                <div class="order-summary__note">Итог зафиксирован в момент оформления заказа.</div>
                <a class="button button--secondary button--full" href="/account.php">Вернуться в кабинет</a>
            </aside>
        </div>
    </div>
</section>
<?php include APP_ROOT . '/app/views/footer.php'; ?>
