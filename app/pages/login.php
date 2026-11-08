<?php
if (is_logged_in()) {
    redirect(is_admin() ? '/admin/index.php' : '/account.php');
}

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный адрес электронной почты.';
    }
    if ($password === '') {
        $errors[] = 'Введите пароль.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            unset($GLOBALS['_current_user']);
            flash('success', 'Добро пожаловать, ' . $user['full_name'] . '!');
            if ($user['role'] === 'admin') {
                redirect('/admin/index.php');
            }
            redirect('/account.php');
        }

        $errors[] = 'Неверный email или пароль.';
    }
}

$pageTitle = 'Вход в аккаунт — ВОЛЬТ';
$bodyClass = 'page-auth';
include APP_ROOT . '/app/views/header.php';
?>
<section class="auth section">
    <div class="container">
        <div class="auth-layout">
            <div class="auth-aside">
                <div class="eyebrow eyebrow--light"><span></span> Личный кабинет</div>
                <h1>Всё ваше —<br>в одном месте</h1>
                <p>История заказов, контактные данные и быстрый доступ к оформлению покупок.</p>
                <ul class="auth-benefits">
                    <li><span>01</span> Отслеживайте статус заказов</li>
                    <li><span>02</span> Сохраняйте адреса доставки</li>
                    <li><span>03</span> Оформляйте покупки быстрее</li>
                </ul>
                <div class="auth-aside__art">
                    <img src="<?= asset('images/products/audio-studiomax.svg') ?>" alt="" width="800" height="600" loading="lazy">
                </div>
            </div>
            <div class="auth-panel">
                <div class="auth-panel__head">
                    <h2>Вход в аккаунт</h2>
                    <p>Ещё нет аккаунта? <a href="/register.php">Зарегистрируйтесь</a></p>
                </div>

                <?php if ($errors): ?>
                    <div class="form-errors" role="alert">
                        <strong>Не удалось войти:</strong>
                        <ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <form class="auth-form" action="/login.php" method="post">
                    <?= csrf_field() ?>
                    <label class="field">
                        <span>Электронная почта</span>
                        <input type="email" name="email" value="<?= e($email) ?>" placeholder="name@example.ru" autocomplete="email" required autofocus>
                    </label>
                    <label class="field">
                        <span>Пароль</span>
                        <div class="password-field">
                            <input type="password" name="password" placeholder="Введите пароль" autocomplete="current-password" required data-password>
                            <button type="button" data-toggle-password aria-label="Показать пароль">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.6"/></svg>
                            </button>
                        </div>
                    </label>
                    <button class="button button--lg button--full" type="submit">Войти</button>
                </form>

                <div class="auth-panel__foot">
                    <span>Входя, вы принимаете условия сервиса и политику конфиденциальности.</span>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include APP_ROOT . '/app/views/footer.php'; ?>
