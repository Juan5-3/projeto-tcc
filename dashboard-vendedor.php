<?php
// ShopBR - Seller Dashboard
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

exigirVendedor('login.php');
iniciarSessao();

$usuarioId = (int) $_SESSION['usuario_id'];

// ── Dados do vendedor ─────────────────────────────────────
try {
    $db = getDB();

    $stmtV = $db->prepare(
        'SELECT v.id, v.nome_loja, v.telefone, u.nome, u.email
         FROM vendedores v INNER JOIN usuarios u ON u.id = v.usuario_id
         WHERE v.usuario_id = ? LIMIT 1'
    );
    $stmtV->execute([$usuarioId]);
    $vendedor = $stmtV->fetch();
    if (!$vendedor) { header('Location: login.php'); exit; }

    $vendedorId = (int) $vendedor['id'];

    // Métricas
    $totalProdutos = (int) $db->prepare('SELECT COUNT(*) FROM produtos WHERE vendedor_id = ?')
                               ->execute([$vendedorId]) ? 0 : 0;
    $stmtTP = $db->prepare('SELECT COUNT(*) AS c FROM produtos WHERE vendedor_id = ?');
    $stmtTP->execute([$vendedorId]);
    $totalProdutos = (int) $stmtTP->fetch()['c'];

    $stmtTA = $db->prepare('SELECT COUNT(*) AS c FROM produtos WHERE vendedor_id = ? AND ativo = 1');
    $stmtTA->execute([$vendedorId]);
    $produtosAtivos = (int) $stmtTA->fetch()['c'];

    $stmtOF = $db->prepare(
        'SELECT COUNT(*) AS c FROM ofertas o
         INNER JOIN produtos p ON p.id = o.produto_id
         WHERE p.vendedor_id = ? AND o.ativa = 1 AND o.data_fim > NOW()'
    );
    $stmtOF->execute([$vendedorId]);
    $ofertasAtivas = (int) $stmtOF->fetch()['c'];

    $stmtVZ = $db->prepare('SELECT COALESCE(SUM(visualizacoes),0) AS c FROM produtos WHERE vendedor_id = ?');
    $stmtVZ->execute([$vendedorId]);
    $totalVisualizacoes = (int) $stmtVZ->fetch()['c'];

    // Produtos do vendedor
    $stmtProd = $db->prepare(
        'SELECT p.id, p.nome, p.preco, p.estoque, p.ativo, p.imagem_url,
                c.nome AS categoria_nome
         FROM produtos p
         INNER JOIN categorias c ON c.id = p.categoria_id
         WHERE p.vendedor_id = ?
         ORDER BY p.criado_em DESC'
    );
    $stmtProd->execute([$vendedorId]);
    $produtos = $stmtProd->fetchAll();

    // Categorias para o formulário de novo produto
    $categorias = $db->query('SELECT id, nome FROM categorias ORDER BY nome')->fetchAll();

    // Ofertas ativas do vendedor
    $stmtOfertas = $db->prepare(
        'SELECT o.id, o.desconto_pct, o.preco_oferta, o.data_fim,
                p.nome AS produto_nome, p.preco AS preco_original
         FROM ofertas o
         INNER JOIN produtos p ON p.id = o.produto_id
         WHERE p.vendedor_id = ? AND o.ativa = 1 AND o.data_fim > NOW()
         ORDER BY o.criado_em DESC'
    );
    $stmtOfertas->execute([$vendedorId]);
    $ofertas = $stmtOfertas->fetchAll();

} catch (PDOException $e) {
    die('Erro ao carregar dados. Verifique a conexão com o banco.');
}

function fmt(float $v): string {
    return 'R$ ' . number_format($v, 2, ',', '.');
}

