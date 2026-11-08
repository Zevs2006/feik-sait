<?php
$infoPages = [
    'delivery' => [
        'eyebrow' => 'Сервис',
        'title' => 'Доставка и оплата',
        'lead' => 'Отправляем заказы каждый день. Выберите удобный способ получения и оплаты.',
    ],
    'warranty' => [
        'eyebrow' => 'Сервис',
        'title' => 'Гарантия и возврат',
        'lead' => 'Следим за качеством техники и помогаем, если что-то пошло не так.',
    ],
    'contacts' => [
        'eyebrow' => 'О магазине',
        'title' => 'Контакты',
        'lead' => 'Ответим на вопросы, поможем с выбором и подскажем статус заказа.',
    ],
    'about' => [
        'eyebrow' => 'О магазине',
        'title' => 'О ВОЛЬТ',
        'lead' => 'Небольшая команда, которая хорошо разбирается в технике и любит своё дело.',
    ],
];
$infoKey = (string) ($_GET['page'] ?? 'delivery');
if (!isset($infoPages[$infoKey])) {
    $infoKey = 'delivery';
}
$info = $infoPages[$infoKey];
$pageTitle = $info['title'] . ' — ВОЛЬТ';
$bodyClass = 'page-info';
include APP_ROOT . '/app/views/header.php';
?>
<div class="page-head">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="/">Главная</a><span>/</span><span><?= e($info['title']) ?></span>
        </nav>
        <div class="page-head__row">
            <div>
                <div class="eyebrow"><span></span> <?= e($info['eyebrow']) ?></div>
                <h1><?= e($info['title']) ?></h1>
                <p><?= e($info['lead']) ?></p>
            </div>
        </div>
    </div>
</div>

