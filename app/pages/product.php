<?php
$productId = (int) ($_GET['id'] ?? 0);
$product = find_product($productId);

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Товар не найден — ВОЛЬТ';
    include APP_ROOT . '/app/views/header.php';
    ?>
    <div class="container section">
        <div class="empty-state empty-state--page">
            <span class="empty-state__icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 7h16v13H4V7Zm4 0V5h8v2" stroke="currentColor" stroke-width="1.6"/><path d="m9 12 6 6m0-6-6 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></span>
            <h1>Товар не найден</h1>
            <p>Возможно, он больше не продаётся. Посмотрите другие модели в каталоге.</p>
            <a class="button" href="/catalog.php">Перейти в каталог</a>
        </div>
    </div>
    <?php
    include APP_ROOT . '/app/views/footer.php';
    return;
}

$specs = product_specs($product);
$stock = (int) $product['stock_quantity'];
$discount = (int) $product['old_price'] > (int) $product['price']
    ? (int) round((1 - (int) $product['price'] / (int) $product['old_price']) * 100)
    : 0;
$pageTitle = $product['name'] . ' — купить в ВОЛЬТ';
$metaDescription = mb_substr($product['description'], 0, 160);
$bodyClass = 'page-product';

$relatedStmt = db()->prepare(
    'SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.category_id = ? AND p.is_active = 1
     ORDER BY p.id ASC
     LIMIT 4'
);
$relatedStmt->execute([(int) $product['category_id']]);
$related = $relatedStmt->fetchAll();
include APP_ROOT . '/app/views/header.php';
?>
<div class="page-head page-head--compact">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="/">Главная</a><span>/</span>
            <a href="/catalog.php">Каталог</a><span>/</span>
            <a href="/catalog.php?category=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a><span>/</span>
            <span><?= e($product['name']) ?></span>
        </nav>
    </div>
</div>

