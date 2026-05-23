<?php
session_start();
include("config.php");

if (!isset($_SESSION['tipo'])) {
    die("Login necessário");
}

if (!isset($_GET['contrato_id'])) {
    die("Contrato inválido");
}

$contrato_id = intval($_GET['contrato_id']);

// busca contrato + info completa
$stmt = $conn->prepare("
SELECT c.*, m.nome as morador_nome, p.nome as prestador_nome, co.nome as condominio_nome
FROM contratacoes c
JOIN moradores m ON m.id = c.morador_id
JOIN prestadores p ON p.id = c.prestador_id
JOIN condominios co ON co.id = m.condominio_id
WHERE c.id=?
");
$stmt->bind_param("i", $contrato_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Contrato não encontrado");
}

$contrato = $result->fetch_assoc();

// 🔒 valida acesso
if ($_SESSION['tipo'] == 'morador' && $contrato['morador_id'] != $_SESSION['morador_id']) {
    die("Acesso negado");
}

if ($_SESSION['tipo'] == 'prestador' && $contrato['prestador_id'] != $_SESSION['prestador_id']) {
    die("Acesso negado");
}

// 📩 envio da mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $msg = trim($_POST['mensagem']);

    if (!empty($msg)) {

        $stmt = $conn->prepare("
            INSERT INTO mensagens (contrato_id, remetente_tipo, mensagem)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param("iss", $contrato_id, $_SESSION['tipo'], $msg);
        $stmt->execute();
    }

    header("Location: chat.php?contrato_id=".$contrato_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Chat do Serviço</title>

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
    padding:30px 15px;
}

.container{
    max-width:900px;
    margin:auto;
}

.topo{
    background:#1a1a1a;
    border:1px solid #333;
    border-radius:20px;
    padding:30px;
    margin-bottom:25px;
    box-shadow:0 10px 30px rgba(0,0,0,0.4);
}

.topo h1{
    font-size:32px;
    margin-bottom:10px;
}

.topo p{
    color:#bbb;
    font-size:14px;
}

.info-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(220px,1fr));
    gap:15px;
    margin-top:25px;
}

.info-card{
    background:#222;
    border:1px solid #333;
    border-radius:14px;
    padding:15px;
}

.info-card span{
    display:block;
    color:#999;
    font-size:13px;
    margin-bottom:5px;
}

.info-card strong{
    color:#fff;
    font-size:15px;
}

.chat-box{
    background:#1a1a1a;
    border:1px solid #333;
    border-radius:20px;
    padding:25px;
    margin-bottom:25px;
    max-height:500px;
    overflow-y:auto;
}

.msg{
    margin-bottom:18px;
    display:flex;
}

.msg.morador{
    justify-content:flex-start;
}

.msg.prestador{
    justify-content:flex-end;
}

.bolha{
    max-width:75%;
    padding:14px 16px;
    border-radius:16px;
    font-size:14px;
    line-height:1.5;
    word-break:break-word;
}

.morador .bolha{
    background:#2c2c2c;
    color:#fff;
}

.prestador .bolha{
    background:#333;
    color:#fff;
}

.remetente{
    font-size:12px;
    font-weight:bold;
    margin-bottom:6px;
    opacity:0.7;
}

.form-chat{
    background:#1a1a1a;
    border:1px solid #333;
    border-radius:20px;
    padding:20px;
}

.form-chat form{
    display:flex;
    gap:12px;
}

.form-chat input{
    flex:1;
    padding:14px;
    border-radius:12px;
    border:1px solid #444;
    background:#222;
    color:#fff;
    font-size:14px;
    outline:none;
    transition:0.3s;
}

.form-chat input:focus{
    border-color:#fff;
}

.form-chat button{
    background:#fff;
    color:#000;
    border:none;
    padding:14px 22px;
    border-radius:12px;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
}

.form-chat button:hover{
    background:#ddd;
    transform:translateY(-2px);
}

.actions{
    margin-top:20px;
    text-align:center;
}

