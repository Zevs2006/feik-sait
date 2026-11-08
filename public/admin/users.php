<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$users = db()->query(
    "SELECT u.*,
            (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS orders_count,
            (SELECT COALESCE(SUM(o.total), 0) FROM orders o WHERE o.user_id = u.id AND o.status <> 'cancelled') AS orders_total
     FROM users u
     ORDER BY u.id ASC"
)->fetchAll();
$adminPageTitle = 'Пользователи';
$adminPageEyebrow = 'Клиенты';
include APP_ROOT . '/app/views/admin-header.php';
?>
<div class="admin-toolbar">
    <div class="admin-toolbar__summary">
        <span class="admin-toolbar__count"><?= count($users) ?> <?= plural_ru(count($users), 'пользователь', 'пользователя', 'пользователей') ?></span>
        <p>Зарегистрированные аккаунты магазина</p>
    </div>
</div>

<section class="admin-panel">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Пользователь</th>
                    <th>Телефон</th>
                    <th>Роль</th>
                    <th>Заказы</th>
                    <th>Сумма покупок</th>
                    <th>Регистрация</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $userRow): ?>
                    <tr>
                        <td>
                            <div class="admin-user-cell">
                                <span class="admin-avatar"><?= e(mb_strtoupper(mb_substr($userRow['full_name'], 0, 1))) ?></span>
                                <div><strong><?= e($userRow['full_name']) ?></strong><span><?= e($userRow['email']) ?></span></div>
                            </div>
                        </td>
                        <td><?= e($userRow['phone'] !== '' ? $userRow['phone'] : '—') ?></td>
                        <td><span class="role-pill role-pill--<?= e($userRow['role']) ?>"><?= $userRow['role'] === 'admin' ? 'Администратор' : 'Покупатель' ?></span></td>
                        <td><?= (int) $userRow['orders_count'] ?></td>
                        <td><strong><?= format_price($userRow['orders_total']) ?></strong></td>
                        <td><?= format_date($userRow['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include APP_ROOT . '/app/views/admin-footer.php'; ?>