<section class="product section section--flush">
    <div class="container">
        <div class="product-detail">
            <div class="product-gallery">
                <div class="product-gallery__main">
                    <?php if ($discount > 0): ?><span class="product-gallery__badge">−<?= $discount ?>%</span><?php endif; ?>
                    <img src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>" width="800" height="600">
                </div>
                <div class="product-gallery__notes">
                    <span><svg viewBox="0 0 24 24" fill="none"><path d="M12 3 5 6v5c0 4.6 2.9 8.1 7 10 4.1-1.9 7-5.4 7-10V6l-7-3Z" stroke="currentColor" stroke-width="1.6"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg> Официальная гарантия</span>
                    <span><svg viewBox="0 0 24 24" fill="none"><path d="M3 7h11v10H3V7Zm11 3h4l3 3v4h-7v-7Z" stroke="currentColor" stroke-width="1.6"/><circle cx="7" cy="18" r="2" stroke="currentColor" stroke-width="1.6"/><circle cx="18" cy="18" r="2" stroke="currentColor" stroke-width="1.6"/></svg> Доставка 1–2 дня</span>
                    <span><svg viewBox="0 0 24 24" fill="none"><path d="M4 12a8 8 0 1 1 2.3 5.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M4 17v-5h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg> Возврат 14 дней</span>
                </div>
            </div>

            <div class="product-info">
                <div class="product-info__top">
                    <a class="product-info__category" href="/catalog.php?category=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a>
                    <span class="product-info__sku">Арт. <?= str_pad((string) $product['id'], 5, '0', STR_PAD_LEFT) ?></span>
                </div>
                <h1><?= e($product['name']) ?></h1>
                <div class="product-rating">
                    <span class="stars">★★★★★</span>
                    <span>4,8</span>
                    <a href="#details">24 отзыва</a>
                </div>
                <p class="product-info__description"><?= e($product['description']) ?></p>

                <div class="product-buy">
                    <div class="product-buy__price">
                        <strong><?= format_price($product['price']) ?></strong>
                        <?php if ($discount > 0): ?>
                            <s><?= format_price($product['old_price']) ?></s>
                            <span class="discount-pill">−<?= $discount ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-stock<?= $stock === 0 ? ' product-stock--empty' : '' ?>">
                        <span class="stock-dot"></span>
                        <?php if ($stock === 0): ?>
                            Нет в наличии
                        <?php elseif ($stock <= 3): ?>
                            В наличии: <?= $stock ?> шт. — успейте заказать
                        <?php else: ?>
                            В наличии: <?= $stock ?> шт.
                        <?php endif; ?>
                    </div>

                    <?php if ($stock > 0): ?>
                        <form class="product-buy__form" action="/cart.php" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <div class="quantity" data-quantity>
                                <button type="button" data-qty-minus aria-label="Уменьшить количество">−</button>
                                <input type="number" name="quantity" value="1" min="1" max="99" aria-label="Количество">
                                <button type="button" data-qty-plus aria-label="Увеличить количество">+</button>
                            </div>
                            <button class="button button--lg product-buy__button" type="submit">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3.5 4.5h2l2 11h10.2l2-8H6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="19" r="1.4" fill="currentColor"/><circle cx="17.5" cy="19" r="1.4" fill="currentColor"/></svg>
                                Добавить в корзину
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="button button--lg product-buy__button" type="button" disabled>Нет в наличии</button>
                    <?php endif; ?>

                    <div class="product-buy__perks">
                        <div><span>01</span><p><strong>Оплата при получении</strong>Проверьте товар перед оплатой</p></div>
                        <div><span>02</span><p><strong>Доставка по России</strong>Рассчитаем при оформлении</p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section--details" id="details">
    <div class="container">
        <div class="details-layout">
            <div class="details-main">
                <div class="section-head section-head--small">
                    <div>
                        <div class="eyebrow"><span></span> О товаре</div>
                        <h2>Описание и характеристики</h2>
                    </div>
                </div>
                <p class="details-description"><?= e($product['description']) ?></p>
                <?php if ($specs): ?>
                    <div class="specs">
                        <?php foreach ($specs as $label => $value): ?>
                            <div class="specs__row">
                                <span><?= e((string) $label) ?></span>
                                <strong><?= e((string) $value) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <aside class="details-aside">
                <div class="delivery-card">
                    <h3>Доставка и оплата</h3>
                    <div class="delivery-card__item">
                        <span class="delivery-card__icon"><svg viewBox="0 0 24 24" fill="none"><path d="M3 7h11v10H3V7Zm11 3h4l3 3v4h-7v-7Z" stroke="currentColor" stroke-width="1.6"/><circle cx="7" cy="18" r="2" stroke="currentColor" stroke-width="1.6"/><circle cx="18" cy="18" r="2" stroke="currentColor" stroke-width="1.6"/></svg></span>
                        <div><strong>Курьером — от 590 ₽</strong><span>По Новосибирску за 1–2 дня</span></div>
                    </div>
                    <div class="delivery-card__item">
                        <span class="delivery-card__icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 20V9l8-5 8 5v11H4Z" stroke="currentColor" stroke-width="1.6"/><path d="M9 20v-6h6v6" stroke="currentColor" stroke-width="1.6"/></svg></span>
                        <div><strong>Самовывоз — бесплатно</strong><span>Красный проспект, 25</span></div>
                    </div>
                    <div class="delivery-card__item">
                        <span class="delivery-card__icon"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M3 10h18" stroke="currentColor" stroke-width="1.6"/></svg></span>
                        <div><strong>Картой или наличными</strong><span>Онлайн и при получении</span></div>
                    </div>
                    <a class="text-link" href="/info.php?page=delivery">Все условия доставки <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></a>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php if ($related): ?>
    <section class="section section--related">
        <div class="container">
            <div class="section-head">
                <div>
                    <div class="eyebrow"><span></span> С этой моделью смотрят</div>
                    <h2>Похожие товары</h2>
                </div>
                <a class="text-link" href="/catalog.php?category=<?= e($product['category_slug']) ?>">Вся категория <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></a>
            </div>
            <div class="product-grid product-grid--four">
                <?php foreach ($related as $relatedProduct): ?>
                    <?php $product = $relatedProduct; include APP_ROOT . '/app/views/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php include APP_ROOT . '/app/views/footer.php'; ?>
