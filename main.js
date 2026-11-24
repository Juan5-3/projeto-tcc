/**
 * ShopBR — main.js
 * Handles:
 *   1. Mobile hamburger menu toggle
 *   2. User account dropdown (header)
 *   3. Product comparison bar logic (categoria.php)
 *      - sessionStorage key: 'compareList'
 *      - Max 3 products
 */

(function () {
    'use strict';

    const STORAGE_KEY = 'compareList';
    const MAX_COMPARE = 3;

    /* ────────────────────────────────────────────
       1. Hamburger menu
    ──────────────────────────────────────────── */
    function initHamburger() {
        var btn = document.getElementById('hamburger-btn');
        var nav = document.getElementById('main-nav');
        if (!btn || !nav) return;

        btn.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('open');
            btn.classList.toggle('open', isOpen);
            btn.setAttribute('aria-expanded', String(isOpen));
        });

        // Close menu when a nav link is clicked (mobile UX)
        nav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                nav.classList.remove('open');
                btn.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');
            });
        });
    }

    /* ────────────────────────────────────────────
       2. User account dropdown
    ──────────────────────────────────────────── */
    function initUserDropdown() {
        var userBtn      = document.getElementById('header-user-btn');
        var userDropdown = document.getElementById('user-dropdown');
        if (!userBtn || !userDropdown) return;

        // Toggle ao clicar no botão
        userBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = userDropdown.classList.toggle('open');
            userBtn.setAttribute('aria-expanded', String(isOpen));
        });

        // Fecha ao clicar fora
        document.addEventListener('click', function (e) {
            if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('open');
                userBtn.setAttribute('aria-expanded', 'false');
            }
        });

        // Fecha com Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                userDropdown.classList.remove('open');
                userBtn.setAttribute('aria-expanded', 'false');
                userBtn.focus();
            }
        });
    }

    /* ────────────────────────────────────────────
       3. sessionStorage helpers
    ──────────────────────────────────────────── */
    function getCompareList() {
        try {
            var raw = sessionStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    }

    function saveCompareList(list) {
        try {
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(list));
        } catch (e) { /* ignore */ }
    }

    function findProductById(list, id) {
        var sid = String(id);
        for (var i = 0; i < list.length; i++) {
            if (String(list[i].id) === sid) return i;
        }
        return -1;
    }

    /* ────────────────────────────────────────────
       4. Comparison bar rendering
    ──────────────────────────────────────────── */
    function updateComparisonBar() {
        var bar        = document.getElementById('comparison-bar');
        var itemsEl    = document.getElementById('comparison-bar-items');
        var compareBtn = document.getElementById('compare-btn');

        if (!bar || !itemsEl) return;

        var list = getCompareList();

        if (list.length >= 2) {
            bar.classList.add('visible');
        } else {
            bar.classList.remove('visible');
        }

        itemsEl.innerHTML = '';
        list.forEach(function (product) {
            var item = document.createElement('div');
            item.className = 'comparison-bar-item';

            var nameSpan = document.createElement('span');
            nameSpan.textContent = product.name;
            nameSpan.title = product.name;

            var removeBtn = document.createElement('button');
            removeBtn.className = 'comparison-bar-item-remove';
            removeBtn.innerHTML = '&times;';
            removeBtn.setAttribute('aria-label', 'Remover ' + product.name + ' da comparação');
            removeBtn.setAttribute('type', 'button');
            removeBtn.addEventListener('click', function () {
                removeProductFromCompare(String(product.id));
            });

            item.appendChild(nameSpan);
            item.appendChild(removeBtn);
            itemsEl.appendChild(item);
        });

        if (compareBtn) {
            compareBtn.disabled = list.length < 2;
        }
    }

    function removeProductFromCompare(id) {
        var list  = getCompareList();
        var index = findProductById(list, id);

        if (index !== -1) {
            list.splice(index, 1);
            saveCompareList(list);
        }

        var checkbox = document.querySelector('.compare-checkbox[data-id="' + id + '"]');
        if (checkbox) checkbox.checked = false;

        updateComparisonBar();
    }

    /* ────────────────────────────────────────────
       5. Checkbox event handling
    ──────────────────────────────────────────── */
    function initComparisonCheckboxes() {
        var checkboxes = document.querySelectorAll('.compare-checkbox');
        if (!checkboxes.length) return;

        var list = getCompareList();
        checkboxes.forEach(function (cb) {
            cb.checked = findProductById(list, cb.dataset.id) !== -1;
        });

        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                var list = getCompareList();

                if (cb.checked) {
                    if (list.length >= MAX_COMPARE) {
                        cb.checked = false;
                        alert(
                            'Você pode comparar no máximo ' + MAX_COMPARE + ' produtos.\n' +
                            'Remova um produto antes de adicionar outro.'
                        );
                        return;
                    }
                    list.push({
                        id:            cb.dataset.id,
                        name:          cb.dataset.name,
                        price:         cb.dataset.price,
                        priceOriginal: cb.dataset.priceOriginal || '',
                        category:      cb.dataset.category,
                        rating:        cb.dataset.rating,
                        image:         cb.dataset.image,
                    });
                } else {
                    var index = findProductById(list, cb.dataset.id);
                    if (index !== -1) list.splice(index, 1);
                }

                saveCompareList(list);
                updateComparisonBar();
            });
        });

        updateComparisonBar();
    }

    /* ────────────────────────────────────────────
       6. Compare button — navigate to comparar.php
    ──────────────────────────────────────────── */
    function initCompareButton() {
        var compareBtn = document.getElementById('compare-btn');
        if (!compareBtn) return;

        compareBtn.addEventListener('click', function () {
            if (getCompareList().length >= 2) {
                window.location.href = 'comparar.php';
            }
        });
    }

    /* ────────────────────────────────────────────
       7. Clear all button
    ──────────────────────────────────────────── */
    function initClearButton() {
        var clearBtn = document.getElementById('clear-compare-btn');
        if (!clearBtn) return;

        clearBtn.addEventListener('click', function () {
            saveCompareList([]);
            document.querySelectorAll('.compare-checkbox').forEach(function (cb) {
                cb.checked = false;
            });
            updateComparisonBar();
        });
    }

    /* ────────────────────────────────────────────
       Init
    ──────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        initHamburger();
        initUserDropdown();
        initComparisonCheckboxes();
        initCompareButton();
        initClearButton();
    });

}());
