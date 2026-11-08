<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_admin();

$productId = (int) ($_GET['id'] ?? 0);
$product = null;
if ($productId > 0) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) {
        flash('error', 'Товар не найден.');
        redirect('/admin/products.php');
    }
}

$categories = categories_with_counts();
$availableImages = [];
foreach (glob(APP_ROOT . '/public/assets/images/products/*.svg') ?: [] as $file) {
    $availableImages[] = basename($file);
}
sort($availableImages);

$form = [
    'name' => $product['name'] ?? '',
    'category_id' => (int) ($product['category_id'] ?? ($categories[0]['id'] ?? 1)),
    'price' => $product['price'] ?? '',
    'old_price' => $product['old_price'] ?? '',
    'stock_quantity' => $product['stock_quantity'] ?? 0,
    'description' => $product['description'] ?? '',
    'specs' => implode("\n", array_map(
        static fn ($key, $value) => $key . ': ' . $value,
        array_keys(product_specs($product ?? ['specs' => '{}'])),
        array_values(product_specs($product ?? ['specs' => '{}']))
    )),
    'image' => $product['image'] ?? 'placeholder.svg',
    'is_featured' => (int) ($product['is_featured'] ?? 0),
    'is_active' => (int) ($product['is_active'] ?? 1),
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $form['name'] = trim((string) ($_POST['name'] ?? ''));
    $form['category_id'] = (int) ($_POST['category_id'] ?? 0);
    $form['price'] = trim((string) ($_POST['price'] ?? ''));
    $form['old_price'] = trim((string) ($_POST['old_price'] ?? ''));
    $form['stock_quantity'] = trim((string) ($_POST['stock_quantity'] ?? '0'));
    $form['description'] = trim((string) ($_POST['description'] ?? ''));
    $form['specs'] = trim((string) ($_POST['specs'] ?? ''));
    $form['image'] = (string) ($_POST['image'] ?? 'placeholder.svg');
    $form['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $form['is_active'] = isset($_POST['is_active']) ? 1 : 0;

    $price = filter_var($form['price'], FILTER_VALIDATE_INT);
    $oldPrice = $form['old_price'] === '' ? null : filter_var($form['old_price'], FILTER_VALIDATE_INT);
    $stock = filter_var($form['stock_quantity'], FILTER_VALIDATE_INT);
    $categoryIds = array_map(static fn ($category) => (int) $category['id'], $categories);

    if (mb_strlen($form['name']) < 2) {
        $errors[] = 'Укажите название товара.';
    }
    if (!in_array($form['category_id'], $categoryIds, true)) {
        $errors[] = 'Выберите корректную категорию.';
    }
    if ($price === false || $price <= 0) {
        $errors[] = 'Укажите корректную цену.';
    }
    if ($oldPrice !== null && ($oldPrice === false || $oldPrice <= $price)) {
        $errors[] = 'Старая цена должна быть выше текущей.';
    }
    if ($stock === false || $stock < 0) {
        $errors[] = 'Остаток не может быть отрицательным.';
    }
    if (!in_array($form['image'], $availableImages, true)) {
        $form['image'] = 'placeholder.svg';
    }
    if ($form['description'] === '') {
        $errors[] = 'Добавьте описание товара.';
    }

    $specs = [];
    foreach (preg_split('/\r\n|\r|\n/', $form['specs']) ?: [] as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = explode(':', $line, 2);
        if (count($parts) === 2 && trim($parts[0]) !== '' && trim($parts[1]) !== '') {
            $specs[trim($parts[0])] = trim($parts[1]);
        }
    }

    if (!$errors) {
        $specsJson = json_encode($specs, JSON_UNESCAPED_UNICODE);
        if ($product) {
            $stmt = db()->prepare(
                'UPDATE products
                 SET category_id = ?, name = ?, description = ?, specs = ?, price = ?, old_price = ?, stock_quantity = ?, image = ?, is_featured = ?, is_active = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $form['category_id'],
                $form['name'],
                $form['description'],
                $specsJson,
                $price,
                $oldPrice,
                $stock,
                $form['image'],
                $form['is_featured'],
                $form['is_active'],
                $productId,
            ]);
            flash('success', 'Товар обновлён.');
        } else {
            $stmt = db()->prepare(
                'INSERT INTO products (category_id, name, description, specs, price, old_price, stock_quantity, image, is_featured, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $form['category_id'],
                $form['name'],
                $form['description'],
                $specsJson,
                $price,
                $oldPrice,
                $stock,
                $form['image'],
                $form['is_featured'],
                $form['is_active'],
            ]);
            flash('success', 'Товар добавлен в каталог.');
        }
        redirect('/admin/products.php');
    }
}

