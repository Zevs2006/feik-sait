<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'add') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);
        if (cart_add($productId, $quantity)) {
            flash('success', 'Товар добавлен в корзину.');
        } else {
            flash('error', 'Этот товар сейчас недоступен для заказа.');
        }
        redirect('/cart.php');
    }

    if ($action === 'update') {
        $cartItemId = (int) ($_POST['cart_item_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);
        if (cart_update($cartItemId, $quantity)) {
            flash('success', 'Количество обновлено.');
        } else {
            flash('warning', 'Не удалось изменить количество.');
        }
        redirect('/cart.php');
    }

    if ($action === 'remove') {
        cart_remove((int) ($_POST['cart_item_id'] ?? 0));
        flash('success', 'Товар удалён из корзины.');
        redirect('/cart.php');
    }
}

$items = cart_items();
$subtotal = cart_subtotal($items);
$units = array_sum(array_map(static fn ($item) => (int) $item['quantity'], $items));
$estimatedDelivery = $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : 590;
$estimatedTotal = $subtotal + $estimatedDelivery;
$pageTitle = 'Корзина — ВОЛЬТ';
$bodyClass = 'page-cart';
include APP_ROOT . '/app/views/header.php';
?>
<div class="page-head page-head--compact">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="/">Главная</a><span>/</span><span>Корзина</span>
        </nav>
        <div class="page-head__row">
            <div>
                <div class="eyebrow"><span></span> Ваш заказ</div>
                <h1>Корзина</h1>
            </div>
            <?php if ($items): ?>
                <span class="page-head__meta"><?= $units ?> <?= plural_ru($units, 'товар', 'товара', 'товаров') ?> · <?= count($items) ?> <?= plural_ru(count($items), 'позиция', 'позиции', 'позиций') ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<section class="cart section section--flush">
    <div class="container">
        <?php if (!$items): ?>
            <div class="empty-state empty-state--page">
                <span class="empty-state__icon">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M3.5 4.5h2l2 11h10.2l2-8H6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="19" r="1.3" fill="currentColor"/><circle cx="17.5" cy="19" r="1.3" fill="currentColor"/></svg>
                </span>
                <h2>Пока здесь пусто</h2>
                <p>Добавьте технику в корзину, чтобы оформить заказ. В каталоге есть всё необходимое.</p>
                <a class="button button--lg" href="/catalog.php">Перейти в каталог</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-list">
                    <div class="cart-list__head">
                        <span>Товар</span>
                        <span>Цена</span>
                        <span>Количество</span>
                        <span>Сумма</span>
                        <span></span>
                    </div>
                    <?php foreach ($items as $item): ?>
                        <?php $lineTotal = (int) $item['price'] * (int) $item['quantity']; ?>
                        <article class="cart-line">
                            <div class="cart-line__product">
                                <a class="cart-line__image" href="/product.php?id=<?= (int) $item['id'] ?>">
                                    <img src="<?= e(product_image($item['image'])) ?>" alt="<?= e($item['name']) ?>" width="160" height="120">
                                </a>
                                <div class="cart-line__info">
                                    <span><?= e($item['category_name'] ?? '') ?></span>
                                    <h2><a href="/product.php?id=<?= (int) $item['id'] ?>"><?= e($item['name']) ?></a></h2>
                                    <small>Арт. <?= str_pad((string) $item['id'], 5, '0', STR_PAD_LEFT) ?> · В наличии <?= (int) $item['stock_quantity'] ?> шт.</small>
                                </div>
                            </div>
                            <div class="cart-line__price" data-label="Цена">
                                <strong><?= format_price($item['price']) ?></strong>
                                <?php if ((int) $item['old_price'] > (int) $item['price']): ?><s><?= format_price($item['old_price']) ?></s><?php endif; ?>
                            </div>
                            <div class="cart-line__quantity" data-label="Количество">
                                <form action="/cart.php" method="post" class="cart-qty-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="cart_item_id" value="<?= (int) $item['cart_item_id'] ?>">
                                    <div class="quantity quantity--small" data-quantity>
                                        <button type="button" data-qty-minus aria-label="Уменьшить количество">−</button>
                                        <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="1" max="99" aria-label="Количество <?= e($item['name']) ?>">
                                        <button type="button" data-qty-plus aria-label="Увеличить количество">+</button>
                                    </div>
                                    <button class="cart-qty-form__submit" type="submit">Обновить</button>
                                </form>
                            </div>
                            <div class="cart-line__total" data-label="Сумма">
                                <strong><?= format_price($lineTotal) ?></strong>
                            </div>
                            <form action="/cart.php" method="post" class="cart-line__remove">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="cart_item_id" value="<?= (int) $item['cart_item_id'] ?>">
                                <button type="submit" aria-label="Удалить <?= e($item['name']) ?> из корзины">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M9 7V5h6v2M7 7l1 13h8l1-13M10 11v5M14 11v5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                    <div class="cart-list__footer">
                        <a class="text-link" href="/catalog.php">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M19 12H5M11 6l-6 6 6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Продолжить покупки
                        </a>
                        <span>Цены и наличие подтверждаются при оформлении</span>
                    </div>
                </div>

                <aside class="cart-summary">
                    <h2>Итого</h2>
                    <div class="cart-summary__row">
                        <span>Товары (<?= $units ?> шт.)</span>
                        <strong><?= format_price($subtotal) ?></strong>
                    </div>
                    <div class="cart-summary__row">
                        <span>Доставка курьером</span>
                        <?php if ($estimatedDelivery === 0): ?>
                            <strong class="text-success">Бесплатно</strong>
                        <?php else: ?>
                            <strong><?= format_price($estimatedDelivery) ?></strong>
                        <?php endif; ?>
                    </div>
                    <?php if ($subtotal < FREE_SHIPPING_THRESHOLD): ?>
                        <div class="shipping-progress">
                            <div class="shipping-progress__label">
                                <span>До бесплатной доставки</span>
                                <strong><?= format_price(FREE_SHIPPING_THRESHOLD - $subtotal) ?></strong>
                            </div>
                            <div class="shipping-progress__bar"><span style="width: <?= min(100, (int) round($subtotal / FREE_SHIPPING_THRESHOLD * 100)) ?>%"></span></div>
                            <small>Бесплатная доставка при заказе от <?= format_price(FREE_SHIPPING_THRESHOLD) ?></small>
                        </div>
                    <?php else: ?>
                        <div class="shipping-free">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M3 7h11v10H3V7Zm11 3h4l3 3v4h-7v-7Z" stroke="currentColor" stroke-width="1.6"/><circle cx="7" cy="18" r="2" stroke="currentColor" stroke-width="1.6"/><circle cx="18" cy="18" r="2" stroke="currentColor" stroke-width="1.6"/></svg>
                            <span>У вас бесплатная доставка курьером</span>
                        </div>
                    <?php endif; ?>
                    <div class="cart-summary__total">
                        <span>Итого</span>
                        <strong><?= format_price($estimatedTotal) ?></strong>
                    </div>
                    <a class="button button--lg button--full" href="/checkout.php">Перейти к оформлению</a>
                    <div class="cart-summary__trust">
                        <span><svg viewBox="0 0 24 24" fill="none"><path d="M12 3 5 6v5c0 4.6 2.9 8.1 7 10 4.1-1.9 7-5.4 7-10V6l-7-3Z" stroke="currentColor" stroke-width="1.6"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg> Безопасная оплата</span>
                        <span><svg viewBox="0 0 24 24" fill="none"><path d="M4 12a8 8 0 1 1 2.3 5.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M4 17v-5h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg> Возврат 14 дней</span>
                    </div>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include APP_ROOT . '/app/views/footer.php'; ?>
