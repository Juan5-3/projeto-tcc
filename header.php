<?php
// ============================================================
// ShopBR — Header reutilizável
// Variáveis opcionais antes do include:
//   $headerSearchValue  — preenche o campo de busca
//   $headerActivePage   — 'inicio' | 'categorias' | 'ofertas'
//   $headerBase         — '' (raiz) ou '../' (subpastas)
// ============================================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
iniciarSessao();

$usuario    = usuarioLogado();
$estaLogado = $usuario !== null;
$searchVal  = htmlspecialchars($headerSearchValue ?? '', ENT_QUOTES, 'UTF-8');
$activePage = $headerActivePage ?? '';
$base       = $headerBase ?? '';

// Conta itens no carrinho para o badge (apenas usuários logados)
$carrinhoQtd = 0;
if ($estaLogado) {
    try {
        $dbH = getDB();
        $stmtC = $dbH->prepare(
            'SELECT COALESCE(SUM(quantidade), 0) AS total FROM carrinho_itens WHERE usuario_id = ?'
        );
        $stmtC->execute([$usuario['id']]);
        $carrinhoQtd = (int) $stmtC->fetch()['total'];
    } catch (PDOException $e) {
        $carrinhoQtd = 0;
    }
}
?>
<!-- ══ HEADER ══ -->
<header class="site-header" role="banner">
    <div class="container header-inner">

        <a href="<?= $base ?>index.php" class="site-logo" aria-label="ShopBR — Página inicial">
            Shop<span>BR</span>
        </a>

        <!-- Barra de busca -->
        <form
            class="header-search"
            role="search"
            action="<?= $base ?>busca.php"
            method="get"
            id="header-search-form"
        >
            <label for="search-input" class="sr-only">Buscar produtos</label>
            <input
                type="search"
                id="search-input"
                name="q"
                value="<?= $searchVal ?>"
                placeholder="Buscar produtos..."
                aria-label="Campo de busca"
                autocomplete="off"
            >
            <button type="submit" class="header-search-btn" aria-label="Buscar">🔍</button>
        </form>

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
            <a href="<?= $base ?>index.php"
               <?= $activePage === 'inicio' ? 'aria-current="page"' : '' ?>>Início</a>

            <a href="<?= $base ?>index.php#categorias"
               <?= $activePage === 'categorias' ? 'aria-current="page"' : '' ?>>Categorias</a>

            <a href="<?= $base ?>index.php#ofertas"
               <?= $activePage === 'ofertas' ? 'aria-current="page"' : '' ?>>Ofertas</a>

            <?php if ($estaLogado): ?>

                <?php if ($usuario['tipo'] === 'vendedor'): ?>
                    <a href="<?= $base ?>dashboard-vendedor.php">🏪 Minha Loja</a>
                <?php else: ?>
                    <a href="<?= $base ?>cadastro-vendedor.php">Seja um Vendedor</a>

                    <!-- Ícone de carrinho (só compradores) -->
                    <a
                        href="<?= $base ?>carrinho.php"
                        class="header-cart-btn"
                        aria-label="Carrinho<?= $carrinhoQtd > 0 ? " — {$carrinhoQtd} " . ($carrinhoQtd === 1 ? 'item' : 'itens') : '' ?>"
                    >
                        🛒
                        <?php if ($carrinhoQtd > 0): ?>
                            <span class="cart-badge" id="cart-badge"><?= $carrinhoQtd ?></span>
                        <?php else: ?>
                            <span class="cart-badge cart-badge-hidden" id="cart-badge"></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <!-- Menu do usuário -->
                <div class="header-user-menu" id="header-user-menu">
                    <button
                        class="header-user-btn"
                        id="header-user-btn"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="user-dropdown"
                        type="button"
                    >
                        <span class="header-user-avatar" aria-hidden="true">
                            <?= mb_strtoupper(mb_substr($usuario['nome'], 0, 1)) ?>
                        </span>
                        <span class="header-user-name">
                            <?= htmlspecialchars(explode(' ', $usuario['nome'])[0], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="header-user-chevron" aria-hidden="true">▾</span>
                    </button>

                    <div class="user-dropdown" id="user-dropdown" role="menu" aria-label="Menu do usuário">
                        <div class="user-dropdown-info">
                            <strong><?= htmlspecialchars($usuario['nome'],  ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?></small>
                        </div>
                        <hr class="user-dropdown-divider">
                        <?php if ($usuario['tipo'] === 'vendedor'): ?>
                            <a href="<?= $base ?>dashboard-vendedor.php" class="user-dropdown-item" role="menuitem">
                                📊 Dashboard
                            </a>
                            <a href="<?= $base ?>perfil-vendedor.php" class="user-dropdown-item" role="menuitem">
                                ✏️ Editar Perfil
                            </a>
                        <?php else: ?>
                            <a href="<?= $base ?>carrinho.php" class="user-dropdown-item" role="menuitem">
                                🛒 Meu Carrinho
                            </a>
                            <a href="<?= $base ?>historico-pedidos.php" class="user-dropdown-item" role="menuitem">
                                📋 Meus Pedidos
                            </a>
                        <?php endif; ?>
                        <a href="<?= $base ?>api/auth/logout.php"
                           class="user-dropdown-item user-dropdown-logout" role="menuitem">
                            🚪 Sair
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <a href="<?= $base ?>cadastro-vendedor.php">Seja um Vendedor</a>
                <a href="<?= $base ?>login.php" class="btn btn-primary">Entrar</a>
            <?php endif; ?>
        </nav>

    </div>
</header>
