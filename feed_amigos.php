<?php
session_start();
include("conexao.php");

$meu_id = $_SESSION['usuario_id'];

// 10 msgs por vez
$limite_base = 10;
$pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($pagina < 1) $pagina = 1;
//acumular posts
$limite = $pagina * $limite_base;
$offset = 0 ;

// total de mensagens 
$sql_total = "
SELECT COUNT(*) AS total
FROM mensagens m
JOIN seguindo s ON m.usuario_id = s.amigo_id
WHERE s.usuario_id = ?
";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("i", $meu_id);
$stmt_total->execute();
$total_result = $stmt_total->get_result()->fetch_assoc();
$total_mensagens = $total_result['total'];

// pegar mensagens dos amigos com o ler mais
$sql = "
SELECT 
    m.id, 
    m.mensagem, 
    m.data_envio, 
    u.nome, 
    u.perfil_img,
    (SELECT COUNT(*) FROM comentarios cm WHERE cm.mensagem_id = m.id) AS total_comentarios,
    (SELECT COUNT(*) FROM curtidas c WHERE c.mensagem_id = m.id) AS total_curtidas,
    (SELECT COUNT(*) FROM curtidas c WHERE c.mensagem_id = m.id AND c.usuario_id = ?) AS ja_curtiu
FROM mensagens m
JOIN seguindo s ON m.usuario_id = s.amigo_id
JOIN usuario u ON m.usuario_id = u.id
WHERE s.usuario_id = ?
ORDER BY m.data_envio DESC
LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $meu_id, $meu_id, $limite, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Feed dos Amigos</title>
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
        padding: 20px;
    }

    .container {
        max-width: 600px;
        margin: 0 auto;
        padding: 0 15px;
    }

    h2 {
        font-size: 22px;
        margin-bottom: 20px;
        text-align: center;
    }

    /* Mensagens */
    ul {
        list-style: none;
    }

    .mensagem {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 15px;
        border-bottom: 1px solid var(--border-color);
        transition: background-color 0.2s;
    }

    .mensagem:hover {
        background-color: var(--hover-bg);
    }

    .foto-perfil {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--main-color);
        flex-shrink: 0;
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

    .mensagem-acoes {
        display: flex;
        align-items: center;
        gap: 15px;
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

    .mensagem-acoes span {
        color: #6e767d;
        font-size: 13px;
    }

    /* Estado vazio */
    .estado-vazio {
        text-align: center;
        padding: 40px 20px;
        color: #6e767d;
        font-size: 16px;
    }

    /* Botões */
    .voltar-link, .ler-mais {
        display: inline-block;
        margin-top: 20px;
        color: var(--main-color);
        text-decoration: none;
        font-weight: 600;
        padding: 8px 16px;
        border: 1px solid var(--main-color);
        border-radius: 9999px;
        transition: all 0.2s ease;
    }

    .voltar-link:hover, .ler-mais:hover {
        background-color: var(--main-color);
        color: var(--text-color);
    }

    /* Voltar no canto superior direito */
    .voltar-link {
        position: fixed;
        top: 20px;
        right: 20px;
        margin: 0;
    }

    /* Centralizar o Ler Mais */
    .ler-mais {
        display: block;
        margin: 30px auto;
        text-align: center;
        width: fit-content;
    }

    /* Responsividade */
    @media (max-width: 500px) {
        .mensagem {
            padding: 12px 10px;
        }
        
        .foto-perfil {
            width: 40px;
            height: 40px;
        }
    }
    </style>
</head>
<body>

 <!-- feedzinho dos amigos --> 
<div class="container">

<h2>Feed dos amigos</h2>

<?php if ($result->num_rows > 0): ?>
    <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
        <li class="mensagem">
            <img src="<?php echo htmlspecialchars($row['perfil_img']); ?>" alt="Foto de Perfil" class="foto-perfil">
            <div>
                <strong><?php echo htmlspecialchars($row['nome']); ?> - <?php echo $row['data_envio']; ?></strong>
                <p><?php echo htmlspecialchars($row['mensagem']); ?></p>
                <div class="mensagem-acoes">
                    <a href="curtir.php?mensagem_id=<?php echo $row['id']; ?>">
                        <?php echo ($row['ja_curtiu'] > 0) ? "Descurtir" : "Curtir"; ?>
                    </a>
                    <span>(<?php echo $row['total_curtidas']; ?> curtidas)</span> | <a href="comentarios.php?mensagem_id=<?php echo $row['id']; ?>">Comentários</a> <span>(<?php echo $row['total_comentarios']; ?>)</span> 
                </div>
            </div>
        </li>
        <?php endwhile; ?>
    </ul>

    <?php if ($limite < $total_mensagens): ?>
        <a href="?page=<?php echo $pagina + 1; ?>" class="ler-mais">Ler mais</a>
    <?php endif; ?>

<?php else: ?>
    <p class="estado-vazio">Seus amigos ainda não postaram mensagens.</p>
<?php endif; ?>

<a href="boas_vindas.php" class="voltar-link">Voltar ao perfil</a>

</div>
</body>
</html>