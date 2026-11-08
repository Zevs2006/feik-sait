<?php
$discount = (int) $product['old_price'] > (int) $product['price']
    ? (int) round((1 - (int) $product['price'] / (int) $product['old_price']) * 100)
    : 0;
$stock = (int) $product['stock_quantity'];
?>
<article class="product-card">
    <div class="product-card__media">
        <?php if ($discount > 0): ?>
            <span class="product-card__badge">−<?= $discount ?>%</span>
        <?php endif; ?>
        <a href="/product.php?id=<?= (int) $product['id'] ?>" aria-label="<?= e($product['name']) ?>">
            <img src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>" loading="lazy" width="800" height="600">
        </a>
    </div>
    <div class="product-card__body">
        <span class="product-card__category"><?= e($product['category_name'] ?? '') ?></span>
        <h3 class="product-card__title"><a href="/product.php?id=<?= (int) $product['id'] ?>"><?= e($product['name']) ?></a></h3>
        <p class="product-card__description"><?= e($product['description']) ?></p>
        <div class="product-card__meta">
            <span class="stock<?= $stock === 0 ? ' stock--empty' : ($stock <= 3 ? ' stock--low' : '') ?>">
                <?php if ($stock === 0): ?>
                    Нет в наличии
                <?php elseif ($stock <= 3): ?>
                    Осталось <?= $stock ?> шт.
                <?php else: ?>
                    В наличии
                <?php endif; ?>
            </span>
            <span class="product-card__sku">Арт. <?= str_pad((string) $product['id'], 5, '0', STR_PAD_LEFT) ?></span>
        </div>
        <div class="product-card__bottom">
            <div class="price">
                <strong><?= format_price($product['price']) ?></strong>
                <?php if ((int) $product['old_price'] > (int) $product['price']): ?>
                    <s><?= format_price($product['old_price']) ?></s>
                <?php endif; ?>
            </div>
            <?php if ($stock > 0): ?>
                <form action="/cart.php" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button class="icon-button icon-button--cart" type="submit" aria-label="Добавить <?= e($product['name']) ?> в корзину">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3.5 4.5h2l2 11h10.2l2-8H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="19" r="1.4" fill="currentColor"/><circle cx="17.5" cy="19" r="1.4" fill="currentColor"/></svg>
                    </button>
                </form>
            <?php else: ?>
                <span class="icon-button icon-button--disabled" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M3.5 4.5h2l2 11h10.2l2-8H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="19" r="1.4" fill="currentColor"/><circle cx="17.5" cy="19" r="1.4" fill="currentColor"/></svg>
                </span>
            <?php endif; ?>
        </div>
    </div>
</article>