$headerBase = '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopBR — Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<main>
<section class="dashboard-page" aria-labelledby="dashboard-heading">
<div class="container">

    <!-- Header -->
    <div class="dashboard-header">
        <div class="dashboard-title">
            <h1 id="dashboard-heading">Dashboard</h1>
            <p>Bem-vindo, <strong><?= htmlspecialchars($vendedor['nome_loja'], ENT_QUOTES, 'UTF-8') ?></strong>!</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button class="btn btn-secondary btn-sm" onclick="abrirModal('modal-produto')">+ Novo Produto</button>
            <a href="perfil-vendedor.php" class="btn btn-secondary btn-sm">✏️ Editar Perfil</a>
        </div>
    </div>

    <!-- Métricas -->
    <section aria-labelledby="metrics-heading">
        <h2 class="sr-only" id="metrics-heading">Métricas</h2>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon">📦</div>
                <div class="metric-label">Total Produtos</div>
                <div class="metric-value"><?= $totalProdutos ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon">✅</div>
                <div class="metric-label">Produtos Ativos</div>
                <div class="metric-value"><?= $produtosAtivos ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon">🏷️</div>
                <div class="metric-label">Ofertas Ativas</div>
                <div class="metric-value"><?= $ofertasAtivas ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon">👁️</div>
                <div class="metric-label">Visualizações</div>
                <div class="metric-value"><?= number_format($totalVisualizacoes, 0, ',', '.') ?></div>
            </div>
        </div>
    </section>

    <!-- Produtos -->
    <section class="dashboard-products" aria-labelledby="products-table-heading">
        <div class="dashboard-section-header">
            <h2 id="products-table-heading">Meus Produtos</h2>
            <button class="btn btn-primary btn-sm" onclick="abrirModal('modal-produto')">+ Adicionar Produto</button>
        </div>

        <div id="products-feedback" role="alert" aria-live="polite" style="display:none;margin-bottom:12px;"></div>

        <?php if (empty($produtos)): ?>
            <div class="empty-message">
                <div class="empty-icon">📦</div>
                <h3>Nenhum produto cadastrado</h3>
                <p>Clique em "Adicionar Produto" para começar.</p>
            </div>
        <?php else: ?>
            <div class="products-table-wrapper">
                <table class="products-table" aria-label="Produtos da loja">
                    <thead>
                        <tr>
                            <th scope="col">Nome</th>
                            <th scope="col">Categoria</th>
                            <th scope="col">Preço</th>
                            <th scope="col">Estoque</th>
                            <th scope="col">Status</th>
                            <th scope="col">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="products-tbody">
                    <?php foreach ($produtos as $p): ?>
                        <tr id="produto-row-<?= $p['id'] ?>">
                            <td><?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($p['categoria_nome'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><strong><?= fmt((float)$p['preco']) ?></strong></td>
                            <td><?= (int)$p['estoque'] ?></td>
                            <td>
                                <?php if ($p['ativo']): ?>
                                    <span class="status-badge status-active">✓ Ativo</span>
                                <?php else: ?>
                                    <span class="status-badge status-inactive">✕ Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="table-actions">
                                <button
                                    class="btn-table-action btn-toggle"
                                    data-id="<?= $p['id'] ?>"
                                    data-ativo="<?= $p['ativo'] ? '1' : '0' ?>"
                                    title="<?= $p['ativo'] ? 'Desativar' : 'Ativar' ?>"
                                ><?= $p['ativo'] ? '⏸' : '▶' ?></button>
                                <button
                                    class="btn-table-action btn-oferta"
                                    data-id="<?= $p['id'] ?>"
                                    data-nome="<?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-preco="<?= (float)$p['preco'] ?>"
                                    title="Criar oferta"
                                >🏷️</button>
                                <button
                                    class="btn-table-action btn-delete"
                                    data-id="<?= $p['id'] ?>"
                                    data-nome="<?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                    title="Excluir"
                                >🗑️</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <!-- Ofertas -->
    <section class="dashboard-products" aria-labelledby="offers-heading" style="margin-top:24px;">
        <div class="dashboard-section-header">
            <h2 id="offers-heading">Ofertas Ativas</h2>
        </div>

        <div id="offers-feedback" role="alert" aria-live="polite" style="display:none;margin-bottom:12px;"></div>

        <?php if (empty($ofertas)): ?>
            <div class="empty-message">
                <div class="empty-icon">🏷️</div>
                <h3>Nenhuma oferta ativa</h3>
                <p>Clique em 🏷️ em um produto para criar uma oferta.</p>
            </div>
        <?php else: ?>
            <div class="products-table-wrapper">
                <table class="products-table" aria-label="Ofertas ativas">
                    <thead>
                        <tr>
                            <th scope="col">Produto</th>
                            <th scope="col">Preço original</th>
                            <th scope="col">Desconto</th>
                            <th scope="col">Preço com desconto</th>
                            <th scope="col">Válida até</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ofertas as $o): ?>
                        <tr>
                            <td><?= htmlspecialchars($o['produto_nome'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= fmt((float)$o['preco_original']) ?></td>
                            <td><span class="status-badge status-active"><?= number_format((float)$o['desconto_pct'], 0) ?>% off</span></td>
                            <td><strong style="color:var(--color-primary)"><?= fmt((float)$o['preco_oferta']) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($o['data_fim'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

</div>
</section>
</main>

<!-- ══ MODAL: Novo Produto ══ -->
<div class="modal-overlay" id="modal-produto" role="dialog" aria-modal="true" aria-labelledby="modal-produto-title" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2 id="modal-produto-title">Cadastrar Produto</h2>
            <button class="modal-close" onclick="fecharModal('modal-produto')" aria-label="Fechar">&times;</button>
        </div>
        <div id="modal-produto-feedback" role="alert" aria-live="polite" style="display:none;margin-bottom:12px;"></div>
        <form id="form-novo-produto" novalidate>
            <div class="form-group">
                <label for="p-nome">Nome *</label>
                <input type="text" id="p-nome" class="form-control" maxlength="150" required>
                <span class="form-error" id="p-nome-error"></span>
            </div>
            <div class="form-group">
                <label for="p-categoria">Categoria *</label>
                <select id="p-categoria" class="form-control" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="form-error" id="p-categoria-error"></span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label for="p-preco">Preço (R$) *</label>
                    <input type="number" id="p-preco" class="form-control" min="0" step="0.01" required>
                    <span class="form-error" id="p-preco-error"></span>
                </div>
                <div class="form-group">
                    <label for="p-estoque">Estoque *</label>
                    <input type="number" id="p-estoque" class="form-control" min="0" step="1" value="0" required>
                    <span class="form-error" id="p-estoque-error"></span>
                </div>
            </div>
            <div class="form-group">
                <label for="p-descricao">Descrição</label>
                <textarea id="p-descricao" class="form-control" rows="3" maxlength="1000" style="resize:vertical;"></textarea>
            </div>
            <div class="form-group">
                <label for="p-imagem">URL da imagem</label>
                <input type="url" id="p-imagem" class="form-control" placeholder="https://...">
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="fecharModal('modal-produto')">Cancelar</button>
                <button type="submit" id="btn-salvar-produto" class="btn btn-primary btn-sm">Salvar Produto</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ MODAL: Nova Oferta ══ -->
<div class="modal-overlay" id="modal-oferta" role="dialog" aria-modal="true" aria-labelledby="modal-oferta-title" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2 id="modal-oferta-title">Criar Oferta</h2>
            <button class="modal-close" onclick="fecharModal('modal-oferta')" aria-label="Fechar">&times;</button>
        </div>
        <p id="oferta-produto-info" style="margin-bottom:16px;font-size:14px;color:var(--color-text-muted);"></p>
        <div id="modal-oferta-feedback" role="alert" aria-live="polite" style="display:none;margin-bottom:12px;"></div>
        <form id="form-nova-oferta" novalidate>
            <input type="hidden" id="oferta-produto-id">
            <div class="form-group">
                <label for="oferta-desconto">Desconto (%) *</label>
                <input type="number" id="oferta-desconto" class="form-control" min="1" max="99" step="1" placeholder="Ex: 20">
                <span class="form-error" id="oferta-desconto-error"></span>
            </div>
            <div id="oferta-preco-preview" style="font-size:14px;color:var(--color-text-muted);margin-bottom:12px;display:none;"></div>
            <div class="form-group">
                <label for="oferta-data-fim">Válida até *</label>
                <input type="datetime-local" id="oferta-data-fim" class="form-control">
                <span class="form-error" id="oferta-data-fim-error"></span>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="fecharModal('modal-oferta')">Cancelar</button>
                <button type="submit" id="btn-salvar-oferta" class="btn btn-primary btn-sm">Criar Oferta</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ FOOTER ══ -->
<footer class="site-footer" role="contentinfo" style="padding:var(--spacing-lg) 0;">
    <div class="container">
        <div class="footer-bottom" style="border:none;padding:0;">
            <p>&copy; <?= date('Y') ?> ShopBR. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>

<script>
// ── Modal helpers ─────────────────────────────────────────
function abrirModal(id) {
    const m = document.getElementById(id);
    m.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    m.querySelector('[autofocus], input, select, textarea').focus();
}
function fecharModal(id) {
    document.getElementById(id).style.display = 'none';
    document.body.style.overflow = '';
}
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) fecharModal(m.id); });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay[style*="flex"]').forEach(m => fecharModal(m.id));
    }
});