<section class="info-page section section--flush">
    <div class="container info-page__layout">
        <aside class="info-nav">
            <a class="<?= $infoKey === 'delivery' ? 'is-active' : '' ?>" href="/info.php?page=delivery">Доставка и оплата</a>
            <a class="<?= $infoKey === 'warranty' ? 'is-active' : '' ?>" href="/info.php?page=warranty">Гарантия и возврат</a>
            <a class="<?= $infoKey === 'contacts' ? 'is-active' : '' ?>" href="/info.php?page=contacts">Контакты</a>
            <a class="<?= $infoKey === 'about' ? 'is-active' : '' ?>" href="/info.php?page=about">О магазине</a>
            <div class="info-nav__help">
                <span>Есть вопрос?</span>
                <a href="tel:+73835550880"><?= e(SITE_PHONE) ?></a>
                <small>Ежедневно 09:00–21:00</small>
            </div>
        </aside>

        <div class="info-content">
            <?php if ($infoKey === 'delivery'): ?>
                <div class="info-block">
                    <h2>Способы доставки</h2>
                    <div class="info-cards">
                        <article><span>01</span><h3>Курьером по городу</h3><p>1–2 рабочих дня. Стоимость — 590 ₽, бесплатно при заказе от 50 000 ₽.</p></article>
                        <article><span>02</span><h3>Экспресс-доставка</h3><p>В течение дня при оформлении до 13:00. Стоимость — 990 ₽.</p></article>
                        <article><span>03</span><h3>Самовывоз</h3><p>Бесплатно. Красный проспект, 25, ежедневно с 09:00 до 21:00.</p></article>
                        <article><span>04</span><h3>По России</h3><p>Транспортной компанией за 2–7 дней. Стоимость рассчитывает менеджер.</p></article>
                    </div>
                </div>
                <div class="info-block">
                    <h2>Оплата</h2>
                    <p>Принимаем банковские карты МИР, Visa и Mastercard онлайн, картой при получении, а также наличными курьеру. Все платежи проходят через защищённое соединение.</p>
                    <div class="payments payments--large"><span>МИР</span><span>VISA</span><span>Mastercard</span></div>
                </div>
                <div class="info-block info-block--note">
                    <h2>Что важно знать</h2>
                    <ul class="check-list">
                        <li>Заказы, оформленные до 15:00, отправляем в тот же день.</li>
                        <li>Курьер позвонит за час до прибытия.</li>
                        <li>При получении проверьте комплектность и внешний вид техники.</li>
                    </ul>
                </div>
            <?php elseif ($infoKey === 'warranty'): ?>
                <div class="info-block">
                    <h2>Гарантия</h2>
                    <p>На всю технику действует официальная гарантия производителя от 1 до 2 лет. Гарантийный талон и чек хранятся в электронном виде — мы отправим их на вашу почту после покупки.</p>
                    <p>Гарантия не распространяется на механические повреждения, следы жидкости и вмешательство в конструкцию устройства.</p>
                </div>
                <div class="info-block">
                    <h2>Возврат в течение 14 дней</h2>
                    <p>Если товар не подошёл, его можно вернуть в течение 14 дней с момента получения. Техника должна быть в исходной упаковке, без следов использования и с полным комплектом аксессуаров.</p>
                    <ul class="check-list">
                        <li>Оставьте заявку на возврат по телефону или почте.</li>
                        <li>Передайте товар курьеру или привезите в магазин.</li>
                        <li>Деньги вернём на карту в течение 3–10 рабочих дней.</li>
                    </ul>
                </div>
                <div class="info-block info-block--note">
                    <h2>Сервис</h2>
                    <p>Поможем настроить устройство, обновить программное обеспечение и оформить обращение в авторизованный сервисный центр.</p>
                    <a class="button" href="/info.php?page=contacts">Связаться с нами</a>
                </div>
            <?php elseif ($infoKey === 'contacts'): ?>
                <div class="info-block">
                    <h2>Связаться с магазином</h2>
                    <div class="contact-grid">
                        <a class="contact-card" href="tel:+73835550880"><span>Телефон</span><strong><?= e(SITE_PHONE) ?></strong><small>Ежедневно 09:00–21:00</small></a>
                        <a class="contact-card" href="mailto:<?= e(SITE_EMAIL) ?>"><span>Почта</span><strong><?= e(SITE_EMAIL) ?></strong><small>Ответим в течение рабочего дня</small></a>
                        <div class="contact-card"><span>Магазин</span><strong>Красный проспект, 25</strong><small>Новосибирск, 1 этаж</small></div>
                        <div class="contact-card"><span>Реквизиты</span><strong>ООО «ВОЛЬТ»</strong><small>ИНН 5400000000</small></div>
                    </div>
                </div>
                <div class="info-block">
                    <h2>Как нас найти</h2>
                    <div class="map-placeholder">
                        <span class="map-placeholder__pin"><svg viewBox="0 0 24 24" fill="none"><path d="M12 21s7-6.1 7-11a7 7 0 1 0-14 0c0 4.9 7 11 7 11Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.3" stroke="currentColor" stroke-width="1.7"/></svg></span>
                        <strong>ВОЛЬТ на Красном проспекте</strong>
                        <span>5 минут от станции метро «Площадь Ленина»</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="info-block">
                    <h2>Техника с человеческим подходом</h2>
                    <p>ВОЛЬТ — локальный магазин электроники в Новосибирске. Мы отбираем устройства, которые сами используем каждый день, и не продаём то, в чём не уверены.</p>
                    <p>В команде — инженеры и консультанты, которые помогут сравнить характеристики, настроить технику и разобраться с гарантией без лишних формальностей.</p>
                </div>
                <div class="about-stats">
                    <div><strong>8</strong><span>лет на рынке</span></div>
                    <div><strong>24</strong><span>товара в каталоге</span></div>
                    <div><strong>4,9</strong><span>средняя оценка</span></div>
                    <div><strong>2 года</strong><span>гарантия</span></div>
                </div>
                <div class="info-block">
                    <h2>Наши принципы</h2>
                    <ul class="check-list">
                        <li>Показываем реальные характеристики и честные цены.</li>
                        <li>Помогаем выбрать под задачу, а не под чек.</li>
                        <li>Остаёмся на связи после покупки.</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include APP_ROOT . '/app/views/footer.php'; ?>
