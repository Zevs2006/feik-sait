<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'delete') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $stmt = db()->prepare('SELECT name FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        $name = $stmt->fetchColumn();
        if ($name !== false) {
            $stmt = db()->prepare('DELETE FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            flash('success', 'Товар «' . $name . '» удалён.');
        } else {
            flash('error', 'Товар не найден.');
        }
    }
    redirect('/admin/products.php');
}

$q = trim((string) ($_GET['q'] ?? ''));
$sql = 'SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c ON c.id = p.category_id';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE p.name LIKE ?';
    $params[] = '%' . $q . '%';
}
$sql .= ' ORDER BY p.id ASC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$adminPageTitle = 'Товары';
$adminPageEyebrow = 'Каталог';
include APP_ROOT . '/app/views/admin-header.php';
?>
<div class="admin-toolbar">
    <form class="admin-search" action="/admin/products.php" method="get">
        <svg viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.7"/><path d="m16.5 16.5 4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Найти товар по названию">
    </form>
    <div class="admin-toolbar__actions">
        <span class="admin-toolbar__count"><?= count($products) ?> <?= plural_ru(count($products), 'товар', 'товара', 'товаров') ?></span>
        <a class="admin-button admin-button--primary" href="/admin/product-edit.php">
            <svg viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            Добавить товар
        </a>
    </div>
</div>

<section class="admin-panel">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Товар</th>
                    <th>Категория</th>
                    <th>Цена</th>
                    <th>Остаток</th>
                    <th>Статус</th>
                    <th class="admin-table__actions">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <div class="admin-product">
                                <img src="<?= e(product_image($product['image'])) ?>" alt="" width="64" height="48">
                                <div><strong><?= e($product['name']) ?></strong><span>Арт. <?= str_pad((string) $product['id'], 5, '0', STR_PAD_LEFT) ?><?= (int) $product['is_featured'] ? ' · Хит' : '' ?></span></div>
                            </div>
                        </td>
                        <td><?= e($product['category_name']) ?></td>
                        <td>
                            <strong><?= format_price($product['price']) ?></strong>
                            <?php if ((int) $product['old_price'] > 0): ?><s class="admin-table__old"><?= format_price($product['old_price']) ?></s><?php endif; ?>
                        </td>
                        <td><span class="<?= (int) $product['stock_quantity'] === 0 ? 'stock-pill stock-pill--empty' : ((int) $product['stock_quantity'] <= 3 ? 'stock-pill stock-pill--low' : 'stock-pill') ?>"><?= (int) $product['stock_quantity'] ?> шт.</span></td>
                        <td><span class="<?= (int) $product['is_active'] ? 'status status--completed' : 'status status--cancelled' ?>"><?= (int) $product['is_active'] ? 'Активен' : 'Скрыт' ?></span></td>
                        <td class="admin-table__actions">
                            <a class="admin-icon-button" href="/admin/product-edit.php?id=<?= (int) $product['id'] ?>" aria-label="Редактировать <?= e($product['name']) ?>" title="Редактировать">
                                <svg viewBox="0 0 24 24" fill="none"><path d="m4 20 4.5-1 10-10-3.5-3.5-10 10L4 20Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="m13.5 6.5 3.5 3.5" stroke="currentColor" stroke-width="1.6"/></svg>
                            </a>
                            <form action="/admin/products.php" method="post" data-confirm="Удалить товар «<?= e($product['name']) ?>»?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <button class="admin-icon-button admin-icon-button--danger" type="submit" aria-label="Удалить <?= e($product['name']) ?>" title="Удалить">
                                    <svg viewBox="0 0 24 24" fill="none"><path d="M4 7h16M9 7V5h6v2M7 7l1 13h8l1-13M10 11v5M14 11v5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$products): ?>
        <div class="admin-empty"><p>Товары не найдены.</p><a class="admin-button" href="/admin/products.php">Сбросить поиск</a></div>
    <?php endif; ?>
</section>
<?php include APP_ROOT . '/app/views/admin-footer.php'; ?>
