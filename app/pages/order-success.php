<?php
$orderId = (int) ($_SESSION['last_order_id'] ?? 0);
if ($orderId <= 0) {
    redirect('/');
}

$stmt = db()->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$orderId]);
$order = $stmt->fetch();
if (!$order) {
    redirect('/');
}

$itemStmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
$itemStmt->execute([$orderId]);
$orderItems = $itemStmt->fetchAll();
$pageTitle = 'Заказ ' . $order['order_number'] . ' оформлен — ВОЛЬТ';
$bodyClass = 'page-success';
include APP_ROOT . '/app/views/header.php';
?>
<section class="success section">
    <div class="container">
        <div class="success-card">
            <span class="success-card__icon">
                <svg viewBox="0 0 24 24" fill="none"><path d="m5 12.5 4.5 4.5L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <div class="eyebrow eyebrow--center"><span></span> Заказ принят</div>
            <h1>Спасибо за заказ!</h1>
            <p>Мы отправили подтверждение на <?= e($order['email']) ?>. Менеджер свяжется с вами для уточнения деталей.</p>
            <div class="success-card__number">
                <span>Номер заказа</span>
                <strong><?= e($order['order_number']) ?></strong>
            </div>

            <div class="success-details">
                <div><span>Получатель</span><strong><?= e($order['full_name']) ?></strong></div>
                <div><span>Телефон</span><strong><?= e($order['phone']) ?></strong></div>
                <div><span>Доставка</span><strong><?= e(delivery_label($order['delivery_method'])) ?></strong></div>
                <div><span>Оплата</span><strong><?= e(payment_label($order['payment_method'])) ?></strong></div>
                <div class="success-details__wide"><span>Адрес</span><strong><?= e($order['address']) ?></strong></div>
            </div>

            <div class="success-items">
                <?php foreach ($orderItems as $item): ?>
                    <div class="success-item">
                        <span><?= e($item['product_name']) ?> × <?= (int) $item['quantity'] ?></span>
                        <strong><?= format_price($item['line_total']) ?></strong>
                    </div>
                <?php endforeach; ?>
                <div class="success-item success-item--muted">
                    <span>Доставка</span>
                    <strong><?= (int) $order['delivery_cost'] === 0 ? 'Бесплатно' : format_price($order['delivery_cost']) ?></strong>
                </div>
                <div class="success-item success-item--total">
                    <span>Итого</span>
                    <strong><?= format_price($order['total']) ?></strong>
                </div>
            </div>

            <div class="success-card__actions">
                <?php if (is_logged_in()): ?>
                    <a class="button button--lg" href="/account.php">Перейти в личный кабинет</a>
                <?php endif; ?>
                <a class="button button--secondary button--lg" href="/catalog.php">Продолжить покупки</a>
            </div>
            <span class="success-card__note">Сохраните номер заказа — он понадобится для связи с поддержкой.</span>
        </div>
    </div>
</section>
<?php include APP_ROOT . '/app/views/footer.php'; ?>
