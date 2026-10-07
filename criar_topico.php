<?php
// Alteração: usa caminhos absolutos e as rotinas comuns de XML.
session_start();
require_once __DIR__ . '/funcoes.php';

if (!isset($_SESSION['usuario'])) {
    encerrarComErro('Você precisa estar logado para criar um tópico.', 403);
}

$titulo = '';
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Alteração: valida o token e os campos antes de acessar o XML.
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        encerrarComErro('Formulário expirado. Tente novamente.', 403);
    }
    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $mensagem = trim((string) ($_POST['mensagem'] ?? ''));
    if ($titulo === '' || $mensagem === '') {
        $erro = 'Informe o título e a mensagem.';
    } else {
        try {
            $topicos = carregarXml(ARQUIVO_TOPICOS, 'topicos');
            $novo = $topicos->addChild('topico');
            adicionarTextoXml($novo, 'autor', (string) $_SESSION['usuario']);
            adicionarTextoXml($novo, 'titulo', $titulo);
            adicionarTextoXml($novo, 'mensagem', $mensagem);
            $novo->addChild('comentarios');
            salvarXml($topicos, ARQUIVO_TOPICOS);
            // Alteração: evita a criação duplicada ao atualizar a página.
            $_SESSION['mensagem_sucesso'] = 'Tópico criado com sucesso!';
            header('Location: listar.php');
            exit;
        } catch (RuntimeException $excecao) {
            $erro = $excecao->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Criar tópico | Fórum</title>
    <style>
        :root {
            color-scheme: light;
            --page: #f5f7fb;
            --surface: #fff;
            --ink: #19243b;
            --muted: #68758c;
            --line: #e3e8f0;
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --danger: #c2414b;
            --shadow: 0 18px 50px rgba(31, 45, 77, .08);
        }
        * { box-sizing: border-box; }
        body { min-width: 320px; min-height: 100vh; margin: 0; color: var(--ink); font: 16px/1.6 Arial, sans-serif; }
        a { color: var(--primary); font-weight: 600; text-decoration: none; }
        a:hover { color: var(--primary-dark); text-decoration: underline; }
        h1, p { margin-top: 0; }
        .auth-page { display: grid; min-height: 100vh; place-items: center; padding: 36px 20px; background: radial-gradient(ellipse at 15% 15%, rgba(99, 102, 241, .1), transparent 36%), radial-gradient(ellipse at 85% 85%, rgba(14, 165, 233, .08), transparent 34%), var(--page); }
        .auth-card { width: min(100%, 620px); padding: 36px; border: 1px solid var(--line); border-radius: 18px; background: var(--surface); box-shadow: var(--shadow); }
        .brand { display: inline-block; margin-bottom: 30px; color: var(--ink); font: 800 1.5rem "Trebuchet MS", Arial, sans-serif; letter-spacing: -.06em; }
        .brand:hover { color: var(--ink); text-decoration: none; }
        .brand span { color: var(--primary); }
        .eyebrow { margin-bottom: 8px; color: var(--primary); font-size: .75rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        h1 { margin-bottom: 8px; font: 700 1.9rem/1.2 "Trebuchet MS", Arial, sans-serif; letter-spacing: -.04em; }
        .intro { margin-bottom: 24px; color: var(--muted); }
        .alert { margin-bottom: 20px; padding: 13px 16px; border: 1px solid #ffd5d5; border-radius: 10px; background: #fff0f0; color: var(--danger); }
        .stacked-form { display: grid; gap: 17px; }
        label { display: grid; gap: 7px; font-size: .9rem; font-weight: 600; }
        input, textarea { width: 100%; padding: 11px 13px; border: 1px solid #d7deea; border-radius: 9px; outline: none; background: #fff; color: var(--ink); font: inherit; font-weight: 400; }
        input { min-height: 46px; }
        textarea { min-height: 190px; resize: vertical; }
        input:focus, textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, .13); }
        button { min-height: 44px; margin-top: 4px; padding: 10px 18px; border: 1px solid var(--primary); border-radius: 10px; background: var(--primary); color: #fff; cursor: pointer; font: inherit; font-weight: 700; }
        button:hover { border-color: var(--primary-dark); background: var(--primary-dark); }
        .back-link { margin: 16px 0 0; font-size: .9rem; text-align: center; }
        @media (max-width: 600px) { .auth-page { padding: 20px 14px; } .auth-card { padding: 26px 22px; } }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; } }
    </style>
</head>
<body>
    <main class="auth-page">
        <section class="auth-card topic-editor">
            <a class="brand auth-brand" href="listar.php">Fórum<span>.</span></a>
            <p class="eyebrow">Nova conversa</p>
            <h1>Criar tópico</h1>
            <p class="intro">Compartilhe uma ideia ou comece uma conversa com a comunidade.</p>
            <?php if ($erro !== ''): ?><p class="alert alert-error"><?= escapar($erro) ?></p><?php endif; ?>
            <form class="stacked-form" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                <label>Título
                    <input type="text" name="titulo" value="<?= escapar($titulo) ?>" placeholder="Sobre o que você quer conversar?" required>
                </label>
                <label>Mensagem
                    <textarea name="mensagem" placeholder="Conte mais detalhes..." required><?= escapar($mensagem) ?></textarea>
                </label>
                <button type="submit">Publicar tópico</button>
            </form>
            <p class="back-link"><a href="listar.php">Voltar aos tópicos</a></p>
        </section>
    </main>
</body>
</html>