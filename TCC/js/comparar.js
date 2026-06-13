/**
 * ShopBR — comparar.js
 * Handles:
 *   - Reading compareList from sessionStorage
 *   - Building comparison table dynamically
 *   - Removing individual products from comparison
 *   - Showing "no products" message when < 2 items
 */

(function () {
    'use strict';

    var STORAGE_KEY = 'compareList';

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
       Star rendering helper
    ──────────────────────────────────────────── */
    function renderStars(rating) {
        var r     = parseFloat(rating) || 0;
        var full  = Math.floor(r);
        var half  = (r - full) >= 0.5 ? 1 : 0;
        var empty = 5 - full - half;
        return '★'.repeat(full) + (half ? '½' : '') + '☆'.repeat(empty);
    }

    /* ────────────────────────────────────────────
       Escape HTML
    ──────────────────────────────────────────── */
    function esc(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(String(str)));
        return div.innerHTML;
    }

    /* ────────────────────────────────────────────
       Render "no products" message
    ──────────────────────────────────────────── */
    function renderEmpty(container) {
        container.innerHTML = [
            '<div class="compare-empty" role="status" aria-live="polite">',
            '  <p>Selecione ao menos 2 produtos para comparar.</p>',
            '  <a href="index.php" class="btn btn-primary">Ir para a página inicial</a>',
            '</div>'
        ].join('');
    }

    /* ────────────────────────────────────────────
       Render comparison table
    ──────────────────────────────────────────── */
    function renderTable(container, list) {
        // Build table HTML
        var colsHtml = '';
        list.forEach(function (product) {
            colsHtml += '<th scope="col">' + esc(product.name) + '</th>';
        });

        // Remove row
        var removeCells = '';
        list.forEach(function (product) {
            removeCells +=
                '<td>' +
                '<button class="remove-btn" data-id="' + esc(product.id) + '" type="button" aria-label="Remover ' + esc(product.name) + ' da comparação">' +
                '&times; Remover' +
                '</button>' +
                '</td>';
        });

        // Image row
        var imageCells = '';
        list.forEach(function (product) {
            imageCells += '<td class="compare-image-cell" aria-hidden="true">' + esc(product.image) + '</td>';
        });

        // Name row
        var nameCells = '';
        list.forEach(function (product) {
            nameCells += '<td>' + esc(product.name) + '</td>';
        });

        // Price row
        var priceCells = '';
        list.forEach(function (product) {
            priceCells += '<td><strong style="color:var(--color-primary);">' + esc(product.price) + '</strong></td>';
        });

        // Category row
        var categoryCells = '';
        list.forEach(function (product) {
            categoryCells += '<td>' + esc(product.category) + '</td>';
        });

        // Rating row
        var ratingCells = '';
        list.forEach(function (product) {
            var stars = renderStars(product.rating);
            ratingCells +=
                '<td>' +
                '<span class="compare-rating" aria-hidden="true">' + esc(stars) + '</span>' +
                '<span class="sr-only">' + esc(product.rating) + ' de 5</span>' +
                ' (' + esc(product.rating) + ')' +
                '</td>';
        });

        var html = [
            '<div class="compare-table-wrapper" role="region" aria-label="Tabela de comparação de produtos">',
            '  <table class="compare-table" aria-label="Comparação de ' + list.length + ' produtos">',
            '    <thead>',
            '      <tr>',
            '        <th scope="col" class="row-header">Produto</th>',
            colsHtml,
            '      </tr>',
            '    </thead>',
            '    <tbody>',
            '      <tr>',
            '        <th scope="row" class="row-header">Imagem</th>',
            imageCells,
            '      </tr>',
            '      <tr>',
            '        <th scope="row" class="row-header">Nome</th>',
            nameCells,
            '      </tr>',
            '      <tr>',
            '        <th scope="row" class="row-header">Preço</th>',
            priceCells,
            '      </tr>',
            '      <tr>',
            '        <th scope="row" class="row-header">Categoria</th>',
            categoryCells,
            '      </tr>',
            '      <tr>',
            '        <th scope="row" class="row-header">Avaliação</th>',
            ratingCells,
            '      </tr>',
            '      <tr>',
            '        <th scope="row" class="row-header">Ações</th>',
            removeCells,
            '      </tr>',
            '    </tbody>',
            '  </table>',
            '</div>'
        ].join('');

        container.innerHTML = html;

        // Attach remove button listeners
        container.querySelectorAll('.remove-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id   = btn.getAttribute('data-id');
                var list = getCompareList();
                var idx  = findProductById(list, id);

                if (idx !== -1) {
                    list.splice(idx, 1);
                    saveCompareList(list);
                }

                render(container);
            });
        });
    }

    /* ────────────────────────────────────────────
       Main render dispatcher
    ──────────────────────────────────────────── */
    function render(container) {
        var list = getCompareList();

        if (list.length < 2) {
            renderEmpty(container);
        } else {
            renderTable(container, list);
        }
    }

    /* ────────────────────────────────────────────
       Init
    ──────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        var container = document.getElementById('compare-content');
        if (!container) return;

        render(container);

        // Hamburger menu (reuse same pattern)
        var hamburger = document.getElementById('hamburger-btn');
        var nav       = document.getElementById('main-nav');
        if (hamburger && nav) {
            hamburger.addEventListener('click', function () {
                var isOpen = nav.classList.toggle('open');
                hamburger.classList.toggle('open', isOpen);
                hamburger.setAttribute('aria-expanded', String(isOpen));
            });
        }
    });

}());