// ── Feedback helper ───────────────────────────────────────
function showFeedback(elId, msg, ok = false) {
    const el = document.getElementById(elId);
    el.textContent = msg;
    el.style.display = 'block';
    el.style.padding = '10px 14px';
    el.style.borderRadius = '8px';
    el.style.fontSize = '14px';
    el.style.fontWeight = '600';
    el.style.background = ok ? '#e8f5e9' : '#fdecea';
    el.style.color      = ok ? '#2e7d32' : '#c62828';
    el.style.border     = ok ? '1px solid #a5d6a7' : '1px solid #ef9a9a';
}
function setErr(id, msg) {
    const el = document.getElementById(id);
    if (el) { el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
}
function clearErrs(...ids) { ids.forEach(id => setErr(id, '')); }

// ── Cadastrar produto ─────────────────────────────────────
document.getElementById('form-novo-produto').addEventListener('submit', async function(e) {
    e.preventDefault();
    clearErrs('p-nome-error','p-categoria-error','p-preco-error','p-estoque-error');
    document.getElementById('modal-produto-feedback').style.display = 'none';

    const nome      = document.getElementById('p-nome').value.trim();
    const catId     = document.getElementById('p-categoria').value;
    const preco     = parseFloat(document.getElementById('p-preco').value);
    const estoque   = parseInt(document.getElementById('p-estoque').value, 10);
    const descricao = document.getElementById('p-descricao').value.trim();
    const imagem    = document.getElementById('p-imagem').value.trim();

    let ok = true;
    if (!nome)           { setErr('p-nome-error','Nome obrigatório.');     ok = false; }
    if (!catId)          { setErr('p-categoria-error','Selecione.');       ok = false; }
    if (isNaN(preco)||preco<0) { setErr('p-preco-error','Preço inválido.'); ok = false; }
    if (isNaN(estoque)||estoque<0) { setErr('p-estoque-error','Estoque inválido.'); ok = false; }
    if (!ok) return;

    const btn = document.getElementById('btn-salvar-produto');
    btn.disabled = true; btn.textContent = 'Salvando...';

    try {
        const res  = await fetch('api/produto/criar.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ nome, categoria_id: parseInt(catId), preco, estoque, descricao, imagem_url: imagem }),
        });
        const data = await res.json();
        if (data.sucesso) {
            showFeedback('modal-produto-feedback', data.mensagem, true);
            setTimeout(() => location.reload(), 900);
        } else {
            if (data.erros) {
                if (data.erros.nome)         setErr('p-nome-error', data.erros.nome);
                if (data.erros.categoria_id) setErr('p-categoria-error', data.erros.categoria_id);
                if (data.erros.preco)        setErr('p-preco-error', data.erros.preco);
                if (data.erros.estoque)      setErr('p-estoque-error', data.erros.estoque);
            }
            showFeedback('modal-produto-feedback', data.mensagem || 'Erro ao salvar.');
        }
    } catch { showFeedback('modal-produto-feedback', 'Erro de conexão.'); }
    finally { btn.disabled = false; btn.textContent = 'Salvar Produto'; }
});

