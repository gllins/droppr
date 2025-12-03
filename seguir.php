<?php
session_start();
// Conexão com o banco
$host = 'localhost';
$db = 'escola';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
$meu_id = $_SESSION['usuario_id']; // pega o id do user
$amigo_id = $_GET['amigo_id'];    // pega o id do amigo

$stmt = $conn->prepare("INSERT INTO seguindo (usuario_id, amigo_id) VALUES (?, ?)");
$stmt->bind_param("ii", $meu_id, $amigo_id);
$stmt->execute();

header("Location: encontrar.php"); // volta para a página de encontrar usuários
exit;
?>