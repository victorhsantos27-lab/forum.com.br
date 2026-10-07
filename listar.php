<?php
// Nova página: prepara a sessão, as funções auxiliares e os dados da listagem.
session_start();
require_once __DIR__ . '/funcoes.php';

$erro = '';
$mensagemSucesso = (string) ($_SESSION['mensagem_sucesso'] ?? '');
unset($_SESSION['mensagem_sucesso']);

try {
    $topicos = carregarXml(ARQUIVO_TOPICOS, 'topicos');
} catch (RuntimeException $excecao) {
    $erro = $excecao->getMessage();
    $topicos = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><topicos/>');
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fórum</title>
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
            --primary-soft: #eeedff;
            --danger: #c2414b;
            --shadow: 0 18px 50px rgba(31, 45, 77, .08);
        }

        * { box-sizing: border-box; }
        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            background: var(--page);
            color: var(--ink);
            font: 16px/1.6 Arial, sans-serif;
        }
        a { color: var(--primary); font-weight: 600; text-decoration: none; }
        a:hover { color: var(--primary-dark); text-decoration: underline; }
        h1, h2, h3, p { margin-top: 0; }
        h1, h2, h3, .brand { font-family: "Trebuchet MS", Arial, sans-serif; }
        h1 { margin-bottom: 8px; font-size: clamp(1.8rem, 4vw, 2.4rem); line-height: 1.2; letter-spacing: -.04em; }
        .container { width: min(100% - 40px, 900px); margin-inline: auto; }
        .site-header { background: var(--surface); border-bottom: 1px solid var(--line); }
        .header-content, .main-nav, .page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-content { min-height: 76px; gap: 24px; }
        .brand { color: var(--ink); font-size: 1.5rem; font-weight: 800; letter-spacing: -.06em; }
        .brand:hover { color: var(--ink); text-decoration: none; }
        .brand span { color: var(--primary); }
        .main-nav { gap: 20px; }
        .user-label { color: var(--muted); font-size: .9rem; }
        .page-content { padding-top: 54px; padding-bottom: 80px; }
        .page-heading { margin-bottom: 28px; gap: 24px; }
        .eyebrow { margin-bottom: 8px; color: var(--primary); font-size: .75rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .intro { margin-bottom: 0; color: var(--muted); }
        .button, button {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            border: 1px solid var(--primary);
            border-radius: 10px;
            background: var(--primary);
            color: #fff;
            cursor: pointer;
            font: inherit;
            font-weight: 700;
            line-height: 1.3;
            text-align: center;
            transition: background-color 140ms ease, border-color 140ms ease, transform 140ms ease;
        }
        .button:hover, button:hover {
            transform: translateY(-1px);
            border-color: var(--primary-dark);
            background: var(--primary-dark);
            color: #fff;
            text-decoration: none;
        }
        .button-secondary { border-color: var(--primary-soft); background: var(--primary-soft); color: var(--primary-dark); }
        .button-secondary:hover { border-color: #dedcff; background: #dedcff; color: var(--primary-dark); }
        .alert { margin-bottom: 20px; padding: 13px 16px; border: 1px solid transparent; border-radius: 10px; }
        .alert-error { border-color: #ffd5d5; background: #fff0f0; color: var(--danger); }
        .alert-success { border-color: #c9eedb; background: #e9f8f0; color: #18794e; }
        .empty-state, .topic-card {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--surface);
            box-shadow: var(--shadow);
        }
        .empty-state { padding: 40px 24px; text-align: center; }
        .empty-state h2 { margin-bottom: 8px; }
        .empty-state p { margin-bottom: 20px; color: var(--muted); }
        .topic-card { margin-bottom: 22px; padding: 28px; }
        .topic-card h2 { margin-bottom: 12px; font-size: 1.45rem; line-height: 1.35; }
        .topic-message { margin-bottom: 14px; overflow-wrap: anywhere; }
        .topic-author, .muted { color: var(--muted); font-size: .9rem; }
        .topic-author { margin-bottom: 0; }
        .comments { margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--line); }
        .comments h3 { display: flex; align-items: center; gap: 9px; margin-bottom: 16px; font-size: 1rem; }
        .comment-count {
            display: inline-flex;
            min-width: 24px;
            height: 24px;
            align-items: center;
            justify-content: center;
            padding: 0 7px;
            border-radius: 20px;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: .75rem;
        }
        .comment { margin-bottom: 12px; padding: 14px 16px; border: 1px solid var(--line); border-radius: 12px; background: #fafbfe; }
        .comment p { margin-bottom: 4px; overflow-wrap: anywhere; }
        .comment p:last-of-type { margin-bottom: 0; }
        .inline-form { margin-top: 8px; }
        .button-link {
            min-height: auto;
            padding: 0;
            border: 0;
            background: transparent;
            color: var(--danger);
            font-size: .82rem;
            font-weight: 600;
        }
        .button-link:hover { transform: none; border: 0; background: transparent; color: #9f2732; text-decoration: underline; }
        .comment-form { display: grid; gap: 14px; margin-top: 20px; }
        label { display: grid; gap: 7px; color: var(--ink); font-size: .9rem; font-weight: 600; }
        input, textarea {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #d7deea;
            border-radius: 9px;
            outline: none;
            background: #fff;
            color: var(--ink);
            font: inherit;
            font-weight: 400;
            transition: border-color 140ms ease, box-shadow 140ms ease;
        }
        input { min-height: 46px; }
        textarea { min-height: 112px; resize: vertical; }
        input::placeholder, textarea::placeholder { color: #9aa5b7; }
        input:focus, textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, .13); }
        .comment-form button { justify-self: start; }
        @media (max-width: 600px) {
            .container { width: min(100% - 28px, 900px); }
            .header-content { min-height: 68px; }
            .main-nav { gap: 12px; }
            .user-label { max-width: 135px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .page-content { padding-top: 36px; }
            .page-heading { align-items: flex-start; flex-direction: column; gap: 18px; }
            .topic-card { padding: 20px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a class="brand" href="listar.php">Fórum<span>.</span></a>
            <nav class="main-nav">
                <?php if (isset($_SESSION['usuario'])): ?>
                    <span class="user-label">Conectado como <?= escapar($_SESSION['usuario']) ?></span>
                    <a class="button button-secondary" href="criar_topico.php">Criar tópico</a>
                <?php else: ?>
                    <a href="login.php">Entrar</a>
                    <a class="button button-secondary" href="cadastro.php">Cadastrar</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container page-content">
        <div class="page-heading">
            <div>
                <p class="eyebrow">Comunidade</p>
                <h1>Tópicos do fórum</h1>
                <p class="intro">Ideias, dúvidas e conversas em um só lugar.</p>
            </div>
            <?php if (isset($_SESSION['usuario'])): ?>
                <a class="button" href="criar_topico.php">Novo tópico</a>
            <?php endif; ?>
        </div>

        <?php if ($mensagemSucesso !== ''): ?><p class="alert alert-success"><?= escapar($mensagemSucesso) ?></p><?php endif; ?>
        <?php if ($erro !== ''): ?>
            <p class="alert alert-error"><?= escapar($erro) ?></p>
        <?php elseif (count($topicos->topico) === 0): ?>
            <section class="empty-state">
                <h2>Comece a conversa</h2>
                <p>Nenhum tópico foi criado ainda.</p>
                <?php if (isset($_SESSION['usuario'])): ?>
                    <a class="button" href="criar_topico.php">Criar primeiro tópico</a>
                <?php else: ?>
                    <a href="login.php">Entre</a> ou <a href="cadastro.php">crie uma conta</a> para publicar.
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php $id = 0; ?>
        <?php foreach ($topicos->topico as $topico): ?>
            <article class="topic-card">
                <h2><?= escapar($topico->titulo) ?></h2>
                <p class="topic-message"><?= nl2br(escapar($topico->mensagem)) ?></p>
                <p class="topic-author">Publicado por <strong><?= escapar($topico->autor) ?></strong></p>

                <div class="comments">
                    <h3>Comentários <span class="comment-count"><?= count($topico->comentarios->comentario) ?></span></h3>
                    <?php if (count($topico->comentarios->comentario) === 0): ?>
                        <p class="muted">Este tópico ainda não possui comentários.</p>
                    <?php endif; ?>

                    <?php $comentarioId = 0; ?>
                    <?php foreach ($topico->comentarios->comentario as $comentario): ?>
                        <section class="comment">
                            <p><strong><?= escapar($comentario->nome) ?></strong></p>
                            <p><?= nl2br(escapar($comentario->mensagem)) ?></p>
                            <?php if (isset($_SESSION['usuario']) && (string) $_SESSION['usuario'] === (string) $topico->autor): ?>
                                <form class="inline-form" method="post" action="excluir.php">
                                    <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                                    <input type="hidden" name="id" value="<?= escapar($id) ?>">
                                    <input type="hidden" name="comentario" value="<?= escapar($comentarioId) ?>">
                                    <button class="button-link button-danger" type="submit">Excluir comentário</button>
                                </form>
                            <?php endif; ?>
                        </section>
                        <?php $comentarioId++; ?>
                    <?php endforeach; ?>

                    <form class="comment-form" method="post" action="comentar.php">
                        <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                        <input type="hidden" name="id" value="<?= escapar($id) ?>">
                        <label>Seu nome
                            <input type="text" name="nome" placeholder="Como podemos chamar você?" required>
                        </label>
                        <label>Comentário
                            <textarea name="mensagem" placeholder="Escreva sua resposta..." required></textarea>
                        </label>
                        <button type="submit">Enviar comentário</button>
                    </form>
                </div>
            </article>
            <?php $id++; ?>
        <?php endforeach; ?>
    </main>
</body>
</html>