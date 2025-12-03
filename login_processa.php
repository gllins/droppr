<?php
session_start();
include("conexao.php");

$email = $_POST['email'];
$senha = $_POST['senha'];

$stmt = $conn->prepare("SELECT id, nome, senha, tentativas, bloqueado FROM usuario WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $usuario = $result->fetch_assoc();

    if ($usuario['bloqueado']) {
        echo " Conta bloqueada após 3 tentativas.<br>";
        echo " <a href='esqueci.php'>Clique aqui para redefinir sua senha</a>";
        exit();
    }

    if (password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario'] = $usuario['nome'];
         $_SESSION['usuario_id'] = $usuario['id'];

        
        $conn->query("UPDATE usuario SET tentativas = 0, bloqueado = 0 WHERE id = {$usuario['id']}");

        header("Location: boas_vindas.php");
        exit();
    } else {
        $tentativas = $usuario['tentativas'] + 1;
        $bloqueado = ($tentativas >= 3) ? 1 : 0;

        $stmtUpdate = $conn->prepare("UPDATE usuario SET tentativas = ?, bloqueado = ? WHERE id = ?");
        $stmtUpdate->bind_param("iii", $tentativas, $bloqueado, $usuario['id']);
        $stmtUpdate->execute();

        if ($bloqueado) {
            echo " Conta bloqueada após 3 tentativas.<br>";
            echo " <a href='esqueci.php'>Clique aqui para redefinir sua senha</a>";
        } else {
            echo " Senha incorreta. Tentativa $tentativas de 3.<br>";
            echo " <a href='login.html'>Tentar novamente</a>";
        }
    }
} else {
    echo " Usuário não encontrado.<br>";
    echo " <a href='login.html'>Voltar ao login</a>";
}

$conn->close();