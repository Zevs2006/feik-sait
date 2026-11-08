<?php
if (is_logged_in()) {
    redirect(is_admin() ? '/admin/index.php' : '/account.php');
}

$form = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $form['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
    $form['email'] = trim((string) ($_POST['email'] ?? ''));
    $form['phone'] = trim((string) ($_POST['phone'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if (mb_strlen($form['full_name']) < 3) {
        $errors[] = 'Укажите имя и фамилию.';
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный адрес электронной почты.';
    }
    if (mb_strlen($password) < 6) {
        $errors[] = 'Пароль должен содержать не менее 8 символов.';
    }
    if ($password !== $passwordConfirm) {
        $errors[] = 'Пароли не совпадают.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$form['email']]);
        if ((int) $stmt->fetchColumn() > 0) {
            $errors[] = 'Пользователь с таким email уже зарегистрирован.';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare(
            'INSERT INTO users (full_name, email, phone, address, password_hash, role)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $form['full_name'],
            $form['email'],
            $form['phone'],
            '',
            password_hash($password, PASSWORD_DEFAULT),
            'customer',
        ]);
        $userId = (int) db()->lastInsertId();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        unset($GLOBALS['_current_user']);
        flash('success', 'Аккаунт создан. Добро пожаловать в ВОЛЬТ!');
        redirect('/account.php');
    }
}

$pageTitle = 'Регистрация — ВОЛЬТ';
$bodyClass = 'page-auth';
include APP_ROOT . '/app/views/header.php';
?>
<section class="auth section">
    <div class="container">
        <div class="auth-layout auth-layout--register">
            <div class="auth-aside">
                <div class="eyebrow eyebrow--light"><span></span> Новый клиент</div>
                <h1>Техника ближе,<br>когда вы с нами</h1>
                <p>Создайте аккаунт, чтобы сохранять данные и возвращаться к своим заказам в один клик.</p>
                <ul class="auth-benefits">
                    <li><span>01</span> История покупок и статусы</li>
                    <li><span>02</span> Быстрое оформление заказа</li>
                    <li><span>03</span> Персональные предложения</li>
                </ul>
                <div class="auth-aside__art auth-aside__art--phone">
                    <img src="<?= asset('images/products/phone-nova-pro.svg') ?>" alt="" width="800" height="600" loading="lazy">
                </div>
            </div>
            <div class="auth-panel">
                <div class="auth-panel__head">
                    <h2>Создать аккаунт</h2>
                    <p>Уже зарегистрированы? <a href="/login.php">Войдите</a></p>
                </div>

                <?php if ($errors): ?>
                    <div class="form-errors" role="alert">
                        <strong>Проверьте данные:</strong>
                        <ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <form class="auth-form" action="/register.php" method="post">
                    <?= csrf_field() ?>
                    <label class="field">
                        <span>Имя и фамилия</span>
                        <input type="text" name="full_name" value="<?= e($form['full_name']) ?>" placeholder="Иван Иванов" autocomplete="name" required>
                    </label>
                    <label class="field">
                        <span>Электронная почта</span>
                        <input type="email" name="email" value="<?= e($form['email']) ?>" placeholder="name@example.ru" autocomplete="email" required>
                    </label>
                    <label class="field">
                        <span>Телефон</span>
                        <input type="tel" name="phone" value="<?= e($form['phone']) ?>" placeholder="+7 999 123-45-67" autocomplete="tel">
                    </label>
                    <div class="form-grid">
                        <label class="field">
                            <span>Пароль</span>
                            <div class="password-field">
                                <input type="password" name="password" placeholder="Минимум 8 символов" autocomplete="new-password" required data-password>
                                <button type="button" data-toggle-password aria-label="Показать пароль">
                                    <svg viewBox="0 0 24 24" fill="none"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.6"/></svg>
                                </button>
                            </div>
                            <small>Не менее 8 символов</small>
                        </label>
                        <label class="field">
                            <span>Повторите пароль</span>
                            <input type="password" name="password_confirm" placeholder="Ещё раз" autocomplete="new-password" required>
                        </label>
                    </div>
                    <button class="button button--lg button--full" type="submit">Зарегистрироваться</button>
                </form>

                <div class="auth-panel__foot">
                    <span>Создавая аккаунт, вы принимаете условия сервиса и политику конфиденциальности.</span>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include APP_ROOT . '/app/views/footer.php'; ?>
