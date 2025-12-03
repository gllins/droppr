<?php
session_start();
include("conexao.php");
// garantir login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// busca dados do user
$stmt = $conn->prepare("SELECT nome, perfil_img FROM usuario WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$res = $stmt->get_result();
$usuario = $res->fetch_assoc();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['acao']) && $_POST['acao'] === "atualizar") {
    $novo_nome = $_POST['nome'];
    $nova_senha = $_POST['senha'];
    $caminho = $usuario['perfil_img']; // mantém a foto antiga se não trocar

    // se enviou nova foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nome_arquivo = "perfil_" . $usuario_id . "." . $extensao;
        $caminho = "uploads/" . $nome_arquivo;
        move_uploaded_file($_FILES['foto']['tmp_name'], $caminho);
    }

    // se senha não for vazia, dá update
    if (!empty($nova_senha)) {
        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $stmt_up = $conn->prepare("UPDATE usuario SET nome = ?, senha = ?, perfil_img = ? WHERE id = ?");
        $stmt_up->bind_param("sssi", $novo_nome, $hash, $caminho, $usuario_id);
    } else {
        $stmt_up = $conn->prepare("UPDATE usuario SET nome = ?, perfil_img = ? WHERE id = ?");
        $stmt_up->bind_param("ssi", $novo_nome, $caminho, $usuario_id);
    }

    if ($stmt_up->execute()) {
        $msg = "Perfil atualizado!";
        $_SESSION['usuario'] = $novo_nome;
        $usuario['nome'] = $novo_nome;
        $usuario['perfil_img'] = $caminho;
    } else {
        $msg = "Erro ao atualizar.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Editar Perfil</title>
<style>
     :root {
        --main-color: #015f4b;
        --bg-color: #15202b;
        --text-color: #ffffff;
        --card-bg: #192734;
        --border-color: #38444d;
        --hover-bg: rgba(1, 95, 75, 0.1);
        --input-bg: #1e2732;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }

    body { background-color: var(--bg-color); color: var(--text-color); line-height: 1.5; padding: 20px; }

    .container { max-width: 600px; margin: 0 auto; }

    h2 { font-size: 24px; margin-bottom: 25px; text-align: center; }

    .mensagem-status {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        font-weight: 600;
    }
    .mensagem-sucesso { background-color: rgba(0, 255, 0, 0.1); color: #00cc00; border: 1px solid #00cc00; }
    .mensagem-erro { background-color: rgba(255, 0, 0, 0.1); color: #ff3333; border: 1px solid #ff3333; }

    .foto-container { text-align: center; margin-bottom: 25px; }
    .foto-perfil { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid var(--main-color); margin-bottom: 10px; }

    .form-container {
        background-color: var(--card-bg);
        border-radius: 16px;
        padding: 25px;
        border: 1px solid var(--border-color);
        margin-bottom: 20px;
    }

    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-color); }

    input[type="text"],
    input[type="password"],
    input[type="file"] {
        width: 100%;
        padding: 12px 16px;
        background-color: var(--input-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-color);
        font-size: 16px;
    }

    input[type="text"]:focus,
    input[type="password"]:focus { outline: none; border-color: var(--main-color); }

    input[type="file"] { padding: 10px; cursor: pointer; color: var(--text-color); }

    .btn {
        display: block;
        width: 100%;
        text-align: center;
        background: #fff;
        color: var(--main-color);
        padding: 12px 0;
        border-radius: 25px;
        font-weight: bold;
        margin: 10px 0;
        text-decoration: none;
        transition: background 0.3s, color 0.3s;
        border: 2px solid var(--main-color);
        cursor: pointer;
        font-size: 16px;
    }
    .btn:hover { background: var(--main-color); color: #fff; }

    .btn-danger {
        background: #ff3333;
        color: #fff;
        border: 2px solid #ff3333;
    }
    .btn-danger:hover { background: #cc0000; border-color: #cc0000; }

    .voltar-link {
        display: inline-block;
        color: var(--main-color);
        text-decoration: none;
        font-weight: 600;
        padding: 8px 16px;
        border: 1px solid var(--main-color);
        border-radius: 9999px;
        transition: all 0.2s ease;
        text-align: center;
        width: 100%;
        margin-top: 15px;
    }
    .voltar-link:hover { background-color: var(--main-color); color: var(--text-color); }

    @media (max-width: 500px) {
        .form-container { padding: 20px 15px; }
        .foto-perfil { width: 100px; height: 100px; }
    }
   
    </style>
</head>
<body>
<div class="container">

<h2>Editar Perfil</h2>

<?php if (!empty($msg)) echo "<p class='mensagem-status ".(strpos($msg,'Erro')===false?'mensagem-sucesso':'mensagem-erro')."'>$msg</p>"; ?>

<!-- Foto atual -->
<div class="foto-container">
    <img src="<?php echo htmlspecialchars($usuario['perfil_img']); ?>" alt="Foto de Perfil" class="foto-perfil">
</div>

<!-- Formulário de atualização -->
<form method="POST" enctype="multipart/form-data" class="form-container">
    <input type="hidden" name="acao" value="atualizar">
    <div class="form-group">
        <label>Nome:</label>
        <input type="text" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>">
    </div>

    <div class="form-group">
        <label>Nova Senha (deixe em branco para não mudar):</label>
        <input type="password" name="senha">
    </div>

    <div class="form-group">
        <label>Nova Foto de perfil:</label>
        <input type="file" name="foto" accept="image/*">
    </div>

    <input type="submit" value="Salvar" class="btn">
</form>

<!-- Formulário de exclusão -->
<form method="POST" action="excluir_conta.php" onsubmit="return confirm('Tem certeza que deseja excluir sua conta? Esta ação não poderá ser desfeita!');">
    <button type="submit" class="btn btn-danger">Excluir Conta</button>
</form>

<a href="boas_vindas.php" class="voltar-link">Voltar ao perfil</a>

</div>
</body>
</html>