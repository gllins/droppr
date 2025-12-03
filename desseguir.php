<?php
session_start();
include("conexao.php");

// remove amigo
$meu_id = $_SESSION['usuario_id'];
$amigo_id = $_GET['amigo_id'];

$stmt = $conn->prepare("DELETE FROM seguindo WHERE usuario_id = ? AND amigo_id = ?");
$stmt->bind_param("ii", $meu_id, $amigo_id);
$stmt->execute();

header("Location: encontrar.php");
exit;
?>