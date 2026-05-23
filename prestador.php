<?php
include("config.php");

function buscarCondominio($conn, $nome, $regiao) {
    $stmt = $conn->prepare("SELECT id FROM condominios WHERE nome=? AND regiao=?");
    $stmt->bind_param("ss", $nome, $regiao);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// 🔒 valida se o ID existe
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Prestador não encontrado");
}

$id = intval($_GET['id']); // força ser número

$stmt = $conn->prepare("SELECT * FROM prestadores WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

// 🔒 valida se encontrou resultado
if ($result->num_rows == 0) {
    die("Prestador não existe");
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil do Prestador</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background:#f5f5f5;
    color:#111;
    min-height:100vh;
}

.header{
    background:#000;
    color:#fff;
    padding:25px;
    text-align:center;
}

.header h1{
    font-size:30px;
}

.container{
    max-width:800px;
    margin:40px auto;
    padding:0 20px;
}

.card{
    background:#fff;
    border-radius:20px;
    padding:35px;
    box-shadow:0 5px 25px rgba(0,0,0,0.08);
    border:1px solid #e5e5e5;
}

.avatar{
    width:120px;
    height:120px;
    background:#000;
    color:#fff;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:50px;
    margin:0 auto 25px;
}

.nome{
    text-align:center;
    font-size:32px;
    font-weight:bold;
    margin-bottom:10px;
}

.tag{
    display:inline-block;
    background:#eee;
    padding:8px 16px;
    border-radius:30px;
    font-size:13px;
    font-weight:bold;
    margin:0 auto 30px;
}

.tag-wrapper{
    text-align:center;
}

.info{
    background:#fafafa;
    border:1px solid #ddd;
    border-radius:14px;
    padding:20px;
    margin-bottom:20px;
}

.info strong{
    display:block;
    color:#666;
    margin-bottom:10px;
    font-size:13px;
    text-transform:uppercase;
}

.info p{
    font-size:18px;
    line-height:1.6;
    color:#111;
}

.preco{
    font-size:36px;
    font-weight:bold;
    text-align:center;
    margin:30px 0;
}

.btns{
    display:flex;
    gap:15px;
    justify-content:center;
    flex-wrap:wrap;
}

.btn{
    text-decoration:none;
    padding:14px 24px;
    border-radius:12px;
    font-weight:bold;
    transition:0.3s;
}

.btn-contratar{
    background:#000;
    color:#fff;
}

.btn-contratar:hover{
    background:#333;
    transform:translateY(-2px);
}

.btn-voltar{
    background:#e5e5e5;
    color:#000;
}

.btn-voltar:hover{
    background:#d5d5d5;
}

.footer{
    margin-top:25px;
    text-align:center;
    color:#777;
    font-size:13px;
}

@media(max-width:768px){

    .card{
        padding:25px;
    }

    .nome{
        font-size:26px;
    }

    .preco{
        font-size:30px;
    }

    .btns{
        flex-direction:column;
    }

    .btn{
        width:100%;
        text-align:center;
    }

}

</style>
</head>
<body>

<div class="header">
    <h1>Perfil do Prestador</h1>
</div>

<div class="container">

    <div class="card">

        <div class="avatar">
            
        </div>

        <div class="nome">
            <?= htmlspecialchars($row['nome']) ?>
        </div>

        <div class="tag-wrapper">
            <div class="tag">
                Prestador Verificado
            </div>
        </div>

        <div class="info">
            <strong>CPF</strong>
            <p><?= htmlspecialchars($row['cpf']) ?></p>
        </div>

        <div class="info">
            <strong> Serviços</strong>
            <p><?= htmlspecialchars($row['descricao']) ?></p>
        </div>

        <div class="preco">
            R$ <?= number_format($row['preco_medio'], 2, ',', '.') ?>
        </div>

        <div class="btns">

            <a 
                href="contratar.php?prestador_id=<?= $row['id'] ?>" 
                class="btn btn-contratar"
            >
                Contratar Agora
            </a>

            <a 
                href="dashboard.php" 
                class="btn btn-voltar"
            >
                ← Voltar
            </a>

        </div>

        <div class="footer">
            Sistema de contratação para condomínios
        </div>

    </div>

</div>

</body>
</html>