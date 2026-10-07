

<?php
// Alteração: inicializa a sessão e carrega as rotinas comuns.
session_start();
require_once __DIR__ . '/funcoes.php';

$email = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Alteração: valida a entrada e trata arquivo XML vazio ou inválido.
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');
    try {
        $usuarios = carregarXml(ARQUIVO_USUARIOS, 'usuarios');
        foreach ($usuarios->usuario as $usuario) {
            $hash = (string) $usuario->senha;
            // Alteração: aceita registros antigos em MD5 e os atualiza após o login.
            $senhaCorreta = password_verify($senha, $hash)
                || (preg_match('/^[a-f0-9]{32}$/i', $hash) === 1 && hash_equals(strtolower($hash), md5($senha)));
            if (strcasecmp((string) $usuario->email, $email) === 0 && $senhaCorreta) {
                session_regenerate_id(true);
                $_SESSION['usuario'] = (string) $usuario->email;
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $usuario->senha = password_hash($senha, PASSWORD_DEFAULT);
                    salvarXml($usuarios, ARQUIVO_USUARIOS);
                }
                // Alteração: redireciona após o POST para evitar reenvio do formulário.
                header('Location: listar.php');
                exit;
            }
        }
        $erro = 'Login inválido.';
    } catch (RuntimeException $excecao) {
        $erro = $excecao->getMessage();
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar | Fórum</title>
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
        .auth-page {
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 36px 20px;
            background: radial-gradient(ellipse at 15% 15%, rgba(99, 102, 241, .1), transparent 36%), radial-gradient(ellipse at 85% 85%, rgba(14, 165, 233, .08), transparent 34%), var(--page);
        }
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
            <p class="eyebrow">Boas-vindas de volta</p>
            <h1>Entrar na sua conta</h1>
            <p class="intro">Acesse a comunidade para participar das conversas.</p>
            <?php if ($erro !== ''): ?><p class="alert alert-error"><?= escapar($erro) ?></p><?php endif; ?>
            <form class="stacked-form" method="post">
                <label>E-mail
                    <input type="email" name="email" value="<?= escapar($email) ?>" autocomplete="email" required>
                </label>
                <label>Senha
                    <input type="password" name="senha" autocomplete="current-password" required>
                </label>
                <button type="submit">Entrar</button>
            </form>
            <p class="auth-footer">Ainda não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
            <p class="back-link"><a href="listar.php">Voltar aos tópicos</a></p>
        </section>
    </main>
</body>
</html>