.actions a{
    color:#ccc;
    text-decoration:none;
    font-size:14px;
    transition:0.3s;
}

.actions a:hover{
    color:#fff;
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

    .form-chat form{
        flex-direction:column;
    }

    .form-chat button{
        width:100%;
    }

    .bolha{
        max-width:100%;
    }
}

</style>
</head>

<body>

<div class="container">

    <div class="topo">

        <h1>💬 Chat do Serviço</h1>
        <p>Converse diretamente sobre o atendimento.</p>

        <div class="info-grid">

            <div class="info-card">
                <span>Morador</span>
                <strong><?= htmlspecialchars($contrato['morador_nome']) ?></strong>
            </div>

            <div class="info-card">
                <span>Prestador</span>
                <strong><?= htmlspecialchars($contrato['prestador_nome']) ?></strong>
            </div>

            <div class="info-card">
                <span>Condomínio</span>
                <strong><?= htmlspecialchars($contrato['condominio_nome']) ?></strong>
            </div>

            <div class="info-card">
                <span>Status</span>
                <strong><?= htmlspecialchars($contrato['status']) ?></strong>
            </div>

        </div>

    </div>

    <div class="chat-box">

        <?php
        // Buscar mensagens com os nomes dos remetentes
        $stmt = $conn->prepare("
            SELECT m.*, 
                   CASE 
                       WHEN m.remetente_tipo = 'morador' THEN mor.nome
                       WHEN m.remetente_tipo = 'prestador' THEN pres.nome
                       ELSE m.remetente_tipo
                   END as nome_remetente
            FROM mensagens m
            LEFT JOIN moradores mor ON m.remetente_tipo = 'morador' AND mor.id = ?
            LEFT JOIN prestadores pres ON m.remetente_tipo = 'prestador' AND pres.id = ?
            WHERE m.contrato_id = ?
            ORDER BY m.id ASC
        ");

        $morador_id = $contrato['morador_id'];
        $prestador_id = $contrato['prestador_id'];
        $stmt->bind_param("iii", $morador_id, $prestador_id, $contrato_id);
        $stmt->execute();

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {

            $tipo = htmlspecialchars($row['remetente_tipo']);
            $msg = htmlspecialchars($row['mensagem']);
            $nome_remetente = htmlspecialchars($row['nome_remetente']);

            ?>

            <div class="msg <?= $tipo == 'morador' ? 'morador' : 'prestador' ?>">
                <div class="bolha">

                    <div class="remetente">
                        <?= $nome_remetente ?>
                    </div>

                    <?= nl2br($msg) ?>

                </div>

            </div>

            <?php
        }
        ?>

    </div>

    <div class="form-chat">

        <form method="POST">

            <input 
                type="text" 
                name="mensagem" 
                placeholder="Digite sua mensagem..."
                required
                autocomplete="off"
            >

            <button type="submit">
                Enviar
            </button>

        </form>

        <div class="actions">
            <a href="javascript:history.back()">
                ← Voltar
            </a>
        </div>

    </div>

</div>

<script>
// Scroll automático para o final do chat
document.addEventListener('DOMContentLoaded', function() {
    const chatBox = document.querySelector('.chat-box');
    chatBox.scrollTop = chatBox.scrollHeight;
});

// Auto reload a cada 30 segundos (sem perder o foco do input)
let mensagemTemp = '';
const inputMsg = document.querySelector('input[name="mensagem"]');

setInterval(() => {
    // Salvar o que está sendo digitado antes do reload
    if (inputMsg && document.activeElement !== inputMsg) {
        mensagemTemp = inputMsg.value;
        location.reload();
    }
}, 30000);

// Restaurar a mensagem após o reload
document.addEventListener('DOMContentLoaded', () => {
    if (inputMsg && mensagemTemp) {
        inputMsg.value = mensagemTemp;
        mensagemTemp = '';
    }
    
    // Manter o foco no input
    if (inputMsg) {
        inputMsg.focus();
    }
});
</script>

</body>
</html>