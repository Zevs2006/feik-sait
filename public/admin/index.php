<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_admin();

$pdo = db();
$revenue = (float) $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'cancelled'")->fetchColumn();
$ordersCount = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$customersCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$lowStockCount = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1 AND stock_quantity <= 3')->fetchColumn();
$newOrdersCount = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'new'")->fetchColumn();

$recentOrders = $pdo->query(
    'SELECT o.*, (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi WHERE oi.order_id = o.id) AS items_count
     FROM orders o
     ORDER BY o.created_at DESC
     LIMIT 5'
)->fetchAll();
$lowStock = $pdo->query(
    'SELECT p.*, c.name AS category_name
     FROM products p JOIN categories c ON c.id = p.category_id
     WHERE p.is_active = 1 AND p.stock_quantity <= 3
     ORDER BY p.stock_quantity ASC, p.name ASC
     LIMIT 5'
)->fetchAll();

$monthlyRows = $pdo->query(
    "SELECT strftime('%Y-%m', created_at) AS month, COALESCE(SUM(total), 0) AS revenue
     FROM orders
     WHERE status <> 'cancelled' AND created_at >= date('now', '-5 months', 'start of month')
     GROUP BY month"
)->fetchAll();
$monthlyMap = [];
foreach ($monthlyRows as $row) {
    $monthlyMap[$row['month']] = (int) $row['revenue'];
}
$months = [];
$maxRevenue = 1;
for ($i = 5; $i >= 0; $i--) {
    $key = date('Y-m', strtotime('-' . $i . ' months'));
    $months[] = [
        'label' => ['янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'][(int) date('n', strtotime($key . '-01')) - 1],
        'value' => $monthlyMap[$key] ?? 0,
    ];
    $maxRevenue = max($maxRevenue, $monthlyMap[$key] ?? 0);
}

$adminPageTitle = 'Обзор магазина';
$adminPageEyebrow = 'Панель управления';
include APP_ROOT . '/app/views/admin-header.php';
?>
<div class="admin-intro">
    <div>
        <h2>Добрый день, <?= e(explode(' ', current_user()['full_name'])[0]) ?>!</h2>
        <p>Вот что происходит в магазине сегодня.</p>
    </div>
    <a class="admin-button admin-button--primary" href="/admin/product-edit.php">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        Добавить товар
    </a>
</div>

<div class="stat-grid">
    <article class="stat-card">
        <div class="stat-card__top"><span>Выручка</span><b class="stat-card__icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 19V5M4 19h16M8 15l3-4 3 2 4-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></b></div>
        <strong><?= format_price($revenue) ?></strong>
        <small>без учёта отменённых заказов</small>
    </article>
    <article class="stat-card">
        <div class="stat-card__top"><span>Заказы</span><b class="stat-card__icon"><svg viewBox="0 0 24 24" fill="none"><path d="M5 4h14v16l-3-2-2 2-2-2-2 2-2-2-3 2V4Z" stroke="currentColor" stroke-width="1.7"/></svg></b></div>
        <strong><?= $ordersCount ?></strong>
        <small><?= $newOrdersCount ?> новых требуют внимания</small>
    </article>
    <article class="stat-card">
        <div class="stat-card__top"><span>Клиенты</span><b class="stat-card__icon"><svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3.2" stroke="currentColor" stroke-width="1.7"/><path d="M3 20a6 6 0 0 1 12 0M16 6.5a3 3 0 0 1 0 5.8M18 15.5a5 5 0 0 1 3 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></b></div>
        <strong><?= $customersCount ?></strong>
        <small>зарегистрированных покупателей</small>
    </article>
    <article class="stat-card stat-card--warning">
        <div class="stat-card__top"><span>Мало на складе</span><b class="stat-card__icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 4 3 20h18L12 4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 9v5M12 17.5v.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></b></div>
        <strong><?= $lowStockCount ?></strong>
        <small>товаров с остатком 3 шт. и меньше</small>
    </article>