// ── Toggle status ─────────────────────────────────────────
document.querySelectorAll('.btn-toggle').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id   = this.dataset.id;
        const res  = await fetch('api/produto/toggle-status.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({produto_id: parseInt(id)}),
        });
        const data = await res.json();
        if (data.sucesso) {
            showFeedback('products-feedback', data.mensagem, true);
            setTimeout(() => location.reload(), 700);
        } else {
            showFeedback('products-feedback', data.mensagem || 'Erro.');
        }
    });
});

// ── Deletar produto ───────────────────────────────────────
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id   = this.dataset.id;
        const nome = this.dataset.nome;
        if (!confirm(`Excluir "${nome}"? Esta ação não pode ser desfeita.`)) return;

        const res  = await fetch('api/produto/deletar.php', {
            method:'DELETE', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({produto_id: parseInt(id)}),
        });
        const data = await res.json();
        if (data.sucesso) {
            document.getElementById('produto-row-' + id)?.remove();
            showFeedback('products-feedback', data.mensagem, true);
        } else {
            showFeedback('products-feedback', data.mensagem || 'Erro.');
        }
    });
});

// ── Abrir modal de oferta ─────────────────────────────────
document.querySelectorAll('.btn-oferta').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('oferta-produto-id').value    = this.dataset.id;
        document.getElementById('oferta-produto-info').textContent =
            `Produto: ${this.dataset.nome} — Preço atual: R$ ${parseFloat(this.dataset.preco).toFixed(2).replace('.',',')}`;
        document.getElementById('oferta-desconto').value  = '';
        document.getElementById('oferta-data-fim').value  = '';
        document.getElementById('oferta-preco-preview').style.display = 'none';
        document.getElementById('modal-oferta-feedback').style.display = 'none';
        abrirModal('modal-oferta');
        // Guarda preço original para preview
        document.getElementById('oferta-desconto').dataset.preco = this.dataset.preco;
    });
});

