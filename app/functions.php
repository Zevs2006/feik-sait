<?php

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $items = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $items;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    if (!isset($GLOBALS['_current_user'])) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([(int) $_SESSION['user_id']]);
        $user = $stmt->fetch();
        if (!$user) {
            unset($_SESSION['user_id']);
            return null;
        }
        $GLOBALS['_current_user'] = $user;
    }

    return $GLOBALS['_current_user'];
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['role'] === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('warning', 'Войдите в аккаунт, чтобы продолжить.');
        redirect('/login.php');
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        redirect('/403.php');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = (string) ($_POST['_token'] ?? '');
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Сессия истекла</title><style>body{font-family:Segoe UI,system-ui,sans-serif;background:#f5f6f8;color:#101828;display:grid;place-items:center;min-height:100vh;margin:0}.box{background:#fff;border:1px solid #e4e7ec;border-radius:20px;padding:40px;max-width:440px;text-align:center}h1{font-size:24px;margin:0 0 12px}p{color:#667085;line-height:1.6}a{display:inline-block;margin-top:18px;background:#2447ff;color:#fff;text-decoration:none;padding:12px 22px;border-radius:12px;font-weight:600}</style></head><body><div class="box"><h1>Страница устарела</h1><p>Обновите страницу и попробуйте отправить форму ещё раз.</p><a href="/">На главную</a></div></body></html>';
        exit;
    }
}

function format_price(int|float|string|null $amount): string
{
    return number_format((float) $amount, 0, ',', ' ') . ' ₽';
}

function format_date(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '—';
    }
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '—';
    }
    return date($withTime ? 'd.m.Y, H:i' : 'd.m.Y', $timestamp);
}

function product_image(?string $image): string
{
    $file = basename((string) $image);
    if ($file === '' || !is_file(APP_ROOT . '/public/assets/images/products/' . $file)) {
        $file = 'placeholder.svg';
    }
    return asset('images/products/' . $file);
}

function category_icon(string $slug): string
{
    $icons = [
        'laptops' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="5" width="16" height="11" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M2 19h20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
        'smartphones' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="7" y="2.5" width="10" height="19" rx="2.5" stroke="currentColor" stroke-width="1.6"/><path d="M10.5 18.5h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
        'audio' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 14v-2a8 8 0 0 1 16 0v2" stroke="currentColor" stroke-width="1.6"/><rect x="2.5" y="13" width="4.5" height="7" rx="2" stroke="currentColor" stroke-width="1.6"/><rect x="17" y="13" width="4.5" height="7" rx="2" stroke="currentColor" stroke-width="1.6"/></svg>',
        'gaming' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7.3 7h9.4a5 5 0 0 1 4.8 6.4l-.8 3a2.6 2.6 0 0 1-4.4 1.2L14.7 16H9.3l-1.6 1.6a2.6 2.6 0 0 1-4.4-1.2l-.8-3A5 5 0 0 1 7.3 7Z" stroke="currentColor" stroke-width="1.6"/><path d="M7 10v3M5.5 11.5h3M16 11h.01M18 13h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
        'accessories' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 3v5M15 3v5M7 8h10v4a5 5 0 0 1-10 0V8Z" stroke="currentColor" stroke-width="1.6"/><path d="M12 17v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
    ];
    return $icons[$slug] ?? $icons['accessories'];
}

function cart_key(): string
{
    if (is_logged_in()) {
        return 'user:' . (int) $_SESSION['user_id'];
    }
    return 'guest:' . session_id();
}

function cart_items(): array
{
    $stmt = db()->prepare(
        'SELECT ci.id AS cart_item_id, ci.quantity, p.*
         FROM cart_items ci
         JOIN products p ON p.id = ci.product_id
         WHERE ci.cart_key = ?
         ORDER BY ci.created_at ASC, ci.id ASC'
    );
    $stmt->execute([cart_key()]);
    return $stmt->fetchAll();
}

function cart_count(): int
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM cart_items WHERE cart_key = ?');
    $stmt->execute([cart_key()]);
    return (int) $stmt->fetchColumn();
}

function cart_subtotal(?array $items = null): int
{
    $items ??= cart_items();
    $sum = 0;
    foreach ($items as $item) {
        $sum += (int) $item['price'] * (int) $item['quantity'];
    }
    return $sum;
}

