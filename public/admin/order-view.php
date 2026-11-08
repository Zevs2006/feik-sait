<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_admin();

$orderId = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$orderId]);
$order = $stmt->fetch();
if (!$order) {
    flash('error', 'Заказ не найден.');
    redirect('/admin/orders.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $newStatus = (string) ($_POST['status'] ?? '');
    if (!array_key_exists($newStatus, ORDER_STATUSES)) {
        flash('error', 'Некорректный статус заказа.');
        redirect('/admin/order-view.php?id=' . $orderId);
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $itemStmt = $pdo->prepare('SELECT product_id, product_name, quantity FROM order_items WHERE order_id = ?');
        $itemStmt->execute([$orderId]);
        $items = $itemStmt->fetchAll();

        if ($order['status'] !== 'cancelled' && $newStatus === 'cancelled') {
            $restore = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?');
            foreach ($items as $item) {
                if ($item['product_id'] !== null) {
                    $restore->execute([(int) $item['quantity'], (int) $item['product_id']]);
                }
            }
        } elseif ($order['status'] === 'cancelled' && $newStatus !== 'cancelled') {
            $check = $pdo->prepare('SELECT stock_quantity FROM products WHERE id = ?');
            $deduct = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?');
            foreach ($items as $item) {
                if ($item['product_id'] === null) {
                    continue;
                }
                $check->execute([(int) $item['product_id']]);
                $stock = $check->fetchColumn();
                if ($stock === false || (int) $stock < (int) $item['quantity']) {
                    throw new RuntimeException('Недостаточно товара «' . $item['product_name'] . '» для возврата заказа в работу.');
                }
                $deduct->execute([(int) $item['quantity'], (int) $item['product_id'], (int) $item['quantity']]);
            }
        }

        $update = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $update->execute([$newStatus, $orderId]);
        $pdo->commit();
        flash('success', 'Статус заказа обновлён: ' . order_status_label($newStatus) . '.');
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash('error', $e->getMessage());
    }
    redirect('/admin/order-view.php?id=' . $orderId);
}

$itemStmt = db()->prepare(
    'SELECT oi.*, p.image
     FROM order_items oi
     LEFT JOIN products p ON p.id = oi.product_id
     WHERE oi.order_id = ?
     ORDER BY oi.id'
);
$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll();

$adminPageTitle = 'Заказ ' . $order['order_number'];
$adminPageEyebrow = 'Продажи';
include APP_ROOT . '/app/views/admin-header.php';
?>
<div class="admin-edit-head">
    <a class="admin-back" href="/admin/orders.php">
        <svg viewBox="0 0 24 24" fill="none"><path d="M19 12H5M11 6l-6 6 6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Все заказы
    </a>
    <span class="<?= e(order_status_class($order['status'])) ?> status--lg"><?= e(order_status_label($order['status'])) ?></span>
</div>

<div class="admin-order-layout">
    <div class="admin-order-main">
        <section class="admin-panel">
            <div class="admin-panel__head">
                <div><h2>Состав заказа</h2><p><?= format_date($order['created_at'], true) ?> · <?= count($items) ?> <?= plural_ru(count($items), 'позиция', 'позиции', 'позиций') ?></p></div>
                <span class="admin-panel__badge"><?= format_price($order['total']) ?></span>
            </div>
            <div class="admin-order-items">
                <?php foreach ($items as $item): ?>
                    <div class="admin-order-product">
                        <img src="<?= e(product_image($item['image'] ?? 'placeholder.svg')) ?>" alt="" width="72" height="54">
                        <div>
                            <strong><?= e($item['product_name']) ?></strong>
                            <span><?= format_price($item['unit_price']) ?> × <?= (int) $item['quantity'] ?></span>
                        </div>
                        <b><?= format_price($item['line_total']) ?></b>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="admin-order-totals">
                <div><span>Товары</span><strong><?= format_price($order['subtotal']) ?></strong></div>
                <div><span>Доставка</span><strong><?= (int) $order['delivery_cost'] === 0 ? 'Бесплатно' : format_price($order['delivery_cost']) ?></strong></div>
                <div class="admin-order-totals__total"><span>Итого</span><strong><?= format_price($order['total']) ?></strong></div>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel__head"><div><h2>Данные покупателя</h2><p>Контактная информация</p></div></div>
            <div class="admin-detail-grid">
                <div><span>Получатель</span><strong><?= e($order['full_name']) ?></strong></div>
                <div><span>Телефон</span><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $order['phone'])) ?>"><?= e($order['phone']) ?></a></div>
                <div><span>Email</span><a href="mailto:<?= e($order['email']) ?>"><?= e($order['email']) ?></a></div>
                <div><span>Способ доставки</span><strong><?= e(delivery_label($order['delivery_method'])) ?></strong></div>
                <div class="admin-detail-grid__wide"><span>Адрес</span><strong><?= e($order['address']) ?></strong></div>
                <div><span>Оплата</span><strong><?= e(payment_label($order['payment_method'])) ?></strong></div>
                <div class="admin-detail-grid__wide"><span>Комментарий</span><strong><?= $order['comment'] !== '' ? e($order['comment']) : 'Не указан' ?></strong></div>
            </div>
        </section>
    </div>

    <aside class="admin-order-aside">
        <section class="admin-panel">
            <div class="admin-panel__head"><div><h2>Статус заказа</h2><p>Обновите этап обработки</p></div></div>
            <form action="/admin/order-view.php?id=<?= (int) $order['id'] ?>" method="post" class="admin-status-form">
                <?= csrf_field() ?>
                <label class="field">
                    <span>Текущий статус</span>
                    <select name="status">
                        <?php foreach (ORDER_STATUSES as $status => $label): ?>
                            <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="admin-button admin-button--primary admin-button--full" type="submit">Применить статус</button>
            </form>
        </section>

        <section class="admin-panel">
            <div class="admin-panel__head"><div><h2>Сводка</h2></div></div>
            <div class="admin-summary-list">
                <div><span>Номер</span><strong><?= e($order['order_number']) ?></strong></div>
                <div><span>Создан</span><strong><?= format_date($order['created_at'], true) ?></strong></div>
                <div><span>Позиций</span><strong><?= count($items) ?></strong></div>
                <div><span>Сумма</span><strong><?= format_price($order['total']) ?></strong></div>
            </div>
        </section>
    </aside>
</div>
<?php include APP_ROOT . '/app/views/admin-footer.php'; ?>
