<?php

// Alteração: centraliza caminhos e corrige a leitura de arquivos XML vazios.
const ARQUIVO_USUARIOS = __DIR__ . DIRECTORY_SEPARATOR . 'usuarios.xml';
const ARQUIVO_TOPICOS = __DIR__ . DIRECTORY_SEPARATOR . 'topicos.xml';

function carregarXml(string $arquivo, string $raiz): SimpleXMLElement
{
    // Alteração: cria uma estrutura válida quando o arquivo ainda não tem conteúdo.
    $conteudo = is_file($arquivo) ? file_get_contents($arquivo) : false;
    if ($conteudo === false || trim($conteudo) === '') {
        return new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $raiz . '/>');
    }

    // Alteração: bloqueia acesso de rede pelo parser e detecta XML corrompido.
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($conteudo, SimpleXMLElement::class, LIBXML_NONET);
    libxml_clear_errors();
    if ($xml === false || $xml->getName() !== $raiz) {
        throw new RuntimeException('O arquivo de dados está corrompido ou possui uma estrutura inválida.');
    }
    return $xml;
}

function salvarXml(SimpleXMLElement $xml, string $arquivo): void
{
    // Alteração: grava com bloqueio e verifica falhas de escrita.
    $conteudo = $xml->asXML();
    if ($conteudo === false || file_put_contents($arquivo, $conteudo, LOCK_EX) === false) {
        throw new RuntimeException('Não foi possível salvar os dados.');
    }
}

function adicionarTextoXml(SimpleXMLElement $pai, string $nome, string $valor): SimpleXMLElement
{
    // Alteração: adiciona caracteres especiais sem interpretá-los como entidades XML.
    $filho = $pai->addChild($nome);
    $filho[0] = $valor;
    return $filho;
}

function escapar(mixed $valor): string
{
    // Alteração: impede injeção de HTML ao exibir dados salvos.
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function obterIndice(mixed $valor): ?int
{
    // Alteração: valida índices antes de acessar tópicos ou comentários.
    if (!is_string($valor) && !is_int($valor)) {
        return null;
    }
    $indice = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    return $indice === false ? null : $indice;
}

function tokenCsrf(): string
{
    // Alteração: gera proteção para formulários que modificam dados.
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfValido(mixed $token): bool
{
    // Alteração: confere se o formulário foi emitido pela sessão atual.
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function encerrarComErro(string $mensagem, int $status = 400): never
{
    // Alteração: apresenta erros com status HTTP e saída segura.
    http_response_code($status);
    echo '<!doctype html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Erro | Fórum</title><style>:root{--page:#f5f7fb;--surface:#fff;--ink:#19243b;--muted:#68758c;--line:#e3e8f0;--primary:#4f46e5;--primary-dark:#4338ca;--danger:#c2414b;--shadow:0 18px 50px rgba(31,45,77,.08)}*{box-sizing:border-box}body{min-width:320px;min-height:100vh;margin:0;color:var(--ink);font:16px/1.6 Arial,sans-serif}a{color:var(--primary);font-weight:600;text-decoration:none}a:hover{color:var(--primary-dark);text-decoration:underline}h1,p{margin-top:0}.auth-page{display:grid;min-height:100vh;place-items:center;padding:36px 20px;background:radial-gradient(ellipse at 15% 15%,rgba(99,102,241,.1),transparent 36%),radial-gradient(ellipse at 85% 85%,rgba(14,165,233,.08),transparent 34%),var(--page)}.auth-card{width:min(100%,480px);padding:36px;border:1px solid var(--line);border-radius:18px;background:var(--surface);box-shadow:var(--shadow)}.brand{display:inline-block;margin-bottom:30px;color:var(--ink);font:800 1.5rem \"Trebuchet MS\",Arial,sans-serif;letter-spacing:-.06em}.brand span{color:var(--primary)}.eyebrow{margin-bottom:8px;color:var(--primary);font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase}h1{margin-bottom:8px;font:700 1.9rem/1.2 \"Trebuchet MS\",Arial,sans-serif;letter-spacing:-.04em}.alert{margin-bottom:20px;padding:13px 16px;border:1px solid #ffd5d5;border-radius:10px;background:#fff0f0;color:var(--danger)}.back-link{margin:16px 0 0;font-size:.9rem;text-align:center}@media(max-width:600px){.auth-page{padding:20px 14px}.auth-card{padding:26px 22px}}</style></head><body>';
    echo '<main class="auth-page"><section class="auth-card"><a class="brand auth-brand" href="listar.php">Fórum<span>.</span></a><p class="eyebrow">Não foi possível continuar</p><h1>Algo deu errado</h1>';
    echo '<p class="alert alert-error">' . escapar($mensagem) . '</p><p class="back-link"><a href="listar.php">Voltar aos tópicos</a></p></section></main></body></html>';
    exit;
}