</div>

<div class="admin-dashboard-grid">
    <section class="admin-panel admin-panel--chart">
        <div class="admin-panel__head">
            <div><h2>Продажи</h2><p>Выручка за последние 6 месяцев</p></div>
            <span class="admin-panel__badge"><?= format_price($revenue) ?></span>
        </div>
        <div class="chart">
            <?php foreach ($months as $month): ?>
                <div class="chart__column">
                    <span class="chart__value"><?= $month['value'] > 0 ? number_format($month['value'] / 1000, 0, ',', ' ') . 'к' : '—' ?></span>
                    <div class="chart__bar"><span style="height: <?= max(3, (int) round($month['value'] / $maxRevenue * 100)) ?>%"></span></div>
                    <span class="chart__label"><?= e($month['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__head">
            <div><h2>Последние заказы</h2><p>Свежие покупки</p></div>
            <a href="/admin/orders.php">Все заказы</a>
        </div>
        <div class="admin-order-list">
            <?php foreach ($recentOrders as $order): ?>
                <a href="/admin/order-view.php?id=<?= (int) $order['id'] ?>" class="admin-order-item">
                    <span class="admin-order-item__avatar"><?= e(mb_strtoupper(mb_substr($order['full_name'], 0, 1))) ?></span>
                    <span class="admin-order-item__info">
                        <strong><?= e($order['order_number']) ?></strong>
                        <small><?= e($order['full_name']) ?> · <?= format_date($order['created_at']) ?></small>
                    </span>
                    <span class="admin-order-item__total"><?= format_price($order['total']) ?></span>
                    <span class="<?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<div class="admin-dashboard-grid admin-dashboard-grid--even">
    <section class="admin-panel">
        <div class="admin-panel__head">
            <div><h2>Низкий остаток</h2><p>Стоит пополнить запас</p></div>
            <a href="/admin/products.php">Товары</a>
        </div>
        <div class="admin-stock-list">
            <?php if (!$lowStock): ?>
                <p class="admin-muted">Все товары в достатке.</p>
            <?php endif; ?>
            <?php foreach ($lowStock as $product): ?>
                <div class="admin-stock-item">
                    <img src="<?= e(product_image($product['image'])) ?>" alt="" width="52" height="40">
                    <div><strong><?= e($product['name']) ?></strong><span><?= e($product['category_name']) ?></span></div>
                    <b class="<?= (int) $product['stock_quantity'] === 0 ? 'text-danger' : 'text-warning' ?>"><?= (int) $product['stock_quantity'] ?> шт.</b>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="admin-panel admin-panel--quick">
        <div class="admin-panel__head"><div><h2>Быстрые действия</h2><p>Частые задачи</p></div></div>
        <div class="quick-grid">
            <a href="/admin/product-edit.php"><span><svg viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span><strong>Новый товар</strong><small>Добавить в каталог</small></a>
            <a href="/admin/orders.php"><span><svg viewBox="0 0 24 24" fill="none"><path d="M5 4h14v16l-3-2-2 2-2-2-2 2-2-2-3 2V4Z" stroke="currentColor" stroke-width="1.7"/></svg></span><strong>Заказы</strong><small>Обработать новые</small></a>
            <a href="/admin/users.php"><span><svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3.2" stroke="currentColor" stroke-width="1.7"/><path d="M3 20a6 6 0 0 1 12 0" stroke="currentColor" stroke-width="1.7"/></svg></span><strong>Клиенты</strong><small>Список покупателей</small></a>
            <a href="/" target="_blank" rel="noopener"><span><svg viewBox="0 0 24 24" fill="none"><path d="M14 4h6v6M20 4l-9 9M18 14v5H5V6h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span><strong>Витрина</strong><small>Открыть сайт</small></a>
        </div>
    </section>
</div>
<?php include APP_ROOT . '/app/views/admin-footer.php'; ?>
