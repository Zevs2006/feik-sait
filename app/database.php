<?php

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $databaseDir = dirname(DB_PATH);
    if (!is_dir($databaseDir)) {
        mkdir($databaseDir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    $pdo->exec('PRAGMA journal_mode = WAL');

    $hasProducts = $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'products'")->fetchColumn();
    if (!(int) $hasProducts) {
        initialize_database($pdo);
    }

    return $pdo;
}

function initialize_database(PDO $pdo): void
{
    $schema = file_get_contents(APP_ROOT . '/database/schema.sql');
    $seed = file_get_contents(APP_ROOT . '/database/seed.sql');

    if ($schema === false || $seed === false) {
        throw new RuntimeException('Не удалось загрузить файлы инициализации базы данных.');
    }

    $pdo->beginTransaction();
    try {
        $pdo->exec($schema);
        $pdo->exec($seed);
        seed_accounts($pdo);
        seed_orders($pdo);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function seed_accounts(PDO $pdo): void
{
    $accounts = [
        [
            'full_name' => 'Илья Волков',
            'email' => 'admin@test.local',
            'phone' => '+7 913 204-77-10',
            'address' => 'г. Новосибирск, Красный проспект, д. 25, офис 4',
            'password' => 'Admin12345',
            'role' => 'admin',
            'created_at' => '2026-01-12 09:00:00',
        ],
        [
            'full_name' => 'Анна Смирнова',
            'email' => 'user@test.local',
            'phone' => '+7 913 555-21-84',
            'address' => 'г. Новосибирск, ул. Ленина, д. 42, кв. 17',
            'password' => 'User12345',
            'role' => 'customer',
            'created_at' => '2026-02-03 14:20:00',
        ],
        [
            'full_name' => 'Мария Ковалёва',
            'email' => 'maria@test.local',
            'phone' => '+7 923 118-40-52',
            'address' => 'г. Бердск, ул. Первомайская, д. 8, кв. 3',
            'password' => 'Maria12345',
            'role' => 'customer',
            'created_at' => '2026-03-21 11:10:00',
        ],
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO users (full_name, email, phone, address, password_hash, role, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    foreach ($accounts as $account) {
        $stmt->execute([
            $account['full_name'],
            $account['email'],
            $account['phone'],
            $account['address'],
            password_hash($account['password'], PASSWORD_DEFAULT),
            $account['role'],
            $account['created_at'],
        ]);
    }
}

function seed_orders(PDO $pdo): void
{
    $stmt = $pdo->prepare('SELECT id, email FROM users');
    $stmt->execute();
    $users = [];
    foreach ($stmt->fetchAll() as $user) {
        $users[$user['email']] = (int) $user['id'];
    }

    $orders = [
        [
            'number' => 'TM-2026-0418',
            'user_id' => $users['user@test.local'],
            'full_name' => 'Анна Смирнова',
            'phone' => '+7 913 555-21-84',
            'email' => 'user@test.local',
            'address' => 'г. Новосибирск, ул. Ленина, д. 42, кв. 17',
            'delivery' => 'courier',
            'payment' => 'card_online',
            'comment' => 'Позвонить за час до доставки.',
            'subtotal' => 16980,
            'delivery_cost' => 590,
            'total' => 17570,
            'status' => 'completed',
            'created_at' => '2026-04-18 14:05:00',
            'items' => [
                [11, 'AeroBuds Pro', 12990, 1],
                [21, 'PowerCore 20000', 3990, 1],
            ],
        ],
        [
            'number' => 'TM-2026-0731',
            'user_id' => $users['user@test.local'],
            'full_name' => 'Анна Смирнова',
            'phone' => '+7 913 555-21-84',
            'email' => 'user@test.local',
            'address' => 'г. Новосибирск, ул. Ленина, д. 42, кв. 17',
            'delivery' => 'pickup',
            'payment' => 'card_online',
            'comment' => '',
            'subtotal' => 99990,
            'delivery_cost' => 0,
            'total' => 99990,
            'status' => 'shipped',
            'created_at' => '2026-07-31 10:40:00',
            'items' => [
                [2, 'Nebula Book Pro 16', 99990, 1],
            ],
        ],
        [
            'number' => 'TM-2026-1012',
            'user_id' => $users['user@test.local'],
            'full_name' => 'Анна Смирнова',
            'phone' => '+7 913 555-21-84',
            'email' => 'user@test.local',
            'address' => 'г. Новосибирск, ул. Ленина, д. 42, кв. 17',
            'delivery' => 'express',
            'payment' => 'card_courier',
            'comment' => 'Домофон 42В.',
            'subtotal' => 15980,
            'delivery_cost' => 990,
            'total' => 16970,
            'status' => 'processing',
            'created_at' => '2026-10-12 18:25:00',
            'items' => [
                [16, 'Titan Mechanical TKL', 9990, 1],
                [17, 'Titan Mouse Pro', 5990, 1],
            ],
        ],
    ];

    $orderStmt = $pdo->prepare(
        'INSERT INTO orders
         (order_number, user_id, full_name, phone, email, address, delivery_method, payment_method, comment, subtotal, delivery_cost, total, status, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $itemStmt = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    foreach ($orders as $order) {
        $orderStmt->execute([
            $order['number'],
            $order['user_id'],
            $order['full_name'],
            $order['phone'],
            $order['email'],
            $order['address'],
            $order['delivery'],
            $order['payment'],
            $order['comment'],
            $order['subtotal'],
            $order['delivery_cost'],
            $order['total'],
            $order['status'],
            $order['created_at'],
        ]);
        $orderId = (int) $pdo->lastInsertId();

        foreach ($order['items'] as $item) {
            [$productId, $name, $unitPrice, $quantity] = $item;
            $itemStmt->execute([
                $orderId,
                $productId,
                $name,
                $unitPrice,
                $quantity,
                $unitPrice * $quantity,
            ]);
        }
    }
}
