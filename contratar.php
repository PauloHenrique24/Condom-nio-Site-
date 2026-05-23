<?php
session_start();
include("config.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'morador') {
    die("Acesso restrito");
}

if (!isset($_GET['prestador_id'])) {
    die("Prestador não encontrado");
}

$prestador_id = intval($_GET['prestador_id']);
$morador_id = $_SESSION['morador_id'];

$mensagem_sucesso = '';
$erro = '';

// 🔍 pega dados do prestador
$stmt = $conn->prepare("SELECT * FROM prestadores WHERE id=?");
$stmt->bind_param("i", $prestador_id);
$stmt->execute();

$prestador = $stmt->get_result()->fetch_assoc();

if (!$prestador) {
    die("Prestador não encontrado");
}

// 🚀 envia solicitação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mensagem = trim($_POST['mensagem']);

    if (empty($mensagem)) {

        $erro = "Descreva o serviço que deseja contratar.";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO contratacoes 
            (morador_id, prestador_id, mensagem) 
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param("iis", $morador_id, $prestador_id, $mensagem);

        if ($stmt->execute()) {
            $mensagem_sucesso = "Solicitação enviada com sucesso!";
        } else {
            $erro = "Erro ao enviar solicitação.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contratar Serviço</title>

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
    max-width:700px;
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

.prestador{
    text-align:center;
    margin-bottom:30px;
}

.avatar{
    width:110px;
    height:110px;
    background:#000;
    color:#fff;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:50px;
    margin:0 auto 20px;
}

.nome{
    font-size:28px;
    font-weight:bold;
    margin-bottom:10px;
}

.descricao{
    color:#555;
    line-height:1.6;
    margin-bottom:15px;
}

.preco{
    font-size:32px;
    font-weight:bold;
    margin-top:10px;
}

.form-group{
    margin-bottom:25px;
}

.form-group label{
    display:block;
    margin-bottom:10px;
    font-weight:bold;
    color:#333;
}

textarea{
    width:100%;
    min-height:180px;
    resize:vertical;
    border:1px solid #ccc;
    border-radius:14px;
    padding:16px;
    font-size:15px;
    font-family:inherit;
    transition:0.3s;
}

textarea:focus{
    outline:none;
    border-color:#000;
    box-shadow:0 0 0 3px rgba(0,0,0,0.08);
}

.btns{
    display:flex;
    gap:15px;
    flex-wrap:wrap;
}

button{
    background:#000;
    color:#fff;
    border:none;
    padding:15px 25px;
    border-radius:12px;
    font-size:15px;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    background:#333;
    transform:translateY(-2px);
}

.btn-voltar{
    text-decoration:none;
    background:#e5e5e5;
    color:#000;
    padding:15px 25px;
    border-radius:12px;
    font-weight:bold;
    transition:0.3s;
}

.btn-voltar:hover{
    background:#d5d5d5;
}

.alerta{
    padding:16px;
    border-radius:12px;
    margin-bottom:25px;
    font-weight:bold;
}

.sucesso{
    background:#111;
    color:#fff;
}

.erro{
    background:#eee;
    color:#000;
    border:1px solid #ccc;
}

.footer{
    margin-top:30px;
    text-align:center;
    color:#777;
    font-size:13px;
}

@media(max-width:768px){

    .card{
        padding:25px;
    }

    .nome{
        font-size:24px;
    }

    .preco{
        font-size:28px;
    }

    .btns{
        flex-direction:column;
    }

    button,
    .btn-voltar{
        width:100%;
        text-align:center;
    }

}

</style>
</head>
<body>

<div class="header">
    <h1>Contratar Serviço</h1>
</div>

<div class="container">

    <div class="card">

        <?php if($mensagem_sucesso): ?>

            <div class="alerta sucesso">
                 <?= $mensagem_sucesso ?>
            </div>

        <?php endif; ?>

        <?php if($erro): ?>

            <div class="alerta erro">
                 <?= $erro ?>
            </div>

        <?php endif; ?>

        <div class="prestador">

            <div class="avatar">
                
            </div>

            <div class="nome">
                <?= htmlspecialchars($prestador['nome']) ?>
            </div>

            <div class="descricao">
                <?= nl2br(htmlspecialchars($prestador['descricao'])) ?>
            </div>

            <div class="preco">
                R$ <?= number_format($prestador['preco_medio'], 2, ',', '.') ?>
            </div>

        </div>

        <form method="POST">

            <div class="form-group">

                <label>
                     Descreva o serviço que você precisa
                </label>

                <textarea 
                    name="mensagem"
                    placeholder="Ex: Preciso de manutenção elétrica no apartamento, tomada sem funcionar na cozinha..."
                    required
                ><?= isset($_POST['mensagem']) ? htmlspecialchars($_POST['mensagem']) : '' ?></textarea>

            </div>

            <div class="btns">

                <button type="submit">
                     Enviar Solicitação
                </button>

                <a href="dashboard.php" class="btn-voltar">
                    ← Voltar
                </a>

            </div>

        </form>

        <div class="footer">
            Depois do envio o prestador poderá aceitar e agendar o serviço.
        </div>

    </div>

</div>

</body>
</html>