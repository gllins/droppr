<?php
session_start();
require_once "conexao.php";

if (!isset($_GET['mensagem_id'])) {
    die("ID inválido.");
}

$post_id = intval($_GET['mensagem_id']);
$usuario_id = $_SESSION['usuario_id'] ?? null;

// BUSCAR POST
$stmt = $conn->prepare("SELECT * FROM mensagens WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    die("Post não encontrado.");
}

// VERIFICA SE O POST É DO USUÁRIO LOGADO
if ($usuario_id != $post['usuario_id']) {
    die("Você não tem permissão para editar este post.");
}

// SALVAR ALTERAÇÃO
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $nova_mensagem = trim($_POST['mensagem']);

    if (!empty($nova_mensagem)) {
        $stmt = $conn->prepare("
            UPDATE mensagens 
            SET mensagem = ?, editado = 1 
            WHERE id = ?
        ");
        $stmt->bind_param("si", $nova_mensagem, $post_id);
        $stmt->execute();
    }

    header("Location: comentarios.php?mensagem_id=$post_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Editar Mensagem</title>

<style>
    :root {
        --main-color: #015f4b;
        --bg-color: #15202b;
        --text-color: #e7e9ea;
        --card-bg: #192734;
        --border-color: #38444d;
    }

    body {
        background-color: var(--bg-color);
        color: var(--text-color);
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
    }

    .container {
        max-width: 600px;
        margin: auto;
    }

    .card {
        background-color: var(--card-bg);
        padding: 20px;
        border-radius: 16px;
        border: 1px solid var(--border-color);
    }

    textarea {
        width: 100%;
        background: var(--bg-color);
        border: 1px solid var(--border-color);
        color: var(--text-color);
        padding: 12px;
        border-radius: 8px;
        resize: vertical;
        font-size: 15px;
        box-sizing: border-box;
    }

    button {
        margin-top: 15px;
        width: 100%;
        background: var(--text-color);
        color: var(--main-color);
        padding: 10px;
        border-radius: 25px;
        border: 1px solid var(--main-color);
        font-weight: bold;
        cursor: pointer;
    }

    button:hover {
        background: var(--main-color);
        color: var(--text-color);
    }

    a {
        display: block;
        margin-top: 15px;
        text-align: center;
        color: var(--main-color);
        font-weight: bold;
        text-decoration: none;
    }

</style>
</head>

<body>
<div class="container">
    <h2 style="text-align:center;">Editar Mensagem</h2>

    <form method="POST" class="card">
        <textarea name="mensagem" rows="6" required><?= htmlspecialchars($post['mensagem']); ?></textarea>
        <button type="submit">Salvar Alterações</button>
    </form>

    <a href="boas_vindas.php?mensagem_id=<?= $post_id; ?>">Voltar</a>
</div>
</body>
</html>