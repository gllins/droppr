<?php
session_start();

include("conexao.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mensagem_id = $_GET['mensagem_id'];

// verifica se já curtiu
$stmt = $conn->prepare("SELECT id FROM curtidas WHERE usuario_id = ? AND mensagem_id = ?");
$stmt->bind_param("ii", $usuario_id, $mensagem_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // remove curtida
    $stmt = $conn->prepare("DELETE FROM curtidas WHERE usuario_id = ? AND mensagem_id = ?");
    $stmt->bind_param("ii", $usuario_id, $mensagem_id);
    $stmt->execute();
} else {
    // add curtida
    $stmt = $conn->prepare("INSERT INTO curtidas (usuario_id, mensagem_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $usuario_id, $mensagem_id);
    $stmt->execute();
}

header("Location: " . $_SERVER['HTTP_REFERER']); // volta pra página anterior
exit;
?>