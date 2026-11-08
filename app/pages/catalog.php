<?php
$q = $_GET['q'] ?? '';
$categorySlug = trim((string) ($_GET['category'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'popular');
$sort = in_array($sort, ['popular', 'price_asc', 'price_desc', 'new'], true) ? $sort : 'popular';
$page = max(1, (int) ($_GET['page'] ?? 1));

$categories = categories_with_counts();
$currentCategory = null;
foreach ($categories as $category) {
    if ($category['slug'] === $categorySlug) {
        $currentCategory = $category;
        break;
    }
}

$where = ['p.is_active = 1'];
$params = [];

if ($q !== '') {
    $where[] = 'p.name LIKE ?';
    $params[] = '%' . $q . '%';
}

if ($currentCategory !== null) {
    $where[] = 'c.slug = ?';
    $params[] = $currentCategory['slug'];
}

$whereSql = implode(' AND ', $where);
$countStmt = db()->prepare(
    "SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id WHERE {$whereSql}"
);
$countStmt->execute($params);
$totalProducts = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalProducts / PRODUCTS_PER_PAGE));
if ($page > $totalPages) {
    $page = $totalPages;
}

$orderBy = match ($sort) {
    'price_asc' => 'p.price DESC',
    'price_desc' => 'p.price DESC',
    'new' => 'p.created_at DESC',
    default => 'p.is_featured DESC, p.id ASC',
};

$offset = 0;
$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        JOIN categories c ON c.id = p.category_id
        WHERE {$whereSql}
        ORDER BY {$orderBy}
        LIMIT " . PRODUCTS_PER_PAGE . " OFFSET {$offset}";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

if ($currentCategory !== null) {
    $pageTitle = $currentCategory['name'] . ' — каталог ВОЛЬТ';
} elseif ($q !== '') {
    $pageTitle = 'Поиск: ' . $q . ' — ВОЛЬТ';
} else {
    $pageTitle = 'Каталог техники — ВОЛЬТ';
}
$metaDescription = 'Каталог электроники ВОЛЬТ: ноутбуки, смартфоны, аудио, гейминг и аксессуары.';
$bodyClass = 'page-catalog';
include APP_ROOT . '/app/views/header.php';
?>
<div class="page-head">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="/">Главная</a>
            <span>/</span>
            <a href="/catalog.php">Каталог</a>
            <?php if ($currentCategory): ?><span>/</span><span><?= e($currentCategory['name']) ?></span><?php endif; ?>
        </nav>
        <div class="page-head__row">
            <div>
                <div class="eyebrow"><span></span> <?= $q !== '' ? 'Поиск по каталогу' : 'Вся техника' ?></div>
                <h1>
                    <?php if ($q !== ''): ?>
                        Поиск: «<?= e($q) ?>»
                    <?php elseif ($currentCategory): ?>
                        <?= e($currentCategory['name']) ?>
                    <?php else: ?>
                        Каталог техники
                    <?php endif; ?>
                </h1>
                <p>
                    <?php if ($currentCategory): ?>
                        <?= e($currentCategory['description']) ?>. <?= $totalProducts ?> <?= plural_ru($totalProducts, 'товар', 'товара', 'товаров') ?> в наличии.
                    <?php else: ?>
                        Подберите технику для работы, учёбы и отдыха. В каталоге <?= $totalProducts ?> <?= plural_ru($totalProducts, 'товар', 'товара', 'товаров') ?>.
                    <?php endif; ?>
                </p>
            </div>
            <?php if ($q !== ''): ?>
                <a class="button button--secondary" href="/catalog.php">Сбросить поиск</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<section class="catalog section section--flush">
    <div class="container catalog__layout">
        <aside class="filters" aria-label="Фильтры каталога">
            <div class="filters__head">
                <h2>Категории</h2>
                <span><?= count($categories) ?></span>
            </div>
            <nav class="filters__list">
                <a class="<?= $currentCategory === null ? 'is-active' : '' ?>" href="/catalog.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>">
                    <span>Все товары</span>
                    <b><?= array_sum(array_map(static fn ($category) => (int) $category['products_count'], $categories)) ?></b>
                </a>
                <?php foreach ($categories as $category): ?>
                    <a class="<?= $currentCategory && $currentCategory['slug'] === $category['slug'] ? 'is-active' : '' ?>" href="/catalog.php?category=<?= e($category['slug']) ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>">
                        <span><?= e($category['name']) ?></span>
                        <b><?= (int) $category['products_count'] ?></b>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="filters__note">
                <strong>Нужна помощь?</strong>
                <p>Позвоните нам — поможем сравнить характеристики и выбрать модель.</p>
                <a href="tel:+73835550880"><?= e(SITE_PHONE) ?></a>
            </div>
        </aside>

        <div class="catalog__content">
            <div class="catalog-toolbar">
                <span class="catalog-toolbar__count">
                    <?= $totalProducts ?> <?= plural_ru($totalProducts, 'товар', 'товара', 'товаров') ?>
                </span>
                <form class="sort-form" action="/catalog.php" method="get">
                    <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
                    <?php if ($currentCategory): ?><input type="hidden" name="category" value="<?= e($currentCategory['slug']) ?>"><?php endif; ?>
                    <label for="sort">Сортировка:</label>
                    <select name="sort" id="sort" data-auto-submit>
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>По популярности</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Сначала дешевле</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Сначала дороже</option>
                        <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Сначала новые</option>
                    </select>
                    <noscript><button class="button button--small" type="submit">Применить</button></noscript>
                </form>
            </div>

            <?php if (!$products): ?>
                <div class="empty-state">
                    <span class="empty-state__icon">
                        <svg viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.6"/><path d="m16.5 16.5 4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                    </span>
                    <h2>Ничего не найдено</h2>
                    <p>Попробуйте изменить запрос или выбрать другую категорию.</p>
                    <a class="button" href="/catalog.php">Вернуться в каталог</a>
                </div>
            <?php else: ?>
                <div class="product-grid product-grid--catalog">
                    <?php foreach ($products as $product): ?>
                        <?php include APP_ROOT . '/app/views/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="pagination" aria-label="Страницы каталога">
                        <?php if ($page > 1): ?>
                            <a class="pagination__arrow" href="/catalog.php?<?= e(query_string(['page' => $page - 1])) ?>" aria-label="Предыдущая страница">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M15 6 9 12l6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <a class="<?= $p === $page ? 'is-active' : '' ?>" href="/catalog.php?<?= e(query_string(['page' => $p])) ?>" <?= $p === $page ? 'aria-current="page"' : '' ?>><?= $p ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <a class="pagination__arrow" href="/catalog.php?<?= e(query_string(['page' => $page + 1])) ?>" aria-label="Следующая страница">
                                <svg viewBox="0 0 24 24" fill="none"><path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        <?php endif; ?>
                    </nav>
                    <p class="pagination__caption">Страница <?= $page ?> из <?= $totalPages ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include APP_ROOT . '/app/views/footer.php'; ?>
