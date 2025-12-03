<?php  
session_start();  
include("conexao.php");

// verifica se o usuário está logado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$nome_usuario = $_SESSION['usuario'];
$usuario_id = $_SESSION['usuario_id'];

// pega foto de perfil
$stmt_user = $conn->prepare("SELECT perfil_img FROM usuario WHERE id = ?");
$stmt_user->bind_param("i", $usuario_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$usuario = $result_user->fetch_assoc();

// atualiza foto de perfil  
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_perfil'])) {
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nome_arquivo = "perfil_" . $usuario_id . "." . $extensao;
        $caminho = "uploads/" . $nome_arquivo;
        move_uploaded_file($_FILES['foto']['tmp_name'], $caminho);

        $stmt_foto = $conn->prepare("UPDATE usuario SET perfil_img = ? WHERE id = ?");
        $stmt_foto->bind_param("si", $caminho, $usuario_id);
        $stmt_foto->execute();

        $usuario['perfil_img'] = $caminho;
    }
}

// variável de aviso
$aviso = "";

// envia nova mensagem  
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensagem'])) {
    $mensagem = trim($_POST['mensagem']);

    // corta se ultrapassar 140 chars
    if (strlen($mensagem) > 140) {
        $mensagem = substr($mensagem, 0, 140);
    }

    // checa quantas mensagens já tem
    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM mensagens WHERE usuario_id = ?");
    $stmt_count->bind_param("i", $usuario_id);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $row_count = $result_count->fetch_assoc();

    if ($row_count['total'] >= 40) {
        $aviso = "Você já atingiu o limite de 40 mensagens. Exclua alguma existente antes de postar outra.";
    } elseif (!empty($mensagem)) {
        $stmt = $conn->prepare("INSERT INTO mensagens (usuario_id, mensagem) VALUES (?, ?)");
        $stmt->bind_param("is", $usuario_id, $mensagem);
        $stmt->execute();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// busca mensagens do user
$stmt_mensagens = $conn->prepare(
    "SELECT m.id, m.mensagem, m.data_envio, u.nome, u.perfil_img,
    (SELECT COUNT(*) FROM curtidas c WHERE c.mensagem_id = m.id) AS total_curtidas,
    (SELECT COUNT(*) FROM curtidas c WHERE c.mensagem_id = m.id AND c.usuario_id = ?) AS ja_curtiu,
    (SELECT COUNT(*) FROM comentarios cm WHERE cm.mensagem_id = m.id) AS total_comentarios
    FROM mensagens m
    JOIN usuario u ON m.usuario_id = u.id
    WHERE m.usuario_id = ?
    ORDER BY m.data_envio DESC
"
);
$stmt_mensagens->bind_param("ii", $usuario_id, $usuario_id);
$stmt_mensagens->execute();
$mensagens = $stmt_mensagens->get_result();

// pega total de msgs para controlar o form
$total_msgs = 0;
$stmt_count2 = $conn->prepare("SELECT COUNT(*) as total FROM mensagens WHERE usuario_id = ?");
$stmt_count2->bind_param("i", $usuario_id);
$stmt_count2->execute();
$res2 = $stmt_count2->get_result();
if ($res2) {
    $total_msgs = $res2->fetch_assoc()['total'];
}
?>  

<!DOCTYPE html>  
<html lang="pt-BR">  
<head>  
    <meta charset="UTF-8">  
    <title>Boas-vindas</title>  
    <style>
      :root {
            --main-color: #015f4b;
            --bg-color: #15202b;
            --text-color: #e7e9ea;
            --card-bg: #192734;
            --border-color: #38444d;
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
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 22px;
            font-weight: 700;
        }

        .perfil {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 12px;
            padding: 15px;
            background-color: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
        }

        .foto-perfil {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--main-color);
        }

        .perfil h1 {
            font-size: 18px;
            font-weight: 700;
        }

        .msg-info {
            margin-bottom: 15px;
            padding: 10px 12px;
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            font-size: 14px;
            color: var(--text-color);
        }

        form {
            background-color: var(--card-bg);
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        textarea {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-color);
            color: var(--text-color);
            font-size: 15px;
            resize: vertical;
            margin-bottom: 5px;
        }

        textarea:focus {
            outline: none;
            border-color: var(--main-color);
        }

        input[type="submit"], button, .btn {
            background-color: var(--text-color);
            color: var(--main-color);
            border: 1px solid var(--main-color);
            border-radius: 50px;
            padding: 8px 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        input[type="submit"]:hover:enabled {
            background-color: var(--main-color);
            color: var(--text-color);
        }

        input[type="submit"]:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .aviso {
            background: #ffdddd;
            color: #900;
            padding: 10px;
            border: 1px solid #d00;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        h3 {
            margin: 20px 0 10px;
            font-size: 20px;
        }

        ul {
            list-style: none;
        }

        .mensagem {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
        }

        .mensagem:hover {
            background-color: var(--hover-bg);
        }

        .mensagem div {
            flex: 1;
        }

        .mensagem strong {
            display: block;
            margin-bottom: 5px;
            font-size: 15px;
        }

        .mensagem p {
            font-size: 15px;
            margin-bottom: 8px;
        }

        .mensagem-acoes a {
            color: var(--main-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .mensagem-acoes a:hover {
            text-decoration: underline;
        }

        .nav {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 25px;
        }

        .nav a {
            color: var(--main-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .nav a:hover {
            text-decoration: underline;
        }

        @media (max-width: 500px) {
            .perfil {
                flex-direction: column;
                text-align: center;
            }

            .mensagem {
                flex-direction: column;
            }

            .nav {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
        }
    </style>
</head>  
<body>  
<div class="container">  
    <div class="header">
        <h1>Olá, <?php echo htmlspecialchars($nome_usuario); ?>!</h1>
    </div>

    <div class="perfil">  
        <img src="<?php echo htmlspecialchars($usuario['perfil_img']); ?>" alt="Foto de Perfil" class="foto-perfil">  
        <h1><?php echo htmlspecialchars($nome_usuario); ?></h1>  
    </div>  

    <div class="msg-info" id="contador-mensagens" data-total="<?php echo $total_msgs; ?>" data-max="40">
        <?php
            $restantes = max(0, 40 - $total_msgs);
            echo "Você tem <strong>{$total_msgs}</strong> mensagens. Restam <strong>{$restantes}</strong> envios (limite 40).";
        ?>
    </div>

    <?php if (!empty($aviso)): ?>
        <div class="aviso"><?php echo $aviso; ?></div>
    <?php endif; ?>

    <form method="POST" action="">  
        <label for="mensagem">Escreva sua mensagem:</label>  
        <!-- textarea sempre limpo -->
        <textarea name="mensagem" id="mensagem" rows="4" maxlength="140" required></textarea>  
        <input id="enviar-btn" type="submit" value="Enviar Mensagem" <?php echo ($total_msgs >= 40) ? "disabled" : ""; ?>>  
    </form>  

    <h3>Suas mensagens:</h3>  
    <?php if ($mensagens->num_rows > 0): ?>  
        <ul>  
            <?php while ($row = $mensagens->fetch_assoc()): ?>  
            <li class="mensagem">
                <img src="<?php echo htmlspecialchars($row['perfil_img']); ?>" alt="Foto de Perfil" class="foto-perfil">
                <div>
                    <strong><?php echo htmlspecialchars($row['nome']); ?> - <?php echo $row['data_envio']; ?></strong>
                    <p><?php echo htmlspecialchars($row['mensagem']); ?></p>
                    <div class="mensagem-acoes">
                        <a href="curtir.php?mensagem_id=<?php echo $row['id']; ?>">
                            <?php echo ($row['ja_curtiu'] > 0) ? "Descurtir" : "Curtir"; ?>
                        </a>
                        <span>(<?php echo $row['total_curtidas']; ?>)</span> | 
                        <a href="comentarios.php?mensagem_id=<?php echo $row['id']; ?>">Comentários</a> <span>(<?php echo $row['total_comentarios']; ?>)</span> |
                        <a href="editar_msg.php?mensagem_id=<?php echo $row['id']; ?>">Editar</a> |
                        <a href="excluir_msg.php?mensagem_id=<?php echo $row['id']; ?>" 
                           onclick="return confirm('Tem certeza que deseja excluir esta mensagem?');">
                           Excluir
                        </a>
                    </div>
                </div>
            </li> 
            <?php endwhile; ?>  
        </ul>  
    <?php else: ?>  
        <p>Você ainda não enviou mensagens.</p>  
    <?php endif; ?>  

    <div class="nav">  
        <a href="encontrar.php">Encontrar usuários</a> |
        <a href="feed_amigos.php">Ver feed dos amigos</a> |
        <a href="editar.php">Editar Perfil</a> |
        <a href="index.php">Sair</a>  
    </div>

</div>  

<!-- limpa textarea após enviar msg -->
<script>
document.getElementById("enviar-btn").addEventListener("click", function() {
    setTimeout(() => {
        document.getElementById("mensagem").value = "";
    }, 50);
});
</script>

</body>  
</html>