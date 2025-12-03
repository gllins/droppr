<?php

$mensagem = "";
$sucesso = false;
$conn = null;

// processar o formulário apenas se for método POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $host = 'localhost';
    $db = 'escola';
    $user = 'root';
    $pass = '';

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Erro de Conexão: " . $conn->connect_error);
    }

    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

  $dominioAluno = "@aluno.colegiodom.com.br";
  $dominioProf  = "@prof.colegiodom.com.br";

// validação simples de cadastro
if (empty($nome) || empty($email) || empty($senha)) {
    $mensagem = "Preencha todos os campos!";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $mensagem = "Formato de email inválido";
} else {
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO usuario (nome, email, senha) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nome, $email, $hash);

    if ($stmt->execute()) {
        $mensagem = "Usuário cadastrado com sucesso!";
        $sucesso = true;
    } else {
        $mensagem = "Erro ao cadastrar: " . $stmt->error;
    }
    $stmt->close();
}
    
    // fechar a conexão apenas se foi aberta
    if ($conn) {
        $conn->close();
    }
}
?>
  <!-- Form de cadastro --> 
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
   <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="icon.png" alt="logo">
        </div>
        
        <form action="" method="POST">
            <h2>Crie sua conta</h2>
            <?php 
            if (!empty($mensagem)) {
                $classe = $sucesso ? 'sucesso' : 'erro';
                echo "<p class='mensagem $classe'>$mensagem</p>";
            }
            ?>

            <?php if (!$sucesso): ?>
                <input type="text" name="nome" placeholder="Nome de usuário" required>
                <input type="email" name="email" placeholder="E-mail" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit">Cadastrar</button>
            <?php else: ?>
                <div style="text-align: center; padding: 20px 0;">
                    <p style="margin-bottom: 20px;">Cadastro realizado com sucesso!</p>
                    <a href="login.html" style="color: #015f4b; text-decoration: none; font-weight: bold; padding: 10px 20px; border: 1px solid #015f4b; border-radius: 50px; display: inline-block;">Fazer login</a>
                </div>
            <?php endif; ?>
        </form>
         <!-- ir pro login --> 
        <div class="login-link">
            Já tem uma conta? <a href="login.html">Conecte-se</a>
        </div>
    </div>
</body>
</html>