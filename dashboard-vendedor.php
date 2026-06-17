<?php
// ShopBR - Seller Dashboard
$products = [
    ['name' => 'Smartphone Galaxy S23',   'category' => 'Eletrônicos',  'price' => 'R$ 2.999,90', 'status' => 'active'],
    ['name' => 'Tênis Esportivo Pro',      'category' => 'Esportes',     'price' => 'R$ 349,90',   'status' => 'active'],
    ['name' => 'Notebook Ultrabook 15"',  'category' => 'Informática',  'price' => 'R$ 4.599,90', 'status' => 'inactive'],
    ['name' => 'Cafeteira Espresso 1200W','category' => 'Casa e Jardim','price' => 'R$ 299,90',   'status' => 'active'],
    ['name' => 'Livro: Clean Code',        'category' => 'Livros',       'price' => 'R$ 89,90',    'status' => 'inactive'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopBR — Dashboard do Vendedor</title>
    <meta name="description" content="Painel de controle para vendedores do ShopBR.">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ══ HEADER ══ -->
<header class="site-header" role="banner">
    <div class="container header-inner">
        <a href="index.php" class="site-logo" aria-label="ShopBR — Página inicial">Shop<span>BR</span></a>

        <span class="store-badge" aria-label="Loja ativa: Loja do João">
            <span aria-hidden="true">🏪</span>
            Loja do João
        </span>

        <button
            class="hamburger"
            id="hamburger-btn"
            aria-label="Abrir menu de navegação"
            aria-expanded="false"
            aria-controls="main-nav"
        >
            <span></span><span></span><span></span>
        </button>

        <nav class="header-nav" id="main-nav" role="navigation" aria-label="Navegação principal">
            <a href="index.php">Voltar ao Site</a>
            <a href="#">Produtos</a>
            <a href="#">Pedidos</a>
            <a href="#">Configurações</a>
        </nav>
    </div>
</header>

<main>
    <section class="dashboard-page" aria-labelledby="dashboard-heading">
        <div class="container">

            <!-- Dashboard header -->
            <div class="dashboard-header">
                <div class="dashboard-title">
                    <h1 id="dashboard-heading">Dashboard</h1>
                    <p>Bem-vindo de volta, <strong>Loja do João</strong>! Aqui está o resumo da sua loja.</p>
                </div>
                <a href="cadastro-vendedor.php" class="btn btn-secondary btn-sm">
                    Editar Perfil
                </a>
            </div>

            <!-- ── Metric Cards ── -->
            <section aria-labelledby="metrics-heading">
                <h2 class="sr-only" id="metrics-heading">Métricas da Loja</h2>
                <div class="metrics-grid">
                    <div class="metric-card" role="article" aria-label="Total de produtos: 42">
                        <div class="metric-icon" aria-hidden="true">📦</div>
                        <div class="metric-label">Total Produtos</div>
                        <div class="metric-value">42</div>
                    </div>
                    <div class="metric-card" role="article" aria-label="Total de pedidos: 128">
                        <div class="metric-icon" aria-hidden="true">🛒</div>
                        <div class="metric-label">Total Pedidos</div>
                        <div class="metric-value">128</div>
                    </div>
                    <div class="metric-card" role="article" aria-label="Pedidos pendentes: 7">
                        <div class="metric-icon" aria-hidden="true">⏳</div>
                        <div class="metric-label">Pedidos Pendentes</div>
                        <div class="metric-value">7</div>
                    </div>
                    <div class="metric-card" role="article" aria-label="Visualizações: 3.240">
                        <div class="metric-icon" aria-hidden="true">👁️</div>
                        <div class="metric-label">Visualizações</div>
                        <div class="metric-value">3.240</div>
                    </div>
                </div>
            </section>

            <!-- ── Chart ── -->
            <section class="dashboard-chart" aria-labelledby="chart-heading">
                <h2 id="chart-heading">Pedidos por Semana</h2>
                <div class="chart-container">
                    <canvas id="ordersChart" role="img" aria-label="Gráfico de barras mostrando pedidos por semana nas últimas 8 semanas"></canvas>
                </div>
            </section>

            <!-- ── Products Table ── -->
            <section class="dashboard-products" aria-labelledby="products-table-heading">
                <h2 id="products-table-heading">Meus Produtos</h2>
                <div class="products-table-wrapper">
                    <table class="products-table" aria-label="Lista de produtos da loja">
                        <thead>
                            <tr>
                                <th scope="col">Nome</th>
                                <th scope="col">Categoria</th>
                                <th scope="col">Preço</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><strong><?= htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td>
                                        <?php if ($product['status'] === 'active'): ?>
                                            <span class="status-badge status-active" aria-label="Status: Ativo">
                                                ✓ Ativo
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-inactive" aria-label="Status: Inativo">
                                                ✕ Inativo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </section>
</main>

<!-- ══ FOOTER ══ -->
<footer class="site-footer" role="contentinfo" style="padding:var(--spacing-lg) 0;">
    <div class="container">
        <div class="footer-bottom" style="border:none;padding:0;">
            <p>&copy; <?= date('Y') ?> ShopBR. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    'use strict';

    // Hamburger menu toggle (inline for dashboard — no main.js dependency needed)
    const hamburger = document.getElementById('hamburger-btn');
    const nav       = document.getElementById('main-nav');

    if (hamburger && nav) {
        hamburger.addEventListener('click', function () {
            const isOpen = nav.classList.toggle('open');
            hamburger.classList.toggle('open', isOpen);
            hamburger.setAttribute('aria-expanded', String(isOpen));
        });
    }

    // Orders chart
    const ctx = document.getElementById('ordersChart');
    if (!ctx) return;

    const labels = ['Sem. 1', 'Sem. 2', 'Sem. 3', 'Sem. 4', 'Sem. 5', 'Sem. 6', 'Sem. 7', 'Sem. 8'];
    const data   = [12, 19, 8, 25, 17, 22, 14, 28];

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pedidos',
                data: data,
                backgroundColor: 'rgba(255, 107, 0, 0.75)',
                borderColor: '#FF6B00',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            return ' ' + ctx.parsed.y + ' pedidos';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: "'Segoe UI', Roboto, sans-serif", size: 12 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: {
                        font: { family: "'Segoe UI', Roboto, sans-serif", size: 12 },
                        stepSize: 5
                    }
                }
            }
        }
    });
}());
</script>
</body>
</html>
