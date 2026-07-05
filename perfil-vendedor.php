<?php
// ShopBR - Editar Perfil do Vendedor
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

exigirVendedor('login.php');
iniciarSessao();

$usuarioId = (int) $_SESSION['usuario_id'];

try {
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT u.nome, u.email, v.nome_loja, v.cpf_cnpj, v.telefone
         FROM usuarios u INNER JOIN vendedores v ON v.usuario_id = u.id
         WHERE u.id = ? LIMIT 1'
    );
    $stmt->execute([$usuarioId]);
    $perfil = $stmt->fetch();
    if (!$perfil) { header('Location: login.php'); exit; }
} catch (PDOException $e) {
    die('Erro ao carregar perfil.');
}

// Formata telefone para exibição
$tel = $perfil['telefone'];
$telFmt = strlen($tel) === 11
    ? '(' . substr($tel,0,2) . ') ' . substr($tel,2,5) . '-' . substr($tel,7)
    : '(' . substr($tel,0,2) . ') ' . substr($tel,2,4) . '-' . substr($tel,6);

$headerBase = '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopBR — Editar Perfil</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<main>
<section class="seller-page" aria-labelledby="perfil-heading">
    <div class="container">

        <nav class="page-nav">
            <a href="dashboard-vendedor.php" class="back-link">← Voltar ao Dashboard</a>
        </nav>

        <div class="seller-card" style="max-width:640px;">
            <div class="seller-card-header">
                <h1 id="perfil-heading">Editar Perfil</h1>
                <p>Atualize os dados da sua conta e loja.</p>
            </div>

            <div id="perfil-success" role="alert" aria-live="polite" style="display:none;margin-bottom:16px;"></div>
            <div id="perfil-error"   role="alert" aria-live="polite" style="display:none;margin-bottom:16px;"></div>

            <form id="form-perfil" novalidate>

                <fieldset style="border:none;padding:0;margin-bottom:24px;">
                    <legend style="font-size:15px;font-weight:700;margin-bottom:16px;color:var(--color-text);">Dados Pessoais</legend>

                    <div class="form-group">
                        <label for="pf-nome">Nome completo *</label>
                        <input type="text" id="pf-nome" class="form-control" maxlength="100"
                               value="<?= htmlspecialchars($perfil['nome'], ENT_QUOTES, 'UTF-8') ?>" required>
                        <span class="form-error" id="pf-nome-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="pf-email">E-mail</label>
                        <input type="email" id="pf-email" class="form-control"
                               value="<?= htmlspecialchars($perfil['email'], ENT_QUOTES, 'UTF-8') ?>" disabled
                               style="opacity:0.6;cursor:not-allowed;" title="O e-mail não pode ser alterado.">
                        <small style="font-size:12px;color:var(--color-text-muted);">O e-mail não pode ser alterado.</small>
                    </div>
                </fieldset>

                <fieldset style="border:none;padding:0;margin-bottom:24px;">
                    <legend style="font-size:15px;font-weight:700;margin-bottom:16px;color:var(--color-text);">Dados da Loja</legend>

                    <div class="form-group">
                        <label for="pf-loja">Nome da loja *</label>
                        <input type="text" id="pf-loja" class="form-control" maxlength="100"
                               value="<?= htmlspecialchars($perfil['nome_loja'], ENT_QUOTES, 'UTF-8') ?>" required>
                        <span class="form-error" id="pf-loja-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="pf-cpf">CPF / CNPJ</label>
                        <input type="text" id="pf-cpf" class="form-control"
                               value="<?= htmlspecialchars($perfil['cpf_cnpj'], ENT_QUOTES, 'UTF-8') ?>" disabled
                               style="opacity:0.6;cursor:not-allowed;" title="CPF/CNPJ não pode ser alterado.">
                        <small style="font-size:12px;color:var(--color-text-muted);">CPF/CNPJ não pode ser alterado.</small>
                    </div>

                    <div class="form-group">
                        <label for="pf-tel">Telefone *</label>
                        <input type="tel" id="pf-tel" class="form-control" maxlength="15" inputmode="numeric"
                               value="<?= htmlspecialchars($telFmt, ENT_QUOTES, 'UTF-8') ?>" required>
                        <span class="form-error" id="pf-tel-error"></span>
                    </div>
                </fieldset>

                <fieldset style="border:1px solid var(--color-border);border-radius:8px;padding:20px;margin-bottom:24px;">
                    <legend style="font-size:15px;font-weight:700;padding:0 8px;color:var(--color-text);">Alterar Senha <span style="font-weight:400;font-size:13px;color:var(--color-text-muted);">(opcional)</span></legend>

                    <div class="form-group">
                        <label for="pf-senha-atual">Senha atual</label>
                        <input type="password" id="pf-senha-atual" class="form-control" autocomplete="current-password">
                        <span class="form-error" id="pf-senha-atual-error"></span>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group">
                            <label for="pf-nova-senha">Nova senha</label>
                            <input type="password" id="pf-nova-senha" class="form-control" autocomplete="new-password" placeholder="Mín. 8 caracteres">
                            <span class="form-error" id="pf-nova-senha-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="pf-confirmar">Confirmar senha</label>
                            <input type="password" id="pf-confirmar" class="form-control" autocomplete="new-password">
                            <span class="form-error" id="pf-confirmar-error"></span>
                        </div>
                    </div>
                </fieldset>

                <button type="submit" id="btn-salvar-perfil" class="btn btn-primary btn-block">Salvar Alterações</button>
            </form>
        </div>
    </div>
