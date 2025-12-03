<?php
session_start();

include("conexao.php");

// vê se tá logado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}

$meu_id = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario'];

// pega geral, menos o logado
$stmt = $conn->prepare("SELECT id, nome, perfil_img FROM usuario WHERE id != ?");
$stmt->bind_param("i", $meu_id);
$stmt->execute();
$result = $stmt->get_result();

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Encontrar Usuários</title>

<style>
  :root {
        --main-color: #015f4b;
        --bg-color: #15202b;
        --text-color: #ffffff;
        --card-bg: #15202b;
        --border-color: #2f3336;
        --hover-bg: rgba(1, 95, 75, 0.1);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    body {
        background-color: var(--bg-color);
        color: var(--text-color);
        line-height: 1.5;
        padding: 20px;
    }

    .container {
        max-width: 600px;
        margin: 0 auto;
    }

    h1 {
        font-size: 24px;
        margin-bottom: 5px;
    }

    h2 {
        font-size: 20px;
        color: var(--main-color);
        margin-bottom: 20px;
    }

    .usuarios-lista {
        list-style: none;
        margin-bottom: 25px;
    }

    .usuario-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid var(--border-color);
        transition: background-color 0.2s;
    }

    .usuario-item:hover {
        background-color: var(--hover-bg);
    }

    .foto-perfil {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--main-color);
        margin-right: 15px;
        flex-shrink: 0;
    }

    .usuario-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .usuario-nome {
        font-weight: 700;
        font-size: 16px;
    }

    .usuario-info a {
        color: var(--main-color);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
        display: inline-block;
    }

    .usuario-info a:hover {
        text-decoration: underline;
    }

    .estado-vazio {
        text-align: center;
        padding: 40px 20px;
        color: #6e767d;
        font-size: 16px;
    }

    .voltar-link {
        display: inline-block;
        color: var(--main-color);
        text-decoration: none;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 9999px;
        transition: all 0.2s ease;
    }

    .voltar-link:hover {
        text-decoration: underline;
    }

    @media (max-width: 500px) {
        .usuario-item {
            flex-direction: column;
            align-items: flex-start;
            text-align: center;
        }
    .foto-perfil {
            margin-right: 0;
            margin-bottom: 10px;
        }

        .usuario-info {
            width: 100%;
            text-align: center;
        }
    }

  
</style>
</head>
<body>
<!-- listinha dos users existentes --> 
<div class="container">
<h1>Olá, <?php echo htmlspecialchars($nome_usuario); ?>!</h1>
<h2>Usuários cadastrados:</h2>

<?php if (count($usuarios) > 0): ?>
    <ul class="usuarios-lista">
        <?php foreach ($usuarios as $u): ?>
            <li class="usuario-item">
                <img src="<?php echo htmlspecialchars($u['perfil_img']); ?>" alt="Foto de Perfil" class="foto-perfil">
                <div class="usuario-info">
                    <div class="usuario-nome"><?php echo htmlspecialchars($u['nome']); ?></div>
                    <?php
                    $stmt2 = $conn->prepare("SELECT * FROM seguindo WHERE usuario_id = ? AND amigo_id = ?");
                    $stmt2->bind_param("ii", $meu_id, $u['id']);
                    $stmt2->execute();
                    $res = $stmt2->get_result();
                    if ($res->num_rows > 0):
                    ?>
                        <a href="desseguir.php?amigo_id=<?php echo $u['id']; ?>">Deixar de seguir</a>
                    <?php else: ?>
                        <a href="seguir.php?amigo_id=<?php echo $u['id']; ?>">Seguir</a>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p class="estado-vazio">Nenhum outro usuário encontrado.</p>
<?php endif; ?>

<a href="boas_vindas.php" class="voltar-link">Voltar ao perfil</a>
</div>
</body>
</html>