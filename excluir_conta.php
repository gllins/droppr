<?php
session_start();
include("conexao.php");
if (!isset($_SESSION['usuario_id'])) {
    header("Location: tela_inicial.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// exclui msg do user deletado
$stmt = $conn->prepare("DELETE FROM mensagens WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->close();

// exclui as curtidas do user deletado
$stmt = $conn->prepare("DELETE FROM curtidas WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->close();

// exclui os relacionamentos do user deletado
$stmt = $conn->prepare("DELETE FROM seguindo WHERE usuario_id = ? OR amigo_id = ?");
$stmt->bind_param("ii", $usuario_id, $usuario_id);
$stmt->execute();
$stmt->close();

// exclui o user deletado
$stmt = $conn->prepare("DELETE FROM usuario WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->close();

// destrói sessão e leva pra tela inicial
session_destroy();
header("Location: tela_inicial.php?msg=conta_excluida");
exit;
?>