</section>
</main>

<footer class="site-footer" role="contentinfo" style="padding:var(--spacing-lg) 0;">
    <div class="container">
        <div class="footer-bottom" style="border:none;padding:0;">
            <p>&copy; <?= date('Y') ?> ShopBR. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>

<script>
// Máscara de telefone
document.getElementById('pf-tel').addEventListener('input', function() {
    let v = this.value.replace(/\D/g,'').slice(0,11);
    if (v.length <= 10) v = v.replace(/(\d{2})(\d)/,'($1) $2').replace(/(\d{4})(\d)/,'$1-$2');
    else                v = v.replace(/(\d{2})(\d)/,'($1) $2').replace(/(\d{5})(\d)/,'$1-$2');
    this.value = v;
});

function setErr(id, msg) {
    const el = document.getElementById(id);
    if (el) { el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
}
function clearAll() {
    ['pf-nome-error','pf-loja-error','pf-tel-error','pf-senha-atual-error','pf-nova-senha-error','pf-confirmar-error']
        .forEach(id => setErr(id, ''));
}
function showBanner(id, msg, ok) {
    const el = document.getElementById(id);
    el.textContent = msg; el.style.display = 'block';
    el.style.padding = '12px 16px'; el.style.borderRadius = '8px';
    el.style.fontWeight = '600'; el.style.fontSize = '14px';
    el.style.background = ok ? '#e8f5e9' : '#fdecea';
    el.style.color      = ok ? '#2e7d32' : '#c62828';
    el.style.border     = ok ? '1px solid #a5d6a7' : '1px solid #ef9a9a';
}

document.getElementById('form-perfil').addEventListener('submit', async function(e) {
    e.preventDefault();
    clearAll();
    ['perfil-success','perfil-error'].forEach(id => {
        document.getElementById(id).style.display = 'none';
    });

    const nome       = document.getElementById('pf-nome').value.trim();
    const nomeLoja   = document.getElementById('pf-loja').value.trim();
    const telefone   = document.getElementById('pf-tel').value.trim();
    const senhaAtual = document.getElementById('pf-senha-atual').value;
    const novaSenha  = document.getElementById('pf-nova-senha').value;
    const confirmar  = document.getElementById('pf-confirmar').value;

    let ok = true;
    if (!nome)    { setErr('pf-nome-error','Nome obrigatório.'); ok=false; }
    if (!nomeLoja){ setErr('pf-loja-error','Nome da loja obrigatório.'); ok=false; }
    const digits = telefone.replace(/\D/g,'');
    if (!digits||![10,11].includes(digits.length)) { setErr('pf-tel-error','Telefone inválido.'); ok=false; }
    if (!ok) return;

    const btn = document.getElementById('btn-salvar-perfil');
    btn.disabled = true; btn.textContent = 'Salvando...';

    try {
        const res  = await fetch('api/vendedor/editar.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ nome, nome_loja: nomeLoja, telefone,
                                   senha_atual: senhaAtual, nova_senha: novaSenha, confirmar_nova: confirmar }),
        });
        const data = await res.json();
        if (data.sucesso) {
            showBanner('perfil-success', data.mensagem, true);
            window.scrollTo({top:0, behavior:'smooth'});
        } else {
            if (data.erros) {
                if (data.erros.nome)          setErr('pf-nome-error', data.erros.nome);
                if (data.erros.nome_loja)     setErr('pf-loja-error', data.erros.nome_loja);
                if (data.erros.telefone)      setErr('pf-tel-error', data.erros.telefone);
                if (data.erros.senha_atual)   setErr('pf-senha-atual-error', data.erros.senha_atual);
                if (data.erros.nova_senha)    setErr('pf-nova-senha-error', data.erros.nova_senha);
                if (data.erros.confirmar_nova)setErr('pf-confirmar-error', data.erros.confirmar_nova);
            }
            showBanner('perfil-error', data.mensagem || 'Erro ao salvar.', false);
        }
    } catch { showBanner('perfil-error','Erro de conexão.',false); }
    finally { btn.disabled=false; btn.textContent='Salvar Alterações'; }
});
</script>
</body>
</html>
