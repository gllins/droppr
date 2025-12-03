<?php
include("conexao.php");
$email = $_POST['email'];
$novaSenha = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE usuario SET senha = ?, tentativas = 0, bloqueado = 0 WHERE email = ?");
$stmt->bind_param("ss", $novaSenha, $email);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Senha redefinida com sucesso! <a href='login.html'>Voltar ao login</a>";
} else {
    echo "Email não encontrado.";
}

$conn->close();
?>