$adminPageTitle = $product ? 'Редактирование товара' : 'Новый товар';
$adminPageEyebrow = 'Каталог';
include APP_ROOT . '/app/views/admin-header.php';
?>
<div class="admin-edit-head">
    <a class="admin-back" href="/admin/products.php">
        <svg viewBox="0 0 24 24" fill="none"><path d="M19 12H5M11 6l-6 6 6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Все товары
    </a>
    <div class="admin-edit-head__actions">
        <?php if ($product): ?><a class="admin-button" href="/product.php?id=<?= (int) $product['id'] ?>" target="_blank" rel="noopener">Открыть на сайте</a><?php endif; ?>
    </div>
</div>

<?php if ($errors): ?>
    <div class="form-errors" role="alert">
        <strong>Проверьте данные товара:</strong>
        <ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form action="/admin/product-edit.php<?= $product ? '?id=' . (int) $product['id'] : '' ?>" method="post" class="admin-edit-layout">
    <?= csrf_field() ?>
    <div class="admin-edit-main">
        <section class="admin-panel">
            <div class="admin-panel__head"><div><h2>Основная информация</h2><p>Название и описание для витрины</p></div></div>
            <div class="form-grid">
                <label class="field field--wide"><span>Название</span><input type="text" name="name" value="<?= e($form['name']) ?>" required></label>
                <label class="field"><span>Категория</span>
                    <select name="category_id">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>" <?= (int) $form['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="field"><span>Артикул</span><input type="text" value="Автоматически" disabled></label>
                <label class="field field--wide"><span>Описание</span><textarea name="description" rows="5" required><?= e($form['description']) ?></textarea></label>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel__head"><div><h2>Характеристики</h2><p>По одной паре «ключ: значение» в строке</p></div></div>
            <label class="field">
                <textarea name="specs" rows="7" placeholder="Процессор: AMD Ryzen 7&#10;Память: 16 ГБ"><?= e($form['specs']) ?></textarea>
                <small>Например: «Дисплей: 14 дюймов, OLED»</small>
            </label>
        </section>
    </div>

    <aside class="admin-edit-aside">
        <section class="admin-panel">
            <div class="admin-panel__head"><div><h2>Цена и остаток</h2></div></div>
            <div class="form-grid form-grid--single">
                <label class="field"><span>Цена, ₽</span><input type="number" name="price" value="<?= e((string) $form['price']) ?>" min="1" step="1" required></label>
                <label class="field"><span>Старая цена, ₽</span><input type="number" name="old_price" value="<?= e((string) $form['old_price']) ?>" min="0" step="1" placeholder="Необязательно"></label>
                <label class="field"><span>На складе, шт.</span><input type="number" name="stock_quantity" value="<?= e((string) $form['stock_quantity']) ?>" min="0" step="1" required></label>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel__head"><div><h2>Изображение</h2></div></div>
            <div class="admin-image-preview">
                <img src="<?= e(product_image($form['image'])) ?>" alt="" data-image-preview width="240" height="180">
            </div>
            <label class="field">
                <span>Файл</span>
                <select name="image" data-image-select>
                    <?php foreach ($availableImages as $image): ?>
                        <option value="<?= e($image) ?>" <?= $form['image'] === $image ? 'selected' : '' ?>><?= e($image) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </section>

        <section class="admin-panel">
            <div class="admin-panel__head"><div><h2>Публикация</h2></div></div>
            <label class="admin-check"><input type="checkbox" name="is_active" value="1" <?= $form['is_active'] ? 'checked' : '' ?>><span>Показывать в каталоге</span></label>
            <label class="admin-check"><input type="checkbox" name="is_featured" value="1" <?= $form['is_featured'] ? 'checked' : '' ?>><span>На главной странице</span></label>
        </section>

        <button class="admin-button admin-button--primary admin-button--full" type="submit"><?= $product ? 'Сохранить изменения' : 'Добавить товар' ?></button>
        <a class="admin-button admin-button--full" href="/admin/products.php">Отмена</a>
    </aside>
</form>
<?php include APP_ROOT . '/app/views/admin-footer.php'; ?>
