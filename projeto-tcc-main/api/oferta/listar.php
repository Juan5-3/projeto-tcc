<?php
// ============================================================
// ShopBR — API: Listar ofertas ativas
// GET /api/oferta/listar.php
// ?limite=8          — quantas ofertas retornar (padrão 8, máx 50)
// ?vendedor_id=1     — filtra por vendedor (dashboard)
// ============================================================
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$limite     = min(50, max(1, (int) ($_GET['limite']     ?? 8)));
$vendedorId = filter_input(INPUT_GET, 'vendedor_id', FILTER_VALIDATE_INT);

try {
    $db = getDB();

    $where  = ['o.ativa = 1', 'o.data_fim > NOW()', 'p.ativo = 1'];
    $params = [];

    if ($vendedorId) {
        $where[]  = 'p.vendedor_id = ?';
        $params[] = $vendedorId;
    }

    $whereClause = implode(' AND ', $where);

    $sql = "
        SELECT
            o.id           AS oferta_id,
            o.desconto_pct,
            o.preco_oferta,
            o.data_fim,
            p.id           AS produto_id,
            p.nome         AS produto_nome,
            p.preco        AS preco_original,
            p.imagem_url,
            p.visualizacoes,
            c.nome         AS categoria_nome,
            c.slug         AS categoria_slug,
            v.nome_loja
        FROM ofertas o
        INNER JOIN produtos   p ON p.id = o.produto_id
        INNER JOIN categorias c ON c.id = p.categoria_id
        INNER JOIN vendedores v ON v.id = p.vendedor_id
        WHERE {$whereClause}
        ORDER BY p.visualizacoes DESC, o.criado_em DESC
        LIMIT ?
    ";

    $params[] = $limite;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $ofertas = $stmt->fetchAll();

    foreach ($ofertas as &$o) {
        $o['oferta_id']    = (int)   $o['oferta_id'];
        $o['produto_id']   = (int)   $o['produto_id'];
        $o['desconto_pct'] = (float) $o['desconto_pct'];
        $o['preco_oferta'] = (float) $o['preco_oferta'];
        $o['preco_original'] = (float) $o['preco_original'];
        $o['visualizacoes']  = (int) $o['visualizacoes'];
    }
    unset($o);

    echo json_encode(['sucesso' => true, 'ofertas' => $ofertas]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno ao buscar ofertas.']);
}
