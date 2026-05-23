<?php
session_start();
include("config.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'morador') {
    header("Location: login_morador.php");
    exit;
}

if (!isset($_SESSION['condominio_id'])) {
    header("Location: login_morador.php");
    exit;
}

$condominio_id = $_SESSION['condominio_id'];

// pega região do condomínio
$stmt = $conn->prepare("SELECT regiao FROM condominios WHERE id=?");
$stmt->bind_param("i", $condominio_id);
$stmt->execute();
$cond = $stmt->get_result()->fetch_assoc();

$regiao = $cond['regiao'];

$busca = isset($_GET['busca']) ? "%" . $_GET['busca'] . "%" : "%";

// filtra por região
$stmt = $conn->prepare("
SELECT DISTINCT p.*
FROM prestadores p
JOIN prestador_regioes pr ON pr.prestador_id = p.id
WHERE pr.regiao = ? 
AND (p.nome LIKE ? OR p.descricao LIKE ?)
");

$stmt->bind_param("sss", $regiao, $busca, $busca);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Prestadores Disponíveis</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background:#f0f2f5;
    color:#111;
    min-height:100vh;
}

.header{
    background:#000;
    color:#fff;
    padding:20px 30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    position:sticky;
    top:0;
    z-index:100;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

.header h1{
    font-size:24px;
    font-weight:600;
}

.nav{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

.nav a{
    text-decoration:none;
    background:#fff;
    color:#000;
    padding:8px 16px;
    border-radius:6px;
    font-weight:500;
    font-size:14px;
    transition:0.3s;
}

.nav a:hover{
    background:#e0e0e0;
    transform:translateY(-1px);
}

.container{
    max-width:1300px;
    margin:25px auto;
    padding:0 20px;
}

.search-box{
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 1px 3px rgba(0,0,0,0.08);
    margin-bottom:25px;
}

.search-form{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

.search-form input{
    flex:1;
    min-width:250px;
    padding:12px 16px;
    border:1px solid #ddd;
    border-radius:8px;
    font-size:14px;
    transition:0.3s;
}

.search-form input:focus{
    outline:none;
    border-color:#000;
    box-shadow:0 0 0 2px rgba(0,0,0,0.1);
}

.search-form button{
    background:#000;
    color:#fff;
    border:none;
    padding:12px 28px;
    border-radius:8px;
    cursor:pointer;
    font-weight:500;
    font-size:14px;
    transition:0.3s;
}

.search-form button:hover{
    background:#333;
    transform:translateY(-1px);
}

/* Grid estilo marketplace - cards menores */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));
    gap:20px;
}

.card{
    background:#fff;
    border-radius:12px;
    padding:18px;
    box-shadow:0 1px 3px rgba(0,0,0,0.08);
    transition:all 0.2s ease;
    border:1px solid #e5e5e5;
    position:relative;
}

.card:hover{
    transform:translateY(-3px);
    box-shadow:0 4px 12px rgba(0,0,0,0.12);
    border-color:#ccc;
}

.card h2{
    font-size:18px;
    margin-bottom:12px;
    color:#000;
    font-weight:600;
    line-height:1.3;
    overflow:hidden;
    display:-webkit-box;
    -webkit-line-clamp:1;
    -webkit-box-orient:vertical;
}

.info{
    margin-bottom:10px;
    color:#555;
    line-height:1.4;
    font-size:13px;
}

.info strong{
    color:#000;
    font-size:12px;
    display:block;
    margin-bottom:5px;
}

.info br + span{
    font-size:13px;
}

.regiao{
    margin:8px 0;
    display:inline-block;
    background:#f0f0f0;
    color:#000;
    padding:4px 10px;
    border-radius:15px;
    font-size:11px;
    font-weight:500;
}

.preco{
    font-size:22px;
    font-weight:bold;
    margin:12px 0;
    color:#000;
}

.preco::before{
    content:" ";
    font-size:18px;
}

.btn{
    display:inline-block;
    text-decoration:none;
    background:#000;
    color:#fff;
    padding:10px 0;
    border-radius:6px;
    font-weight:500;
    font-size:13px;
    transition:0.3s;
    text-align:center;
    width:100%;
}

.btn:hover{
    background:#333;
    transform:translateY(-1px);
}

.empty{
    background:#fff;
    padding:50px 30px;
    border-radius:12px;
    text-align:center;
    color:#666;
    box-shadow:0 1px 3px rgba(0,0,0,0.08);
}

.empty h2{
    font-size:22px;
    margin-bottom:15px;
    color:#000;
}

.empty p{
    font-size:14px;
    color:#888;
}

/* Badge opcional para serviços */
.servico-badge{
    background:#f8f9fa;
    border-left:3px solid #000;
    padding:8px;
    margin:10px 0;
    font-size:12px;
    color:#555;
    border-radius:4px;
    line-height:1.4;
    max-height:60px;
    overflow-y:auto;
}

/* Scrollbar personalizada */
.servico-badge::-webkit-scrollbar{
    width:4px;
}

.servico-badge::-webkit-scrollbar-track{
    background:#f1f1f1;
}

.servico-badge::-webkit-scrollbar-thumb{
    background:#888;
    border-radius:2px;
}

@media(max-width:768px){

    .header{
        padding:15px 20px;
        flex-direction:column;
        gap:12px;
        text-align:center;
    }

    .header h1{
        width:100%;
        font-size:20px;
    }

    .nav{
        width:100%;
        justify-content:center;
    }

    .search-form{
        flex-direction:column;
    }

    .search-form button{
        width:100%;
    }
    
    .grid{
        grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));
        gap:15px;
    }
    
    .card{
        padding:15px;
    }
    
    .preco{
        font-size:20px;
    }
}

@media(max-width:480px){
    .grid{
        grid-template-columns:1fr;
    }
}

</style>
</head>
<body>

<div class="header">

    <h1>Marketplace de Serviços</h1>

    <div class="nav">
        <a href="meus_pedidos.php">Meus Pedidos</a>
        <a href="logout.php">Sair</a>
    </div>

</div>

<div class="container">

    <div class="search-box">

        <form method="GET" class="search-form">

            <input 
                type="text" 
                name="busca" 
                placeholder="Buscar prestador ou serviço..."
                value="<?= isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : '' ?>"
            >

            <button type="submit">
                Buscar
            </button>

        </form>

    </div>

    <?php if($result->num_rows > 0): ?>

        <div class="grid">

            <?php while($row = $result->fetch_assoc()): ?>

                <div class="card">

                    <h2>
                        <?= htmlspecialchars($row['nome']) ?>
                    </h2>

                    <div class="servico-badge">
                        <strong> Serviços:</strong><br>
                        <?= htmlspecialchars(substr($row['descricao'], 0, 80)) . (strlen($row['descricao']) > 80 ? '...' : '') ?>
                    </div>

                    <div class="regiao">
                         <?= htmlspecialchars($regiao) ?>
                    </div>

                    <div class="preco">
                        R$ <?= number_format($row['preco_medio'], 2, ',', '.') ?>
                    </div>

                    <a 
                        href="prestador.php?id=<?= $row['id'] ?>" 
                        class="btn"
                    >
                        Ver Perfil
                    </a>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">
            <h2>Nenhum prestador encontrado</h2>
            <br>
            <p>
                Tenta outra busca. Sistema sem prestador é igual síndico sem grupo do WhatsApp:<br>
                paz demais pra ser verdade.
            </p>
        </div>

    <?php endif; ?>

</div>

</body>
</html>