<?php
session_start();
include("conexao.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if (isset($_GET['mensagem_id'])) {
    $mensagem_id = intval($_GET['mensagem_id']);

    // só deleta se a mensagem pertencer ao usuário logado
    $stmt = $conn->prepare("DELETE FROM mensagens WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $mensagem_id, $usuario_id);
    $stmt->execute();
}

// volta para a tela de boas-vindas
header("Location: boas_vindas.php");
exit();
?>