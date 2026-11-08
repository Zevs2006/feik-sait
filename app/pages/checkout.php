<?php
$items = cart_items();
if (!$items) {
    flash('warning', 'Сначала добавьте товары в корзину.');
    redirect('/cart.php');
}

$user = current_user();
$subtotal = cart_subtotal($items);
$units = array_sum(array_map(static fn ($item) => (int) $item['quantity'], $items));

$form = [
    'full_name' => (string) ($user['full_name'] ?? ''),
    'phone' => (string) ($user['phone'] ?? ''),
    'email' => (string) ($user['email'] ?? ''),
    'address' => (string) ($user['address'] ?? ''),
    'delivery_method' => 'courier',
    'payment_method' => 'card_online',
    'comment' => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $form['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
    $form['phone'] = trim((string) ($_POST['phone'] ?? ''));
    $form['email'] = trim((string) ($_POST['email'] ?? ''));
    $form['address'] = trim((string) ($_POST['address'] ?? ''));
    $form['delivery_method'] = (string) ($_POST['delivery_method'] ?? '');
    $form['payment_method'] = (string) ($_POST['payment_method'] ?? '');
    $form['comment'] = trim((string) ($_POST['comment'] ?? ''));

    if (mb_strlen($form['full_name']) < 3) {
        $errors[] = 'Укажите имя и фамилию получателя.';
    }
    if ($form['phone'] === '') {
        $errors[] = 'Укажите номер телефона.';
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Укажите корректный адрес электронной почты.';
    }
    if (mb_strlen($form['address']) < 5) {
        $errors[] = 'Укажите адрес доставки.';
    }
    if (!array_key_exists($form['delivery_method'], DELIVERY_METHODS)) {
        $errors[] = 'Выберите способ доставки.';
    }
    if (!array_key_exists($form['payment_method'], PAYMENT_METHODS)) {
        $errors[] = 'Выберите способ оплаты.';
    }

    $stockErrors = cart_stock_errors($items);
    if ($stockErrors) {
        $errors = array_merge($errors, $stockErrors);
    }

    if (!$errors) {
        $deliveryMethod = $form['delivery_method'];
        $deliveryCost = delivery_cost($deliveryMethod, $subtotal);
        $total = $subtotal + $deliveryCost;
        $comment = trim((string) ($_POST['customer_comment'] ?? ''));
        $orderNumber = next_order_number();

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO orders
                 (order_number, user_id, full_name, phone, email, address, delivery_method, payment_method, comment, subtotal, delivery_cost, total, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $orderNumber,
                $user['id'] ?? null,
                $form['full_name'],
                $form['phone'],
                $form['email'],
                $form['address'],
                $deliveryMethod,
                $form['payment_method'],
                $comment,
                $subtotal,
                $deliveryCost,
                $total,
                'new',
            ]);
            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stockStmt = $pdo->prepare(
                'UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?'
            );

            foreach ($items as $item) {
                $quantity = (int) $item['quantity'];
                $itemStmt->execute([
                    $orderId,
                    (int) $item['id'],
                    $item['name'],
                    (int) $item['price'],
                    $quantity,
                    (int) $item['price'] * $quantity,
                ]);

                $stockStmt->execute([$quantity, (int) $item['id'], $quantity]);
                if ($stockStmt->rowCount() === 0) {
                    throw new RuntimeException('Недостаточно товара на складе: ' . $item['name']);
                }
            }

            cart_clear();
            $pdo->commit();
            $_SESSION['last_order_id'] = $orderId;
            redirect('/order-success.php');
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Не удалось оформить заказ. Проверьте наличие товаров и попробуйте ещё раз.';
            error_log($e->getMessage());
        }
    }
}

$deliveryMethod = array_key_exists($form['delivery_method'], DELIVERY_METHODS) ? $form['delivery_method'] : 'courier';
$deliveryCost = delivery_cost($deliveryMethod, $subtotal);
$total = $subtotal + $deliveryCost;
$pageTitle = 'Оформление заказа — ВОЛЬТ';
$bodyClass = 'page-checkout';
include APP_ROOT . '/app/views/header.php';
?>
<div class="page-head page-head--compact">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="/">Главная</a><span>/</span><a href="/cart.php">Корзина</a><span>/</span><span>Оформление</span>
        </nav>
        <div class="page-head__row">
            <div>
                <div class="eyebrow"><span></span> Последний шаг</div>
                <h1>Оформление заказа</h1>
            </div>
            <span class="page-head__meta"><?= $units ?> <?= plural_ru($units, 'товар', 'товара', 'товаров') ?> в заказе</span>
        </div>
    </div>
</div>

