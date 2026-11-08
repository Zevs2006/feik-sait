<?php
$pageTitle = 'ВОЛЬТ — электроника и компьютерная техника';
$bodyClass = 'page-home';
$categories = categories_with_counts();
$heroProduct = find_product(1);
$offerProduct = find_product(13);
$featured = db()->query(
    'SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.is_active = 1 AND p.is_featured = 1
     ORDER BY p.id
     LIMIT 8'
)->fetchAll();
$offers = db()->query(
    'SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.is_active = 1 AND p.old_price IS NOT NULL AND p.old_price > p.price
     ORDER BY (p.old_price - p.price) * 100 / p.old_price DESC
     LIMIT 4'
)->fetchAll();
$newProducts = db()->query(
    'SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.is_active = 1
     ORDER BY p.created_at DESC
     LIMIT 4'
)->fetchAll();
include APP_ROOT . '/app/views/header.php';
?>
<section class="hero">
    <div class="container">
        <div class="hero__card">
            <div class="hero__content">
                <div class="eyebrow eyebrow--light"><span></span> Новая коллекция · 2026</div>
                <h1>Техника, которая<br>работает на вас</h1>
                <p>Ноутбуки, смартфоны и аудио для работы, учёбы и отдыха. Подбираем устройства, которым можно доверять.</p>
                <div class="hero__actions">
                    <a class="button button--light button--lg" href="/catalog.php">
                        Перейти в каталог
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                    <a class="button button--ghost-light button--lg" href="/catalog.php?sort=new">Новинки</a>
                </div>
                <div class="hero__stats">
                    <div><strong>4,9</strong><span>средняя оценка</span></div>
                    <div><strong>24 ч</strong><span>отправка заказа</span></div>
                    <div><strong>2 года</strong><span>гарантия</span></div>
                </div>
            </div>
            <div class="hero__visual">
                <div class="hero__halo"></div>
                <span class="hero__label hero__label--top">Хит продаж</span>
                <div class="hero__product">
                    <img src="<?= e(product_image($heroProduct['image'] ?? 'laptop-air.svg')) ?>" alt="<?= e($heroProduct['name'] ?? 'Aurora Air 14') ?>" width="800" height="600">
                </div>
                <div class="hero__product-info">
                    <div>
                        <span><?= e($heroProduct['category_name'] ?? 'Ноутбуки') ?></span>
                        <strong><?= e($heroProduct['name'] ?? 'Aurora Air 14') ?></strong>
                    </div>
                    <b><?= format_price($heroProduct['price'] ?? 54990) ?></b>
                </div>
                <div class="hero__rating">
                    <span class="stars">★★★★★</span>
                    <span>4,9 · 128 отзывов</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="benefits">
    <div class="container benefits__grid">
        <div class="benefit">
            <span class="benefit__icon"><svg viewBox="0 0 24 24" fill="none"><path d="M3 7h11v10H3V7Zm11 3h4l3 3v4h-7v-7Z" stroke="currentColor" stroke-width="1.6"/><circle cx="7" cy="18" r="2" stroke="currentColor" stroke-width="1.6"/><circle cx="18" cy="18" r="2" stroke="currentColor" stroke-width="1.6"/></svg></span>
            <div><strong>Доставка 1–2 дня</strong><span>По Новосибирску и всей России</span></div>
        </div>
        <div class="benefit">
            <span class="benefit__icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 3 5 6v5c0 4.6 2.9 8.1 7 10 4.1-1.9 7-5.4 7-10V6l-7-3Z" stroke="currentColor" stroke-width="1.6"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></span>
            <div><strong>Официальная гарантия</strong><span>До 2 лет на всю технику</span></div>
        </div>
        <div class="benefit">
            <span class="benefit__icon"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M3 10h18M7 15h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></span>
            <div><strong>Удобная оплата</strong><span>Картой онлайн или при получении</span></div>
        </div>
        <div class="benefit">
            <span class="benefit__icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 13v-2a8 8 0 0 1 16 0v2" stroke="currentColor" stroke-width="1.6"/><rect x="3" y="13" width="4" height="7" rx="2" stroke="currentColor" stroke-width="1.6"/><rect x="17" y="13" width="4" height="7" rx="2" stroke="currentColor" stroke-width="1.6"/></svg></span>
            <div><strong>Поддержка рядом</strong><span>Поможем с выбором и настройкой</span></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <div class="eyebrow"><span></span> Категории</div>
                <h2>Выберите своё</h2>
            </div>
            <a class="text-link" href="/catalog.php">Весь каталог <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        </div>
        <div class="category-grid">
            <?php foreach ($categories as $index => $category): ?>
                <a class="category-card category-card--<?= ($index % 5) + 1 ?>" href="/catalog.php?category=<?= e($category['slug']) ?>">
                    <span class="category-card__icon"><?= category_icon($category['slug']) ?></span>
                    <span class="category-card__name"><?= e($category['name']) ?></span>
                    <span class="category-card__count"><?= (int) $category['products_count'] ?> <?= plural_ru((int) $category['products_count'], 'товар', 'товара', 'товаров') ?></span>
                    <span class="category-card__arrow"><svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--products">
    <div class="container">
        <div class="section-head">
            <div>
                <div class="eyebrow"><span></span> Популярное</div>
                <h2>Выбор покупателей</h2>
            </div>
            <a class="text-link" href="/catalog.php?sort=popular">Смотреть все <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        </div>
        <div class="product-grid">
            <?php foreach ($featured as $product): ?>
                <?php include APP_ROOT . '/app/views/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="promo">
            <div class="promo__content">
                <div class="eyebrow eyebrow--light"><span></span> Специальное предложение</div>
                <h2>Тишина, которую<br>слышно</h2>
                <p>StudioMax ANC — полноразмерные наушники с адаптивным шумоподавлением и 34 часами автономной работы.</p>
                <div class="promo__price">
                    <strong><?= format_price($offerProduct['price'] ?? 34990) ?></strong>
                    <s><?= format_price($offerProduct['old_price'] ?? 39990) ?></s>
                    <span>выгода 5 000 ₽</span>
                </div>
                <div class="promo__actions">
                    <a class="button button--accent" href="/product.php?id=13">Смотреть товар</a>
                    <a class="button button--ghost-light" href="/catalog.php?category=audio">Вся аудиотехника</a>
                </div>
            </div>
            <div class="promo__visual">
                <span class="promo__sticker">−13%</span>
                <img src="<?= asset('images/products/audio-studiomax.svg') ?>" alt="StudioMax ANC" width="800" height="600">
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <div class="eyebrow"><span></span> Выгодно сейчас</div>
                <h2>Специальные предложения</h2>
            </div>
            <a class="text-link" href="/catalog.php?sort=new">Все предложения <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        </div>
        <div class="product-grid product-grid--four">
            <?php foreach ($offers as $product): ?>
                <?php include APP_ROOT . '/app/views/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="section-head">
            <div>
                <div class="eyebrow"><span></span> Только что привезли</div>
                <h2>Новинки</h2>
            </div>
            <a class="text-link" href="/catalog.php?sort=new">Смотреть все <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        </div>
        <div class="product-grid product-grid--four">
            <?php foreach ($newProducts as $product): ?>
                <?php include APP_ROOT . '/app/views/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--info">
    <div class="container">
        <div class="info-grid">
            <article class="info-card">
                <span class="info-card__number">01</span>
                <h3>Быстрая доставка</h3>
                <p>Отправим заказ в день оплаты. По Новосибирску — курьером за 1–2 дня, по России — транспортной компанией.</p>
                <a href="/info.php?page=delivery">Подробнее о доставке</a>
            </article>
            <article class="info-card info-card--accent">
                <span class="info-card__number">02</span>
                <h3>Гарантия и сервис</h3>
                <p>Официальная гарантия производителя до 2 лет. Поможем с настройкой и обращением в сервисный центр.</p>
                <a href="/info.php?page=warranty">Условия гарантии</a>
            </article>
            <article class="info-card">
                <span class="info-card__number">03</span>
                <h3>Возврат 14 дней</h3>
                <p>Если техника не подошла, верните её в течение 14 дней в исходной упаковке и с полным комплектом.</p>
                <a href="/info.php?page=warranty">Как оформить возврат</a>
            </article>
        </div>
    </div>
</section>
<?php include APP_ROOT . '/app/views/footer.php'; ?>
