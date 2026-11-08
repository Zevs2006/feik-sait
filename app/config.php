<?php

const SITE_NAME = 'ВОЛЬТ';
const SITE_TAGLINE = 'электроника и техника';
const SITE_EMAIL = 'hello@volt.local';
const SITE_PHONE = '+7 383 555-08-80';
const CURRENCY = '₽';
const FREE_SHIPPING_THRESHOLD = 50000;
const PRODUCTS_PER_PAGE = 9;

const DELIVERY_METHODS = [
    'courier' => 'Курьером',
    'express' => 'Экспресс-доставка',
    'pickup' => 'Самовывоз',
];

const PAYMENT_METHODS = [
    'card_online' => 'Картой онлайн',
    'card_courier' => 'Картой при получении',
    'cash' => 'Наличными при получении',
];

const ORDER_STATUSES = [
    'new' => 'Новый',
    'processing' => 'В обработке',
    'shipped' => 'Отправлен',
    'completed' => 'Выполнен',
    'cancelled' => 'Отменён',
];
