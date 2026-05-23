<?php
session_start();
include("config.php");

// 🔒 só morador
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'morador') {
    die("Acesso restrito");
}

$morador_id = $_SESSION['morador_id'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Meus Pedidos</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#111;
    color:#fff;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height:100vh;
    padding:30px 15px;
}

.container{
    max-width:1000px;
    margin:auto;
}

.topo{
    background:#1a1a1a;
    border:1px solid #333;
    border-radius:20px;
    padding:30px;
    margin-bottom:30px;
    box-shadow:0 10px 30px rgba(0,0,0,0.4);
}

.topo h1{
    font-size:32px;
    margin-bottom:10px;
}

.topo p{
    color:#aaa;
    font-size:14px;
}

.actions{
    margin-top:20px;
}

.actions a{
    display:inline-block;
    padding:12px 20px;
    background:#fff;
    color:#000;
    border-radius:12px;
    text-decoration:none;
    font-weight:bold;
    transition:0.3s;
}

.actions a:hover{
    background:#ddd;
    transform:translateY(-2px);
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(300px,1fr));
    gap:20px;
}

.card{
    background:#1a1a1a;
    border:1px solid #333;
    border-radius:18px;
    padding:25px;
    transition:0.3s;
    box-shadow:0 8px 25px rgba(0,0,0,0.3);
}

.card:hover{
    transform:translateY(-4px);
    border-color:#555;
}

.card h2{
    font-size:22px;
    margin-bottom:20px;
}

.info{
    margin-bottom:15px;
}

.info span{
    display:block;
    color:#888;
    font-size:13px;
    margin-bottom:5px;
}

.info strong{
    font-size:15px;
    color:#fff;
    line-height:1.5;
}

.status{
    display:inline-block;
    padding:8px 14px;
    border-radius:999px;
    font-size:12px;
    font-weight:bold;
    margin-top:5px;
}

.status.pendente{
    background:#333;
    color:#fff;
}

.status.aceito{
    background:#fff;
    color:#000;
}

.btn-chat{
    display:block;
    margin-top:20px;
    width:100%;
    text-align:center;
    padding:14px;
    border-radius:12px;
    background:#fff;
    color:#000;
    text-decoration:none;
    font-weight:bold;
    transition:0.3s;
}

.btn-chat:hover{
    background:#ddd;
    transform:translateY(-2px);
}

.empty{
    background:#1a1a1a;
    border:1px solid #333;
    border-radius:20px;
    padding:50px 20px;
    text-align:center;
}

.empty h3{
    font-size:24px;
    margin-bottom:10px;
}

.empty p{
    color:#999;
}

::-webkit-scrollbar{
    width:8px;
}

::-webkit-scrollbar-thumb{
    background:#444;
    border-radius:10px;
}

@media(max-width:700px){

    .topo h1{
        font-size:26px;
    }

    .card{
        padding:20px;
    }
}

</style>
</head>

<body>

<div class="container">

    <div class="topo">

        <h1>Meus Pedidos</h1>

        <p>
            Aqui você acompanha todos os serviços solicitados.
        </p>

        <div class="actions">
            <a href="dashboard.php">
                ← Voltar ao painel
            </a>
        </div>

    </div>

    <div class="grid">

        <?php

        $stmt = $conn->prepare("
        SELECT c.*, p.nome as prestador_nome 
        FROM contratacoes c
        JOIN prestadores p ON p.id = c.prestador_id
        WHERE c.morador_id=?
        ORDER BY c.id DESC
        ");

        $stmt->bind_param("i", $morador_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {

            echo "
            <div class='empty'>
                <h3> Nenhum pedido encontrado</h3>
                <p>Você ainda não solicitou nenhum serviço.</p>
            </div>
            ";

        } else {

            while ($row = $result->fetch_assoc()) {

                $status = strtolower($row['status']);

                ?>

                <div class="card">

                    <h2>
                         <?= htmlspecialchars($row['prestador_nome']) ?>
                    </h2>

                    <div class="info">
                        <span>Descrição do serviço</span>

                        <strong>
                            <?= nl2br(htmlspecialchars($row['mensagem'])) ?>
                        </strong>
                    </div>

                    <div class="info">
                        <span>Status</span>

                        <div class="status <?= $status ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </div>
                    </div>

                    <?php if (!empty($row['data_servico'])): ?>

                    <div class="info">
                        <span>Agendamento</span>

                        <strong>
                            <?= htmlspecialchars($row['data_servico']) ?>
                            às
                            <?= htmlspecialchars($row['hora_servico']) ?>
                        </strong>
                    </div>

                    <?php endif; ?>

                    <a class="btn-chat" href="chat.php?contrato_id=<?= $row['id'] ?>">
                        Abrir Chat
                    </a>

                </div>

                <?php
            }
        }
        ?>

    </div>

</div>

</body>
</html>