// Preview do preço com desconto
document.getElementById('oferta-desconto').addEventListener('input', function() {
    const pct   = parseFloat(this.value);
    const preco = parseFloat(this.dataset.preco || 0);
    const prev  = document.getElementById('oferta-preco-preview');
    if (pct > 0 && pct < 100 && preco > 0) {
        const com = (preco * (1 - pct/100)).toFixed(2).replace('.',',');
        prev.textContent = `Preço com desconto: R$ ${com}`;
        prev.style.display = 'block';
        prev.style.color = 'var(--color-primary)';
        prev.style.fontWeight = '600';
    } else {
        prev.style.display = 'none';
    }
});

// ── Criar oferta ──────────────────────────────────────────
document.getElementById('form-nova-oferta').addEventListener('submit', async function(e) {
    e.preventDefault();
    clearErrs('oferta-desconto-error','oferta-data-fim-error');
    document.getElementById('modal-oferta-feedback').style.display = 'none';

    const produtoId = parseInt(document.getElementById('oferta-produto-id').value);
    const desconto  = parseFloat(document.getElementById('oferta-desconto').value);
    const dataFim   = document.getElementById('oferta-data-fim').value;

    let ok = true;
    if (!desconto||desconto<=0||desconto>=100) { setErr('oferta-desconto-error','Entre 1% e 99%.'); ok=false; }
    if (!dataFim) { setErr('oferta-data-fim-error','Informe a data.'); ok=false; }
    if (!ok) return;

    const btn = document.getElementById('btn-salvar-oferta');
    btn.disabled = true; btn.textContent = 'Salvando...';

    try {
        const res  = await fetch('api/oferta/criar.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ produto_id: produtoId, desconto_pct: desconto, data_fim: dataFim }),
        });
        const data = await res.json();
        if (data.sucesso) {
            showFeedback('modal-oferta-feedback', data.mensagem, true);
            setTimeout(() => location.reload(), 900);
        } else {
            if (data.erros) {
                if (data.erros.desconto_pct) setErr('oferta-desconto-error', data.erros.desconto_pct);
                if (data.erros.data_fim)     setErr('oferta-data-fim-error', data.erros.data_fim);
            }
            showFeedback('modal-oferta-feedback', data.mensagem || 'Erro.');
        }
    } catch { showFeedback('modal-oferta-feedback', 'Erro de conexão.'); }
    finally { btn.disabled = false; btn.textContent = 'Criar Oferta'; }
});
</script>
</body>
</html>
