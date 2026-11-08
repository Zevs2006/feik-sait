<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_admin();

$statusFilter = (string) ($_GET['status'] ?? '');
if (!array_key_exists($statusFilter, ORDER_STATUSES)) {
    $statusFilter = '';
}
$where = '';
$params = [];
if ($statusFilter !== '') {
    $where = 'WHERE o.status = ?';
    $params[] = $statusFilter;
}
$stmt = db()->prepare(
    "SELECT o.*, (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi WHERE oi.order_id = o.id) AS items_count
     FROM orders o
     {$where}
     ORDER BY o.created_at DESC, o.id DESC"
);
$stmt->execute($params);
$orders = $stmt->fetchAll();
$counts = [];
foreach (db()->query('SELECT status, COUNT(*) AS total FROM orders GROUP BY status')->fetchAll() as $row) {
    $counts[$row['status']] = (int) $row['total'];
}
$totalOrders = array_sum($counts);

$adminPageTitle = 'Заказы';
$adminPageEyebrow = 'Продажи';
include APP_ROOT . '/app/views/admin-header.php';
?>
<div class="admin-toolbar admin-toolbar--tabs">
    <div class="admin-tabs">
        <a class="<?= $statusFilter === '' ? 'is-active' : '' ?>" href="/admin/orders.php">Все <b><?= $totalOrders ?></b></a>
        <?php foreach (ORDER_STATUSES as $status => $label): ?>
            <?php if (!empty($counts[$status])): ?>
                <a class="<?= $statusFilter === $status ? 'is-active' : '' ?>" href="/admin/orders.php?status=<?= e($status) ?>"><?= e($label) ?> <b><?= $counts[$status] ?></b></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<section class="admin-panel">
    <div class="admin-table-wrap">
        <table class="admin-table admin-table--orders">
            <thead>
                <tr>
                    <th>Заказ</th>
                    <th>Покупатель</th>
                    <th>Дата</th>
                    <th>Состав</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th class="admin-table__actions">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><a class="admin-order-link" href="/admin/order-view.php?id=<?= (int) $order['id'] ?>"><?= e($order['order_number']) ?></a></td>
                        <td>
                            <div class="admin-customer"><strong><?= e($order['full_name']) ?></strong><span><?= e($order['email']) ?></span></div>
                        </td>
                        <td><?= format_date($order['created_at'], true) ?></td>
                        <td><?= (int) $order['items_count'] ?> <?= plural_ru((int) $order['items_count'], 'товар', 'товара', 'товаров') ?></td>
                        <td><strong><?= format_price($order['total']) ?></strong></td>
                        <td><span class="<?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span></td>
                        <td class="admin-table__actions">
                            <a class="admin-icon-button" href="/admin/order-view.php?id=<?= (int) $order['id'] ?>" aria-label="Открыть заказ" title="Открыть">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M2.5 12S6 6 12 6s9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.6"/></svg>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$orders): ?>
        <div class="admin-empty"><p>Заказов с таким статусом пока нет.</p><a class="admin-button" href="/admin/orders.php">Показать все</a></div>
    <?php endif; ?>
</section>
<?php include APP_ROOT . '/app/views/admin-footer.php'; ?>
