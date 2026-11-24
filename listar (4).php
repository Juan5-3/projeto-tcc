<?php
// ============================================================
// ShopBR — API: Listar produtos
// GET /api/produto/listar.php
// Parâmetros (query string):
//   ?categoria_slug=eletronicos   — filtra por slug da categoria
//   ?categoria_id=1               — filtra por id da categoria
//   ?vendedor_id=1                — filtra por vendedor (dashboard)
//   ?pagina=1&por_pagina=12       — paginação (padrão: pág. 1, 12 itens)
//   ?busca=texto                  — busca no nome e descrição
// ============================================================

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

// --- Parâmetros ---
$categoriaSlug = trim($_GET['categoria_slug'] ?? '');
$categoriaId   = filter_input(INPUT_GET, 'categoria_id',  FILTER_VALIDATE_INT);
$vendedorId    = filter_input(INPUT_GET, 'vendedor_id',   FILTER_VALIDATE_INT);
$busca         = trim($_GET['busca'] ?? '');
$pagina        = max(1, (int) ($_GET['pagina']    ?? 1));
$porPagina     = min(50, max(1, (int) ($_GET['por_pagina'] ?? 12))); // limite máximo: 50
$offset        = ($pagina - 1) * $porPagina;

try {
    $db = getDB();

    // Resolve slug → id se necessário
    if ($categoriaSlug !== '' && !$categoriaId) {
        $stmtSlug = $db->prepare('SELECT id FROM categorias WHERE slug = ? LIMIT 1');
        $stmtSlug->execute([$categoriaSlug]);
        $catRow = $stmtSlug->fetch();
        $categoriaId = $catRow ? (int) $catRow['id'] : 0;
    }

    // --- Monta query dinâmica ---
    $where  = ['p.ativo = 1'];
    $params = [];

    if ($categoriaId) {
        $where[]  = 'p.categoria_id = ?';
        $params[] = $categoriaId;
    }

    if ($vendedorId) {
        $where[]  = 'p.vendedor_id = ?';
        $params[] = $vendedorId;
    }

    if ($busca !== '') {
        $where[]  = '(p.nome LIKE ? OR p.descricao LIKE ?)';
        $termo    = '%' . $busca . '%';
        $params[] = $termo;
        $params[] = $termo;
    }

    $whereClause = implode(' AND ', $where);

    // Total de registros (para paginação)
    $stmtCount = $db->prepare("SELECT COUNT(*) as total FROM produtos p WHERE {$whereClause}");
    $stmtCount->execute($params);
    $total = (int) $stmtCount->fetch()['total'];

    // Produtos com dados da categoria e da loja
    $sql = "
        SELECT
            p.id,
            p.nome,
            p.descricao,
            p.preco,
            p.estoque,
            p.imagem_url,
            p.criado_em,
            c.id   AS categoria_id,
            c.nome AS categoria_nome,
            c.slug AS categoria_slug,
            c.icone AS categoria_icone,
            v.id        AS vendedor_id,
            v.nome_loja AS loja_nome
        FROM produtos p
        INNER JOIN categorias c ON c.id = p.categoria_id
        INNER JOIN vendedores  v ON v.id = p.vendedor_id
        WHERE {$whereClause}
        ORDER BY p.criado_em DESC
        LIMIT ? OFFSET ?
    ";

    $paramsPage   = array_merge($params, [$porPagina, $offset]);
    $stmtProdutos = $db->prepare($sql);
    $stmtProdutos->execute($paramsPage);
    $produtos = $stmtProdutos->fetchAll();

    // Converte tipos numéricos
    foreach ($produtos as &$p) {
        $p['id']           = (int)   $p['id'];
        $p['preco']        = (float) $p['preco'];
        $p['estoque']      = (int)   $p['estoque'];
        $p['categoria_id'] = (int)   $p['categoria_id'];
        $p['vendedor_id']  = (int)   $p['vendedor_id'];
    }
    unset($p);

    echo json_encode([
        'sucesso'    => true,
        'total'      => $total,
        'pagina'     => $pagina,
        'por_pagina' => $porPagina,
        'paginas'    => (int) ceil($total / $porPagina),
        'produtos'   => $produtos,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno ao buscar produtos.']);
}