<section class="checkout section section--flush">
    <div class="container">
        <?php if ($errors): ?>
            <div class="form-errors" role="alert">
                <strong>Проверьте данные заказа:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="checkout-layout" action="/checkout.php" method="post" data-checkout-form data-subtotal="<?= $subtotal ?>">
            <?= csrf_field() ?>
            <div class="checkout-main">
                <section class="form-card">
                    <div class="form-card__head">
                        <span>01</span>
                        <div><h2>Получатель</h2><p>Кому и куда доставить заказ</p></div>
                    </div>
                    <div class="form-grid">
                        <label class="field field--wide">
                            <span>ФИО <b>*</b></span>
                            <input type="text" name="full_name" value="<?= e($form['full_name']) ?>" placeholder="Иванов Иван Иванович" autocomplete="name" required>
                        </label>
                        <label class="field">
                            <span>Телефон <b>*</b></span>
                            <input type="tel" name="phone" value="<?= e($form['phone']) ?>" placeholder="+7 999 123-45-67" autocomplete="tel" required>
                        </label>
                        <label class="field">
                            <span>Электронная почта <b>*</b></span>
                            <input type="email" name="email" value="<?= e($form['email']) ?>" placeholder="name@example.ru" autocomplete="email" required>
                        </label>
                        <label class="field field--wide">
                            <span>Адрес доставки <b>*</b></span>
                            <input type="text" name="address" value="<?= e($form['address']) ?>" placeholder="Город, улица, дом, квартира" autocomplete="street-address" required>
                        </label>
                    </div>
                </section>

                <section class="form-card">
                    <div class="form-card__head">
                        <span>02</span>
                        <div><h2>Доставка</h2><p>Выберите удобный способ</p></div>
                    </div>
                    <div class="option-list">
                        <?php foreach (DELIVERY_METHODS as $method => $label): ?>
                            <?php $methodCost = delivery_cost($method, $subtotal); ?>
                            <label class="option-card">
                                <input type="radio" name="delivery_method" value="<?= e($method) ?>" data-delivery-cost="<?= $methodCost ?>" <?= $deliveryMethod === $method ? 'checked' : '' ?>>
                                <span class="option-card__mark"></span>
                                <span class="option-card__content">
                                    <strong><?= e($label) ?></strong>
                                    <small>
                                        <?php if ($method === 'courier'): ?>
                                            Курьером по адресу, 1–2 дня
                                        <?php elseif ($method === 'express'): ?>
                                            Доставка в течение дня
                                        <?php else: ?>
                                            Самовывоз со склада на Красном проспекте, 25
                                        <?php endif; ?>
                                    </small>
                                </span>
                                <b class="option-card__price"><?= $methodCost === 0 ? 'Бесплатно' : format_price($methodCost) ?></b>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="form-hint">Бесплатная доставка курьером при заказе от <?= format_price(FREE_SHIPPING_THRESHOLD) ?>.</p>
                </section>

                <section class="form-card">
                    <div class="form-card__head">
                        <span>03</span>
                        <div><h2>Оплата</h2><p>Выберите способ оплаты</p></div>
                    </div>
                    <div class="option-list option-list--compact">
                        <?php foreach (PAYMENT_METHODS as $method => $label): ?>
                            <label class="option-card">
                                <input type="radio" name="payment_method" value="<?= e($method) ?>" <?= $form['payment_method'] === $method ? 'checked' : '' ?>>
                                <span class="option-card__mark"></span>
                                <span class="option-card__content"><strong><?= e($label) ?></strong></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="form-card">
                    <div class="form-card__head">
                        <span>04</span>
                        <div><h2>Комментарий</h2><p>Необязательно</p></div>
                    </div>
                    <label class="field">
                        <span>Комментарий к заказу</span>
                        <textarea name="comment" rows="4" placeholder="Например: позвонить за час до доставки"><?= e($form['comment']) ?></textarea>
                    </label>
                </section>
            </div>

            <aside class="checkout-summary">
                <h2>Ваш заказ</h2>
                <div class="checkout-summary__items">
                    <?php foreach ($items as $item): ?>
                        <div class="checkout-item">
                            <div class="checkout-item__image">
                                <img src="<?= e(product_image($item['image'])) ?>" alt="<?= e($item['name']) ?>" width="80" height="60">
                                <span><?= (int) $item['quantity'] ?></span>
                            </div>
                            <div>
                                <strong><?= e($item['name']) ?></strong>
                                <small><?= format_price($item['price']) ?> × <?= (int) $item['quantity'] ?></small>
                            </div>
                            <b><?= format_price((int) $item['price'] * (int) $item['quantity']) ?></b>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="checkout-summary__rows">
                    <div><span>Товары</span><strong><?= format_price($subtotal) ?></strong></div>
                    <div><span>Доставка</span><strong data-delivery-total><?= $deliveryCost === 0 ? 'Бесплатно' : format_price($deliveryCost) ?></strong></div>
                </div>
                <div class="checkout-summary__total">
                    <span>К оплате</span>
                    <strong data-order-total><?= format_price($total) ?></strong>
                </div>
                <button class="button button--lg button--full" type="submit">Подтвердить заказ</button>
                <p class="checkout-summary__legal">Нажимая кнопку, вы соглашаетесь с условиями обработки персональных данных.</p>
                <a class="checkout-summary__back" href="/cart.php">Вернуться в корзину</a>
            </aside>
        </form>
    </div>
</section>
<?php include APP_ROOT . '/app/views/footer.php'; ?>
