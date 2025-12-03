<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="icon.png" alt="logo">
        </div>

        <form method="post" action="redefinir.php">
            <h2>Redefinir Senha</h2>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Digite seu email" required>

            <label for="nova_senha">Nova Senha</label>
            <input type="password" id="nova_senha" name="nova_senha" placeholder="Digite sua nova senha" required>

            <input type="submit" value="Redefinir Senha">
        </form>

        <div class="extra-links">
            <a href="login.html">Voltar ao login</a>
            <a href="cadastro.php">Criar conta</a>
        </div>
    </div>
</body>
</html>