<?php
session_start();
include("config.php");

// 🔒 proteção
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'prestador') {
    header("Location: login_prestador.php");
    exit;
}

$prestador_id = $_SESSION['prestador_id'];
$mensagem = "";

// 📅 agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agendar'])) {

    $contrato_id = intval($_POST['contrato_id']);
    $data = $_POST['data'];
    $hora = $_POST['hora'];

    $stmt = $conn->prepare("
        SELECT id FROM contratacoes 
        WHERE id=? AND prestador_id=?
    ");
    $stmt->bind_param("ii", $contrato_id, $prestador_id);
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0) {

        $stmt = $conn->prepare("
            UPDATE contratacoes 
            SET status='aceito', data_servico=?, hora_servico=? 
            WHERE id=?
        ");
        $stmt->bind_param("ssi", $data, $hora, $contrato_id);

        if ($stmt->execute()) {
            $mensagem = "Serviço agendado com sucesso!";
        }
    }
}

// 🔍 dados do prestador
$stmt = $conn->prepare("SELECT * FROM prestadores WHERE id=?");
$stmt->bind_param("i", $prestador_id);
$stmt->execute();
$prestador = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel do Prestador</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background:#f4f4f4;
    color:#111;
    min-height:100vh;
}

.header{
    background:#000;
    color:#fff;
    gap:12px;
    padding:25px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    flex-wrap:wrap;
}

.header h1{
    font-size:28px;
}

.header-buttons{
    display:flex;
    gap:12px;
    align-items:center;
}

.logout{
    background:#fff;
    color:#000;
    padding:10px 18px;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
    transition:0.3s;
}

.btn-editar{
    background:#fff;
    color:#000;
    padding:10px 18px;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
    transition:0.3s;
}

.btn-editar:hover{
    background:#ddd;
}
.logout:hover{
    background:#ddd;
}

.container{
    max-width:1200px;
    margin:30px auto;
    padding:0 20px;
}

.card{
    background:#fff;
    border-radius:16px;
    padding:25px;
    margin-bottom:25px;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

.card h2{
    margin-bottom:20px;
    font-size:24px;
    color:#000;
}

.info-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:20px;
}

.info-box{
    background:#fafafa;
    border:1px solid #ddd;
    border-radius:12px;
    padding:20px;
}

.info-box span{
    display:block;
    color:#666;
    margin-bottom:8px;
    font-size:13px;
}

.info-box strong{
    font-size:18px;
}

.pedido{
    border:1px solid #ddd;
    border-radius:14px;
    padding:20px;
    margin-bottom:20px;
    background:#fff;
    transition:0.3s;
}

.pedido:hover{
    transform:translateY(-2px);
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

.status{
    display:inline-block;
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
    margin-top:10px;
}

.status.pendente{
    background:#000;
    color:#fff;
}

.status.aceito{
    background:#eaeaea;
    color:#000;
}

.form-agendar{
    margin-top:20px;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.form-agendar input{
    padding:12px;
    border:1px solid #ccc;
    border-radius:8px;
}

.form-agendar button{
    background:#000;
    color:#fff;
    border:none;
    padding:12px 20px;
    border-radius:8px;
    cursor:pointer;
    font-weight:bold;
    transition:0.3s;
}

.form-agendar button:hover{
    background:#333;
}

.chat-btn{
    display:inline-block;
    margin-top:15px;
    text-decoration:none;
    background:#000;
    color:#fff;
    padding:10px 16px;
    border-radius:8px;
    transition:0.3s;
}

.chat-btn:hover{
    background:#333;
}

.alerta{
    background:#000;
    color:#fff;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
}

.vazio{
    text-align:center;
    padding:40px;
    color:#666;
}

@media(max-width:768px){

    .header{
        flex-direction:column;
        text-align:center;
    }

    .header-buttons{
        width:100%;
        justify-content:center;
    }

    .form-agendar{
        flex-direction:column;
    }

    .form-agendar button{
        width:100%;
    }

}

</style>
</head>
<body>

<div class="header">
    <h1>Painel do Prestador</h1>

    <div class="header-buttons">
        <a href="editar_prestador.php" class="btn-editar">
         Editar Meu Perfil
        </a>
        <a href="logout.php" class="logout">
         Sair
        </a>
    </div>
</div>

<div class="container">

    <?php if($mensagem): ?>
        <div class="alerta">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <div class="card">

        <h2>Seus Dados</h2>

        <div class="info-grid">

            <div class="info-box">
                <span>Prestador</span>
                <strong><?= htmlspecialchars($prestador['nome']) ?></strong>
            </div>

            <div class="info-box">
                <span>Serviços</span>
                <strong><?= htmlspecialchars($prestador['descricao']) ?></strong>
            </div>

            <div class="info-box">
                <span>Preço Médio</span>
                <strong>R$ <?= number_format($prestador['preco_medio'], 2, ',', '.') ?></strong>
            </div>

        </div>

    </div>

    <div class="card">

        <h2>Pedidos Recebidos</h2>

        <?php

        $stmt = $conn->prepare("
        SELECT 
            c.*, 
            m.nome as morador_nome,
            m.bloco,
            m.apartamento,
            co.nome as condominio_nome
        FROM contratacoes c
        JOIN moradores m ON m.id = c.morador_id
        JOIN condominios co ON co.id = m.condominio_id
        WHERE c.prestador_id=?
        ORDER BY c.id DESC
        ");

        $stmt->bind_param("i", $prestador_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0):

            while ($row = $result->fetch_assoc()):
        ?>

        <div class="pedido">

            <p><strong> Morador:</strong> <?= htmlspecialchars($row['morador_nome']) ?></p>

            <p>
                <strong> Local:</strong>
                Bloco <?= htmlspecialchars($row['bloco']) ?>
                - AP <?= htmlspecialchars($row['apartamento']) ?>
            </p>

            <p><strong> Condomínio:</strong> <?= htmlspecialchars($row['condominio_nome']) ?></p>

            <p><strong> Serviço:</strong> <?= htmlspecialchars($row['mensagem']) ?></p>

            <div class="status <?= $row['status'] ?>">
                <?= strtoupper($row['status']) ?>
            </div>

            <?php if($row['status'] == 'pendente'): ?>

                <form method="POST" class="form-agendar">

                    <input type="hidden" name="contrato_id" value="<?= $row['id'] ?>">

                    <input type="date" name="data" required>

                    <input type="time" name="hora" required>

                    <button type="submit" name="agendar">
                        ✔ Aceitar e Agendar
                    </button>

                </form>

            <?php endif; ?>

            <?php if($row['status'] == 'aceito'): ?>

                <p style="margin-top:15px;">
                    <strong> Agendado para:</strong>
                    <?= $row['data_servico'] ?> às <?= $row['hora_servico'] ?>
                </p>

            <?php endif; ?>

            <a href="chat.php?contrato_id=<?= $row['id'] ?>" class="chat-btn">
                 Abrir Chat
            </a>

        </div>

        <?php
            endwhile;

        else:
        ?>

            <div class="vazio">
                Nenhum pedido recebido até o momento.
            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>