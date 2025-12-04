<?php
session_start();
require_once "conexao.php";

if (!isset($_GET['mensagem_id'])) {
    die("Mensagem inválida.");
}

$mensagem_id = intval($_GET['mensagem_id']);
$usuario_id = $_SESSION['usuario_id'] ?? null;

// busca msg original
$stmt = $conn->prepare("SELECT m.*, u.nome, u.perfil_img 
                        FROM mensagens m 
                        JOIN usuario u ON u.id = m.usuario_id 
                        WHERE m.id = ?");
$stmt->bind_param("i", $mensagem_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    die("Post não encontrado.");
}

// novo comentario
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $comentario = trim($_POST['comentario']);

    if (!empty($comentario)) {
        $stmt = $conn->prepare("INSERT INTO comentarios (mensagem_id, usuario_id, comentario) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $mensagem_id, $usuario_id, $comentario);
        $stmt->execute();
    }

    header("Location: comentarios.php?mensagem_id=$mensagem_id");
    exit;
}

// busca comentarios
$stmt = $conn->prepare("SELECT c.*, u.nome, u.perfil_img 
                        FROM comentarios c 
                        JOIN usuario u ON u.id = c.usuario_id 
                        WHERE c.mensagem_id = ?
                        ORDER BY c.data_envio ASC");
$stmt->bind_param("i", $mensagem_id);
$stmt->execute();
$comentarios = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Comentários</title>

<style>
    :root {
        --main-color: #015f4b;
        --bg-color: #15202b;
        --text-color: #e7e9ea;
        --card-bg: #192734;
        --border-color: #38444d;
        --hover-bg: rgba(1, 95, 75, 0.1);
    }

    body {
        background-color: var(--bg-color);
        color: var(--text-color);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        margin: 0;
        padding: 20px;
    }

    .container {
        max-width: 600px;
        margin: 0 auto;
    }

    .titulo {
        text-align: center;
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 20px;
    }

    .card {
        background-color: var(--card-bg);
        padding: 15px;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        margin-bottom: 20px;
    }

    .perfil-box {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .foto-perfil {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--main-color);
    }

    .nome {
        font-weight: bold;
        font-size: 16px;
    }

    .mensagem-texto {
        margin-top: 8px;
        font-size: 15px;
    }

    .comentario-item {
        display: flex;
        gap: 10px;
        padding: 12px;
        border-bottom: 1px solid var(--border-color);
    }

    .comentario-item:last-child {
        border-bottom: none;
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

        /* 🔥 AQUI ESTÁ O AJUSTE QUE CORRIGE O ALINHAMENTO */
        box-sizing: border-box;
        display: block;
        margin: 0;
    }

    button {
        margin-top: 10px;
        width: 100%;
        background: var(--text-color);
        color: var(--main-color);
        padding: 10px;
        border-radius: 25px;
        border: 1px solid var(--main-color);
        font-weight: bold;
        cursor: pointer;
        transition: 0.2s;
    }

    button:hover {
        background: var(--main-color);
        color: var(--text-color);
    }

    .voltar {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: var(--main-color);
        font-weight: bold;
        text-decoration: none;
    }

    .voltar:hover {
        text-decoration: underline;
    }
</style>
</head>

<body>
<div class="container">

    <div class="titulo">Post original</div>

    <div class="card">
        <div class="perfil-box">
            <img src="<?= htmlspecialchars($post['perfil_img']); ?>" class="foto-perfil">
            <div>
                <div class="nome"><?= htmlspecialchars($post['nome']); ?></div>
                <p class="mensagem-texto"><?= nl2br(htmlspecialchars($post['mensagem'])); ?></p>
            </div>
        </div>
    </div>

    <div class="titulo">Comentários</div>

    <?php while ($c = $comentarios->fetch_assoc()): ?>
        <div class="comentario-item">
            <img src="<?= htmlspecialchars($c['perfil_img']); ?>" class="foto-perfil">

            <div>
                <div class="nome"><?= htmlspecialchars($c['nome']); ?></div>
                <p class="mensagem-texto"><?= nl2br(htmlspecialchars($c['comentario'])); ?></p>
                <span style="font-size:12px; color:#8b98a5;">
                    <?= date("d/m/Y H:i", strtotime($c['data_envio'])); ?>
                </span>
            </div>
        </div>
    <?php endwhile; ?>

    <div class="titulo">Escrever comentário</div>

    <form method="POST" class="card">
        <textarea name="comentario" rows="3" required></textarea>
        <button type="submit">Enviar</button>
    </form>

    <a href="boas_vindas.php" class="voltar">Voltar</a>

</div>
</body>
</html>