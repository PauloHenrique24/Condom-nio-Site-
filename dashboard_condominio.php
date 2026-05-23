<?php
session_start();
include("config.php");

// 🔒 Proteção
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'condominio') {
    header("Location: login_condominio.php");
    exit;
}

$condominio_id = $_SESSION['condominio_id'];
$condominio_nome = $_SESSION['condominio_nome'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Painel do Condomínio</title>

<style>

    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
    }

    body{
        font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background:#111;
        color:#fff;
        min-height:100vh;
    }

    .topo{
        background:#000;
        padding:25px 30px;
        border-bottom:1px solid #222;
        display:flex;
        justify-content:space-between;
        align-items:center;
        flex-wrap:wrap;
        gap:15px;
    }

    .topo h1{
        font-size:28px;
        font-weight:700;
    }

    .topo p{
        color:#aaa;
        margin-top:5px;
        font-size:14px;
    }

    .btn-sair{
        background:#fff;
        color:#000;
        padding:12px 18px;
        border-radius:10px;
        text-decoration:none;
        font-weight:700;
        transition:.3s;
    }

    .btn-sair:hover{
        background:#dcdcdc;
        transform:translateY(-2px);
    }

    .container{
        max-width:1200px;
        margin:auto;
        padding:30px 20px;
    }

    .titulo{
        margin-bottom:25px;
    }

    .titulo h2{
        font-size:24px;
        margin-bottom:8px;
    }

    .titulo p{
        color:#999;
        font-size:14px;
    }

    .cards{
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));
        gap:20px;
    }

    .card{
        background:#1a1a1a;
        border:1px solid #2a2a2a;
        border-radius:18px;
        padding:22px;
        transition:.3s;
        box-shadow:0 5px 15px rgba(0,0,0,0.25);
    }

    .card:hover{
        transform:translateY(-4px);
        border-color:#555;
    }

    .card h3{
        font-size:20px;
        margin-bottom:18px;
        color:#fff;
    }

    .info{
        margin-bottom:12px;
        line-height:1.6;
    }

    .info span{
        color:#999;
        display:block;
        font-size:13px;
        margin-bottom:3px;
    }

    .status{
        display:inline-block;
        padding:8px 14px;
        border-radius:999px;
        font-size:13px;
        font-weight:700;
        margin-top:10px;
    }

    .status-pendente{
        background:#332701;
        color:#ffcc00;
    }

    .status-aprovado{
        background:#032b12;
        color:#00ff88;
    }

    .status-cancelado{
        background:#2b0303;
        color:#ff5c5c;
    }

    .vazio{
        background:#1a1a1a;
        border:1px dashed #333;
        border-radius:18px;
        padding:40px;
        text-align:center;
        color:#777;
    }

    @media(max-width:768px){

        .topo{
            flex-direction:column;
            align-items:flex-start;
        }

        .topo h1{
            font-size:24px;
        }

    }

</style>
</head>
<body>

<div class="topo">

    <div>
        <h1>Painel do Condomínio</h1>
        <p>Bem-vindo, <?php echo htmlspecialchars($condominio_nome); ?></p>
    </div>

    <a href="logout.php" class="btn-sair">
        Sair
    </a>

</div>

<div class="container">

    <div class="titulo">
        <h2> Contratações</h2>
        <p>Visualize todos os serviços solicitados pelos moradores.</p>
    </div>

    <div class="cards">

<?php

$stmt = $conn->prepare("
SELECT 
    c.*, 
    m.nome as morador_nome, 
    p.nome as prestador_nome
FROM contratacoes c
JOIN moradores m ON m.id = c.morador_id
JOIN prestadores p ON p.id = c.prestador_id
WHERE m.condominio_id=?
ORDER BY c.data_servico DESC
");

$stmt->bind_param("i", $condominio_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $status = strtolower($row['status']);
        $classeStatus = 'status-pendente';

        if ($status == 'aprovado') {
            $classeStatus = 'status-aprovado';
        }

        if ($status == 'cancelado') {
            $classeStatus = 'status-cancelado';
        }

        ?>

        <div class="card">

            <h3> <?php echo htmlspecialchars($row['prestador_nome']); ?></h3>

            <div class="info">
                <span>Morador</span>
                <?php echo htmlspecialchars($row['morador_nome']); ?>
            </div>

            <div class="info">
                <span>Serviço</span>
                <?php echo htmlspecialchars($row['mensagem']); ?>
            </div>

            <?php if ($row['data_servico']) : ?>

                <div class="info">
                    <span>Data</span>
                    <?php echo date('d/m/Y', strtotime($row['data_servico'])); ?>
                </div>

                <div class="info">
                    <span>Horário</span>
                    <?php echo htmlspecialchars($row['hora_servico']); ?>
                </div>

            <?php endif; ?>

            <div class="status <?php echo $classeStatus; ?>">
                <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
            </div>

        </div>

        <?php
    }

} else {

    echo '
    <div class="vazio">
        <h3> Nenhuma contratação encontrada</h3>
        <p>Assim que moradores contratarem serviços, eles aparecerão aqui.</p>
    </div>
    ';
}

$stmt->close();

?>

    </div>

</div>

</body>
</html>