<?php
// Alteração: usa as funções seguras de leitura, gravação e escape.
require_once __DIR__ . '/funcoes.php';

// Alteração: mantém os dados preenchidos e apresenta erros de validação.
$nome = '';
$celular = '';
$email = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Alteração: normaliza e valida todos os campos antes de gravá-los.
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $celular = trim((string) ($_POST['celular'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');

    if ($nome === '' || $celular === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Preencha nome, celular e um e-mail válido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve possuir pelo menos 6 caracteres.';
    } else {
        try {
            $usuarios = carregarXml(ARQUIVO_USUARIOS, 'usuarios');
            // Alteração: impede o cadastro duplicado do mesmo e-mail.
            foreach ($usuarios->usuario as $usuario) {
                if (strcasecmp((string) $usuario->email, $email) === 0) {
                    $erro = 'Este e-mail já está cadastrado.';
                    break;
                }
            }
            if ($erro === '') {
                $novo = $usuarios->addChild('usuario');
                adicionarTextoXml($novo, 'nome', $nome);
                adicionarTextoXml($novo, 'celular', $celular);
                adicionarTextoXml($novo, 'email', $email);
                // Alteração: substitui MD5 pelo algoritmo seguro de hash de senha do PHP.
                adicionarTextoXml($novo, 'senha', password_hash($senha, PASSWORD_DEFAULT));
                salvarXml($usuarios, ARQUIVO_USUARIOS);
                // Alteração: corrige a codificação da mensagem exibida.
                echo '<!doctype html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Cadastro concluído | Fórum</title><style>:root{--page:#f5f7fb;--surface:#fff;--ink:#19243b;--muted:#68758c;--line:#e3e8f0;--primary:#4f46e5;--primary-dark:#4338ca;--shadow:0 18px 50px rgba(31,45,77,.08)}*{box-sizing:border-box}body{min-width:320px;min-height:100vh;margin:0;color:var(--ink);font:16px/1.6 Arial,sans-serif}a{color:var(--primary);font-weight:600;text-decoration:none}a:hover{color:var(--primary-dark)}h1,p{margin-top:0}.auth-page{display:grid;min-height:100vh;place-items:center;padding:36px 20px;background:radial-gradient(ellipse at 15% 15%,rgba(99,102,241,.1),transparent 36%),radial-gradient(ellipse at 85% 85%,rgba(14,165,233,.08),transparent 34%),var(--page)}.auth-card{width:min(100%,480px);padding:36px;border:1px solid var(--line);border-radius:18px;background:var(--surface);box-shadow:var(--shadow);text-align:center}.brand{display:inline-block;margin-bottom:30px;color:var(--ink);font:800 1.5rem \"Trebuchet MS\",Arial,sans-serif;letter-spacing:-.06em}.brand span{color:var(--primary)}.eyebrow{margin-bottom:8px;color:var(--primary);font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase}h1{margin-bottom:8px;font:700 1.9rem/1.2 \"Trebuchet MS\",Arial,sans-serif}.intro{margin-bottom:24px;color:var(--muted)}.button{display:inline-flex;min-height:44px;align-items:center;justify-content:center;margin-top:24px;padding:10px 18px;border:1px solid var(--primary);border-radius:10px;background:var(--primary);color:#fff;font-weight:700}.button:hover{background:var(--primary-dark);color:#fff}@media(max-width:600px){.auth-page{padding:20px 14px}.auth-card{padding:26px 22px}}</style></head><body>';
                echo '<main class="auth-page"><section class="auth-card success-card"><a class="brand auth-brand" href="listar.php">Fórum<span>.</span></a><p class="eyebrow">Tudo pronto</p><h1>Cadastro realizado!</h1><p class="intro">Sua conta foi criada. Agora você já pode participar das conversas.</p><a class="button button-full" href="login.php">Fazer login</a></section></main></body></html>';
                exit;
            }
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
    <title>Criar conta | Fórum</title>
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
        .auth-card { width: min(100%, 480px); padding: 36px; border: 1px solid var(--line); border-radius: 18px; background: var(--surface); box-shadow: var(--shadow); }
        .brand { display: inline-block; margin-bottom: 30px; color: var(--ink); font: 800 1.5rem "Trebuchet MS", Arial, sans-serif; letter-spacing: -.06em; }
        .brand:hover { color: var(--ink); text-decoration: none; }
        .brand span { color: var(--primary); }
        .eyebrow { margin-bottom: 8px; color: var(--primary); font-size: .75rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        h1 { margin-bottom: 8px; font: 700 1.9rem/1.2 "Trebuchet MS", Arial, sans-serif; letter-spacing: -.04em; }
        .intro { margin-bottom: 24px; color: var(--muted); }
        .alert { margin-bottom: 20px; padding: 13px 16px; border: 1px solid #ffd5d5; border-radius: 10px; background: #fff0f0; color: var(--danger); }
        .stacked-form { display: grid; gap: 17px; }
        label { display: grid; gap: 7px; font-size: .9rem; font-weight: 600; }
        input { width: 100%; min-height: 46px; padding: 11px 13px; border: 1px solid #d7deea; border-radius: 9px; outline: none; background: #fff; color: var(--ink); font: inherit; font-weight: 400; }
        input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, .13); }
        button { min-height: 44px; margin-top: 4px; padding: 10px 18px; border: 1px solid var(--primary); border-radius: 10px; background: var(--primary); color: #fff; cursor: pointer; font: inherit; font-weight: 700; }
        button:hover { border-color: var(--primary-dark); background: var(--primary-dark); }
        .field-hint { color: var(--muted); font-size: .8rem; font-weight: 400; }
        .auth-footer { margin: 24px 0 0; padding-top: 20px; border-top: 1px solid var(--line); color: var(--muted); text-align: center; }
        .back-link { margin: 16px 0 0; font-size: .9rem; text-align: center; }
        @media (max-width: 600px) { .auth-page { padding: 20px 14px; } .auth-card { padding: 26px 22px; } }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; } }
    </style>
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <a class="brand auth-brand" href="listar.php">Fórum<span>.</span></a>
            <p class="eyebrow">Faça parte</p>
            <h1>Crie sua conta</h1>
            <p class="intro">Entre para compartilhar ideias e conversar com a comunidade.</p>
            <?php if ($erro !== ''): ?><p class="alert alert-error"><?= escapar($erro) ?></p><?php endif; ?>
            <form class="stacked-form" method="post">
                <label>Nome
                    <input type="text" name="nome" value="<?= escapar($nome) ?>" autocomplete="name" required>
                </label>
                <label>Celular
                    <input type="tel" name="celular" value="<?= escapar($celular) ?>" autocomplete="tel" required>
                </label>
                <label>E-mail
                    <input type="email" name="email" value="<?= escapar($email) ?>" autocomplete="email" required>
                </label>
                <label>Senha
                    <input type="password" name="senha" minlength="6" autocomplete="new-password" required>
                    <small class="field-hint">Use pelo menos 6 caracteres.</small>
                </label>
                <button type="submit">Cadastrar</button>
            </form>
            <p class="auth-footer">Já tem conta? <a href="login.php">Entrar</a></p>
            <p class="back-link"><a href="listar.php">Voltar aos tópicos</a></p>
        </section>
    </main>
</body>
</html>