function cart_add(int $productId, int $quantity): bool
{
    $quantity = max(1, min(99, $quantity));
    $stmt = db()->prepare('SELECT id, stock_quantity, is_active FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product || !(int) $product['is_active'] || (int) $product['stock_quantity'] <= 0) {
        return false;
    }

    $key = cart_key();
    $stmt = db()->prepare('SELECT id, quantity FROM cart_items WHERE cart_key = ? AND product_id = ?');
    $stmt->execute([$key, $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $next = min(99, (int) $existing['quantity'] + $quantity);
        $stmt = db()->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?');
        $stmt->execute([$next, (int) $existing['id']]);
    } else {
        $stmt = db()->prepare('INSERT INTO cart_items (cart_key, product_id, quantity) VALUES (?, ?, ?)');
        $stmt->execute([$key, $productId, $quantity]);
    }

    return true;
}

function cart_update(int $cartItemId, int $quantity): bool
{
    $quantity = max(1, min(99, $quantity));
    $stmt = db()->prepare('UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_key = ?');
    $stmt->execute([$quantity, $cartItemId, cart_key()]);
    return $stmt->rowCount() > 0;
}

function cart_remove(int $cartItemId): void
{
    $stmt = db()->prepare('DELETE FROM cart_items WHERE id = ? AND cart_key = ?');
    $stmt->execute([$cartItemId, cart_key()]);
}

function cart_clear(): void
{
    $stmt = db()->prepare('DELETE FROM cart_items WHERE cart_key = ?');
    $stmt->execute([cart_key()]);
}

function cart_stock_errors(array $items): array
{
    $errors = [];
    foreach ($items as $item) {
        if ((int) $item['quantity'] > (int) $item['stock_quantity']) {
            $errors[] = '«' . $item['name'] . '»: в наличии ' . (int) $item['stock_quantity'] . ' шт., в корзине ' . (int) $item['quantity'] . ' шт.';
        }
    }
    return $errors;
}

function delivery_cost(string $method, int $subtotal): int
{
    return match ($method) {
        'express' => 990,
        'pickup' => 0,
        default => $subtotal > 60000 ? 0 : 590,
    };
}

function delivery_label(string $method): string
{
    return DELIVERY_METHODS[$method] ?? $method;
}

function payment_label(string $method): string
{
    return PAYMENT_METHODS[$method] ?? $method;
}

function order_status_label(string $status): string
{
    return ORDER_STATUSES[$status] ?? $status;
}

function order_status_class(string $status): string
{
    return 'status status--' . preg_replace('/[^a-z]/', '', $status);
}

function next_order_number(): string
{
    do {
        $number = 'TM-' . date('ymd') . '-' . random_int(100, 999);
        $stmt = db()->prepare('SELECT COUNT(*) FROM orders WHERE order_number = ?');
        $stmt->execute([$number]);
    } while ((int) $stmt->fetchColumn() > 0);

    return $number;
}

function categories_with_counts(): array
{
    return db()->query(
        'SELECT c.*, COUNT(p.id) AS products_count
         FROM categories c
         LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
         GROUP BY c.id
         ORDER BY c.id'
    )->fetchAll();
}

function find_product(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         JOIN categories c ON c.id = p.category_id
         WHERE p.id = ? AND p.is_active = 1'
    );
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    return $product ?: null;
}

function product_specs(array $product): array
{
    $decoded = json_decode((string) $product['specs'], true);
    return is_array($decoded) ? $decoded : [];
}

function stock_label(int $stock): string
{
    return $stock > 0 ? 'В наличии' : 'Нет в наличии';
}

function query_string(array $overrides = []): string
{
    $params = array_merge($_GET, $overrides);
    foreach ($params as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
        }
    }
    return http_build_query($params);
}

function nav_active(string $file): string
{
    $current = basename($_SERVER['SCRIPT_NAME'] ?? '');
    return $current === $file ? ' is-active' : '';
}

function admin_nav_active(string $file): string
{
    $current = basename($_SERVER['SCRIPT_NAME'] ?? '');
    return $current === $file ? ' is-active' : '';
}

function plural_ru(int $number, string $one, string $few, string $many): string
{
    $mod10 = $number % 10;
    $mod100 = $number % 100;
    if ($mod10 === 1 && $mod100 !== 11) {
        return $one;
    }
    if ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 12 || $mod100 > 14)) {
        return $few;
    }
    return $many;
}
