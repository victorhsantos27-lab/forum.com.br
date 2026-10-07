<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
    <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: Arial, sans-serif;
        background: linear-gradient(135deg, #68abe9, #6d6fd2);
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
    }

    form {
        background-color: #ffffff;
        width: 100%;
        max-width: 400px;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    }

    form h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #222;
    }

    label {
        display: block;
        margin-bottom: 6px;
        font-weight: bold;
        color: #333;
    }

    input,
    button {
        width: 100%;
        padding: 12px;
        margin-bottom: 15px;
        border-radius: 8px;
        font-size: 15px;
    }

    input {
        border: 1px solid #ccc;
        outline: none;
        transition: 0.3s;
    }

    input:focus {
        border-color: #1500ff;
        box-shadow: 0 0 5px rgba(21, 0, 255, 0.3);
    }

    button {
        background: linear-gradient(135deg, #1500ff, #4d3cff);
        color: white;
        border: none;
        cursor: pointer;
        font-weight: bold;
        transition: 0.3s;
    }

    button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(21, 0, 255, 0.4);
    }

    button:active {
        transform: translateY(0);
    }
</style>
</head>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarios = simplexml_load_file("usuarios.xml");
    $novo = $usuarios->addChild("usuario");
    $novo->addChild("nome", $_POST['nome']);
    $novo->addChild("celular", $_POST['celular']);
    $novo->addChild("email", $_POST['email']);
    $novo->addChild("senha", md5($_POST['senha']));
    $usuarios->asXML("usuarios.xml");
    echo "Usuário cadastrado com sucesso! <a href='login.php'>Fazer login</a>";
} else {
?>
    <form action="cadastro.php" method="post">
        <label for="nome">Nome:</label>
        <input type="text" name="nome" required><br><br>

        <label for="celular">Celular:</label>
        <input type="text" name="celular" required><br><br>

        <label for="email">Email:</label>
        <input type="email" name="email" required><br><br>

        <label for="senha">Senha:</label>
        <input type="password" name="senha" required><br><br>

        <button type="submit">Cadastrar</button><br><br>
    </form>
<?php } ?>