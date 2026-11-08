(function () {
    'use strict';

    const ready = (fn) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    };

    const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

    ready(function () {
        const navToggle = document.querySelector('[data-nav-toggle]');
        const mobileNav = document.querySelector('[data-mobile-nav]');
        if (navToggle && mobileNav) {
            navToggle.addEventListener('click', function () {
                const isOpen = mobileNav.classList.toggle('is-open');
                navToggle.classList.toggle('is-open', isOpen);
                navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        document.querySelectorAll('[data-flash-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                const flash = button.closest('.flash');
                if (flash) {
                    flash.remove();
                }
            });
        });

        document.querySelectorAll('[data-auto-submit]').forEach(function (select) {
            select.addEventListener('change', function () {
                if (select.form) {
                    select.form.submit();
                }
            });
        });

        document.querySelectorAll('[data-quantity]').forEach(function (wrapper) {
            const input = wrapper.querySelector('input[type="number"]');
            if (!input) {
                return;
            }
            const minus = wrapper.querySelector('[data-qty-minus]');
            const plus = wrapper.querySelector('[data-qty-plus]');
            const min = Number(input.min || 1);
            const max = Number(input.max || 99);

            if (minus) {
                minus.addEventListener('click', function () {
                    input.value = String(clamp((parseInt(input.value, 10) || min) - 1, min, max));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }
            if (plus) {
                plus.addEventListener('click', function () {
                    input.value = String(clamp((parseInt(input.value, 10) || min) + 1, min, max));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }
            input.addEventListener('change', function () {
                input.value = String(clamp(parseInt(input.value, 10) || min, min, max));
            });
        });

        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                const field = button.closest('.password-field');
                const input = field ? field.querySelector('[data-password]') : null;
                if (!input) {
                    return;
                }
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-label', show ? 'Скрыть пароль' : 'Показать пароль');
            });
        });

        const checkoutForm = document.querySelector('[data-checkout-form]');
        if (checkoutForm) {
            const subtotal = Number(checkoutForm.dataset.subtotal || 0);
            const deliveryTotal = checkoutForm.querySelector('[data-delivery-total]');
            const orderTotal = checkoutForm.querySelector('[data-order-total]');
            const formatter = new Intl.NumberFormat('ru-RU', { maximumFractionDigits: 0 });

            const updateTotals = function () {
                const selected = checkoutForm.querySelector('input[name="delivery_method"]:checked');
                const cost = selected ? Number(selected.dataset.deliveryCost || 0) : 0;
                if (deliveryTotal) {
                    deliveryTotal.textContent = cost === 0 ? 'Бесплатно' : formatter.format(cost) + ' ₽';
                }
                if (orderTotal) {
                    orderTotal.textContent = formatter.format(subtotal + cost) + ' ₽';
                }
            };

            checkoutForm.querySelectorAll('input[name="delivery_method"]').forEach(function (radio) {
                radio.addEventListener('change', updateTotals);
            });
            updateTotals();
        }

        const imageSelect = document.querySelector('[data-image-select]');
        const imagePreview = document.querySelector('[data-image-preview]');
        if (imageSelect && imagePreview) {
            imageSelect.addEventListener('change', function () {
                imagePreview.src = '/assets/images/products/' + imageSelect.value;
            });
        }

        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!window.confirm(form.dataset.confirm || 'Подтвердить действие?')) {
                    event.preventDefault();
                }
            });
        });

        const adminToggle = document.querySelector('[data-admin-toggle]');
        const adminSidebar = document.querySelector('[data-admin-sidebar]');
        const adminOverlay = document.querySelector('[data-admin-overlay]');
        const closeAdminMenu = function () {
            if (adminSidebar) {
                adminSidebar.classList.remove('is-open');
            }
            if (adminOverlay) {
                adminOverlay.classList.remove('is-open');
            }
            if (adminToggle) {
                adminToggle.setAttribute('aria-expanded', 'false');
            }
            document.body.classList.remove('no-scroll');
        };

        if (adminToggle && adminSidebar) {
            adminToggle.addEventListener('click', function () {
                const isOpen = adminSidebar.classList.toggle('is-open');
                adminToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                if (adminOverlay) {
                    adminOverlay.classList.toggle('is-open', isOpen);
                }
                document.body.classList.toggle('no-scroll', isOpen);
            });
        }
        if (adminOverlay) {
            adminOverlay.addEventListener('click', closeAdminMenu);
        }
        document.querySelectorAll('.admin-nav a').forEach(function (link) {
            link.addEventListener('click', closeAdminMenu);
        });

        document.querySelectorAll('a[href^="#"]').forEach(function (link) {
            link.addEventListener('click', function () {
                if (mobileNav && mobileNav.classList.contains('is-open')) {
                    mobileNav.classList.remove('is-open');
                    if (navToggle) {
                        navToggle.classList.remove('is-open');
                        navToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        });
    });
})();
