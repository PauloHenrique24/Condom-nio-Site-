<?php
session_start();
include("config.php");

if (!isset($_SESSION['prestador_id'])) {
    header("Location: login_prestador.php");
    exit;
}

$prestador_id = $_SESSION['prestador_id'];

if ($_POST) {

    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $preco = floatval($_POST['preco']);

    // 🔧 atualiza dados
    $stmt = $conn->prepare("
        UPDATE prestadores 
        SET nome=?, descricao=?, preco_medio=? 
        WHERE id=?
    ");

    $stmt->bind_param("ssdi", $nome, $descricao, $preco, $prestador_id);
    $stmt->execute();

    // 🔥 remove regiões antigas
    $stmt = $conn->prepare("DELETE FROM prestador_regioes WHERE prestador_id=?");
    $stmt->bind_param("i", $prestador_id);
    $stmt->execute();

    // 🔥 salva novas regiões
    if (isset($_POST['regioes'])) {

        foreach ($_POST['regioes'] as $regiao) {

            $regiao = trim($regiao);

            if (!empty($regiao)) {

                $stmt = $conn->prepare("
                    INSERT INTO prestador_regioes (prestador_id, regiao)
                    VALUES (?, ?)
                ");

                $stmt->bind_param("is", $prestador_id, $regiao);
                $stmt->execute();
            }
        }
    }

    header("Location: dashboard_prestador.php");
    exit;
}

// dados atuais
$stmt = $conn->prepare("SELECT * FROM prestadores WHERE id=?");
$stmt->bind_param("i", $prestador_id);
$stmt->execute();

$prestador = $stmt->get_result()->fetch_assoc();

// regiões atuais
$stmt = $conn->prepare("
    SELECT regiao 
    FROM prestador_regioes
    WHERE prestador_id=?
");

$stmt->bind_param("i", $prestador_id);
$stmt->execute();

$regioes = [];

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $regioes[] = $row['regiao'];
}

// garante pelo menos 3 campos
while (count($regioes) < 3) {
    $regioes[] = '';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Editar Perfil</title>

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
    max-width:700px;
    margin:auto;
}

.card{
    background:#1a1a1a;
    border:1px solid #333;
    border-radius:24px;
    padding:35px;
    box-shadow:0 10px 30px rgba(0,0,0,0.4);
}

.header{
    margin-bottom:30px;
}

.header h1{
    font-size:34px;
    margin-bottom:10px;
}

.header p{
    color:#999;
    font-size:14px;
}

.form-group{
    margin-bottom:22px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    color:#ddd;
    font-size:14px;
    font-weight:600;
}

.form-group input,
.form-group textarea{
    width:100%;
    padding:14px;
    border-radius:14px;
    border:1px solid #333;
    background:#222;
    color:#fff;
    font-size:14px;
    outline:none;
    transition:0.3s;
}

.form-group input:focus,
.form-group textarea:focus{
    border-color:#fff;
}

.form-group textarea{
    resize:vertical;
    min-height:120px;
}

.regioes{
    margin-top:10px;
}

.regiao-item{
    margin-bottom:12px;
}

.regiao-item input{
    width:100%;
}

.actions{
    display:flex;
    gap:15px;
    margin-top:30px;
    flex-wrap:wrap;
}

.btn{
    flex:1;
    min-width:180px;
    text-align:center;
    padding:15px;
    border-radius:14px;
    border:none;
    font-size:15px;
    font-weight:bold;
    cursor:pointer;
    text-decoration:none;
    transition:0.3s;
}

.btn-salvar{
    background:#fff;
    color:#000;
}

.btn-salvar:hover{
    background:#ddd;
    transform:translateY(-2px);
}

.btn-voltar{
    background:#222;
    color:#fff;
    border:1px solid #444;
}

.btn-voltar:hover{
    background:#2c2c2c;
}

.divisor{
    width:100%;
    height:1px;
    background:#333;
    margin:25px 0;
}

.info-box{
    background:#222;
    border:1px solid #333;
    border-radius:16px;
    padding:18px;
    margin-bottom:25px;
}

.info-box p{
    color:#bbb;
    font-size:13px;
    line-height:1.6;
}

small{
    color:#777;
}

::-webkit-scrollbar{
    width:8px;
}

::-webkit-scrollbar-thumb{
    background:#444;
    border-radius:10px;
}

@media(max-width:700px){

    .card{
        padding:25px;
    }

    .header h1{
        font-size:28px;
    }

    .actions{
        flex-direction:column;
    }

    .btn{
        width:100%;
    }
}

</style>

</head>

<body>

<div class="container">

    <div class="card">

        <div class="header">

            <h1> Editar Perfil</h1>

            <p>
                Atualize seus serviços, preços e regiões atendidas.
            </p>

        </div>

        <div class="info-box">

            <p>
                Um perfil bem preenchido gera mais confiança e aumenta as chances de contratação.
            </p>

        </div>

        <form method="POST">

            <div class="form-group">

                <label>Nome</label>

                <input 
                    type="text" 
                    name="nome"
                    value="<?= htmlspecialchars($prestador['nome']) ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Serviços</label>

                <textarea 
                    name="descricao"
                    required
                ><?= htmlspecialchars($prestador['descricao']) ?></textarea>

            </div>

            <div class="form-group">

                <label>Preço Médio</label>

                <input 
                    type="number"
                    step="0.01"
                    min="0"
                    name="preco"
                    value="<?= htmlspecialchars($prestador['preco_medio']) ?>"
                    required
                >

            </div>

            <div class="divisor"></div>

            <div class="form-group">

                <label>
                    Regiões de atuação
                    <br>
                    <small>Adicione cidades ou regiões que você atende.</small>
                </label>

                <div class="regioes" id="regioes-wrapper">

                    <?php foreach ($regioes as $regiao): ?>

                        <div class="regiao-item">

                            <input 
                                type="text"
                                name="regioes[]"
                                placeholder="Ex: Sorocaba/SP"
                                value="<?= htmlspecialchars($regiao) ?>"
                            >

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

            <div class="actions">

                <button class="btn btn-salvar">
                     Salvar Alterações
                </button>

                <a href="dashboard_prestador.php" class="btn btn-voltar">
                    ← Voltar
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>