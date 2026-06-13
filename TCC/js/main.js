/**
 * ShopBR — main.js
 * Handles:
 *   1. Mobile hamburger menu toggle
 *   2. Product comparison bar logic (categoria.php)
 *      - sessionStorage key: 'compareList'
 *      - Max 3 products
 */

(function () {
    'use strict';

    const STORAGE_KEY = 'compareList';
    const MAX_COMPARE = 3;

    /* ────────────────────────────────────────────
       Hamburger menu
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
       sessionStorage helpers
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
       Comparison bar rendering
    ──────────────────────────────────────────── */
    function updateComparisonBar() {
        var bar       = document.getElementById('comparison-bar');
        var itemsEl   = document.getElementById('comparison-bar-items');
        var compareBtn = document.getElementById('compare-btn');

        if (!bar || !itemsEl) return;

        var list = getCompareList();

        // Show/hide bar
        if (list.length >= 2) {
            bar.classList.add('visible');
        } else {
            bar.classList.remove('visible');
        }

        // Build items HTML
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

        // Update compare button state
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

        // Uncheck the corresponding checkbox on the page
        var checkbox = document.querySelector(
            '.compare-checkbox[data-id="' + id + '"]'
        );
        if (checkbox) {
            checkbox.checked = false;
        }

        updateComparisonBar();
    }

    /* ────────────────────────────────────────────
       Checkbox event handling
    ──────────────────────────────────────────── */
    function initComparisonCheckboxes() {
        var checkboxes = document.querySelectorAll('.compare-checkbox');
        if (!checkboxes.length) return;

        // Restore checkbox states from sessionStorage
        var list = getCompareList();
        checkboxes.forEach(function (cb) {
            var idx = findProductById(list, cb.dataset.id);
            cb.checked = idx !== -1;
        });

        // Attach change listeners
        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                var list = getCompareList();

                if (cb.checked) {
                    // Check max
                    if (list.length >= MAX_COMPARE) {
                        cb.checked = false;
                        alert(
                            'Você pode comparar no máximo ' + MAX_COMPARE + ' produtos.\n' +
                            'Remova um produto antes de adicionar outro.'
                        );
                        return;
                    }

                    // Add to list
                    var product = {
                        id:       cb.dataset.id,
                        name:     cb.dataset.name,
                        price:    cb.dataset.price,
                        category: cb.dataset.category,
                        rating:   cb.dataset.rating,
                        image:    cb.dataset.image,
                    };
                    list.push(product);
                } else {
                    // Remove from list
                    var index = findProductById(list, cb.dataset.id);
                    if (index !== -1) list.splice(index, 1);
                }

                saveCompareList(list);
                updateComparisonBar();
            });
        });

        // Initial bar render
        updateComparisonBar();
    }

    /* ────────────────────────────────────────────
       Compare button — navigate to comparar.php
    ──────────────────────────────────────────── */
    function initCompareButton() {
        var compareBtn = document.getElementById('compare-btn');
        if (!compareBtn) return;

        compareBtn.addEventListener('click', function () {
            var list = getCompareList();
            if (list.length >= 2) {
                window.location.href = 'comparar.php';
            }
        });
    }

    /* ────────────────────────────────────────────
       Clear all button
    ──────────────────────────────────────────── */
    function initClearButton() {
        var clearBtn = document.getElementById('clear-compare-btn');
        if (!clearBtn) return;

        clearBtn.addEventListener('click', function () {
            saveCompareList([]);

            // Uncheck all checkboxes
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
        initComparisonCheckboxes();
        initCompareButton();
        initClearButton();
    });

}());
