<?php
require_login();
$user = current_user();
$errors = [];
$form = [
    'full_name' => $user['full_name'],
    'email' => $user['email'],
    'phone' => $user['phone'],
    'address' => $user['address'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $form['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
    $form['email'] = trim((string) ($_POST['email'] ?? ''));
    $form['phone'] = trim((string) ($_POST['phone'] ?? ''));
    $form['address'] = trim((string) ($_POST['address'] ?? ''));

    if (mb_strlen($form['full_name']) < 3) {
        $errors[] = 'Укажите имя и фамилию.';
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный адрес электронной почты.';
    }
    if ($form['phone'] === '') {
        $errors[] = 'Укажите телефон.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?');
        $stmt->execute([$form['email'], (int) $user['id']]);
        if ((int) $stmt->fetchColumn() > 0) {
            $errors[] = 'Этот email уже используется другим пользователем.';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?');
        $stmt->execute([$form['full_name'], $form['email'], $form['phone'], $form['address'], (int) $user['id']]);
        unset($GLOBALS['_current_user']);
        flash('success', 'Контактные данные обновлены.');
        redirect('/account.php');
    }
}

$stmt = db()->prepare(
    'SELECT o.*, (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi WHERE oi.order_id = o.id) AS items_count
     FROM orders o
     WHERE o.user_id = ?
     ORDER BY o.created_at DESC, o.id DESC'
);
$stmt->execute([(int) $user['id']]);
$orders = $stmt->fetchAll();
$initials = mb_strtoupper(mb_substr($user['full_name'], 0, 1));
$nameParts = preg_split('/\s+/', trim($user['full_name']));
if (count($nameParts) > 1) {
    $initials .= mb_strtoupper(mb_substr($nameParts[1], 0, 1));
}
$pageTitle = 'Личный кабинет — ВОЛЬТ';
$bodyClass = 'page-account';
include APP_ROOT . '/app/views/header.php';
?>
<div class="page-head page-head--compact">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="/">Главная</a><span>/</span><span>Личный кабинет</span>
        </nav>
        <div class="page-head__row">
            <div>
                <div class="eyebrow"><span></span> Аккаунт</div>
                <h1>Личный кабинет</h1>
            </div>
            <?php if (is_admin()): ?>
                <a class="button button--secondary" href="/admin/index.php">Административная панель</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<section class="account section section--flush">
    <div class="container account-layout">
        <aside class="account-sidebar">
            <div class="account-user">
                <span class="account-user__avatar"><?= e($initials) ?></span>
                <strong><?= e($user['full_name']) ?></strong>
                <span><?= e($user['email']) ?></span>
                <?php if ($user['role'] === 'admin'): ?><b class="account-user__role">Администратор</b><?php endif; ?>
            </div>
            <nav class="account-nav">
                <a class="is-active" href="#profile">Контактные данные</a>
                <a href="#orders">История заказов <b><?= count($orders) ?></b></a>
                <a href="/cart.php">Корзина</a>
            </nav>
            <form action="/logout.php" method="post" class="account-logout">
                <?= csrf_field() ?>
                <button type="submit">Выйти из аккаунта</button>
            </form>
        </aside>

        <div class="account-main">
            <section class="account-card" id="profile">
                <div class="account-card__head">
                    <div><h2>Контактные данные</h2><p>Используем их для оформления доставки</p></div>
                    <span class="account-card__icon"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.6"/><path d="M5 20a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></span>
                </div>

                <?php if ($errors): ?>
                    <div class="form-errors" role="alert">
                        <strong>Проверьте данные:</strong>
                        <ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <form action="/account.php" method="post" class="account-form">
                    <?= csrf_field() ?>
                    <div class="form-grid">
                        <label class="field field--wide">
                            <span>Имя и фамилия</span>
                            <input type="text" name="full_name" value="<?= e($form['full_name']) ?>" autocomplete="name" required>
                        </label>
                        <label class="field">
                            <span>Телефон</span>
                            <input type="tel" name="phone" value="<?= e($form['phone']) ?>" autocomplete="tel" required>
                        </label>
                        <label class="field">
                            <span>Электронная почта</span>
                            <input type="email" name="email" value="<?= e($form['email']) ?>" autocomplete="email" required>
                        </label>
                        <label class="field field--wide">
                            <span>Адрес доставки</span>
                            <input type="text" name="address" value="<?= e($form['address']) ?>" autocomplete="street-address" placeholder="Город, улица, дом, квартира">
                        </label>
                    </div>
                    <div class="account-form__actions">
                        <button class="button" type="submit">Сохранить изменения</button>
                        <span>Дата регистрации: <?= format_date($user['created_at']) ?></span>
                    </div>
                </form>
            </section>

            <section class="account-card" id="orders">
                <div class="account-card__head">
                    <div><h2>История заказов</h2><p>Все покупки и их текущий статус</p></div>
                    <span class="account-card__icon"><svg viewBox="0 0 24 24" fill="none"><path d="M5 4h14v16l-3-2-2 2-2-2-2 2-2-2-3 2V4Z" stroke="currentColor" stroke-width="1.6"/><path d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></span>
                </div>

                <?php if (!$orders): ?>
                    <div class="account-empty">
                        <p>Вы ещё не оформляли заказы.</p>
                        <a class="button" href="/catalog.php">Перейти в каталог</a>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <article class="order-row">
                                <div class="order-row__main">
                                    <span class="order-row__number">Заказ <?= e($order['order_number']) ?></span>
                                    <span class="order-row__date"><?= format_date($order['created_at'], true) ?></span>
                                </div>
                                <div class="order-row__meta">
                                    <span><?= (int) $order['items_count'] ?> <?= plural_ru((int) $order['items_count'], 'товар', 'товара', 'товаров') ?></span>
                                    <span class="<?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span>
                                </div>
                                <div class="order-row__total">
                                    <strong><?= format_price($order['total']) ?></strong>
                                    <a class="text-link" href="/order.php?id=<?= (int) $order['id'] ?>">Детали <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>
<?php include APP_ROOT . '/app/views/footer.php'; ?>
