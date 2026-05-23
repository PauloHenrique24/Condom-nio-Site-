<?php
include("config.php");

$condominios = $conn->query("SELECT * FROM condominios ORDER BY nome");

$mensagem = '';
$erro = '';

if ($_POST) {
    // Validação básica
    if (empty($_POST['condominio_id']) || empty($_POST['nome']) || empty($_POST['cpf']) || 
        empty($_POST['bloco']) || empty($_POST['apartamento']) || empty($_POST['telefone']) || 
        empty($_POST['senha'])) {
        $erro = "Todos os campos são obrigatórios!";
    } else {
        $condominio_id = $_POST['condominio_id'];
        $nome = trim($_POST['nome']);
        $cpf = trim($_POST['cpf']);
        $bloco = trim($_POST['bloco']);
        $ap = trim($_POST['apartamento']);
        $tel = trim($_POST['telefone']);
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

        $cpf = trim($_POST['cpf']);
$cpf = preg_replace('/[^0-9]/', '', $cpf); 
        
        // Verificar se CPF já existe neste condomínio
        $check_stmt = $conn->prepare("SELECT id FROM moradores WHERE cpf = ? AND condominio_id = ?");
        $check_stmt->bind_param("si", $cpf, $condominio_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $erro = "CPF já cadastrado neste condomínio!";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO moradores (condominio_id, nome, cpf, bloco, apartamento, telefone, senha)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("issssss", $condominio_id, $nome, $cpf, $bloco, $ap, $tel, $senha);
            
            if ($stmt->execute()) {
                $mensagem = "Morador cadastrado com sucesso!";
                // Redirecionar para página de login após 2 segundos
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login_morador.php';
                    }, 2000);
                </script>";
            } else {
                $erro = "Erro ao cadastrar: " . $conn->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Morador</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    :root {
        --cor-principal: #111111;
        --cor-secundaria: #f5f5f5;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: var(--cor-secundaria);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        color: var(--cor-principal);
    }

    .container {
        background: white;
        border-radius: 14px;
        border: 1px solid #e5e5e5;
        overflow: hidden;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .header {
        padding: 35px 30px 25px;
        text-align: center;
        border-bottom: 1px solid #ececec;
        background: white;
    }

    .header h1 {
        font-size: 28px;
        font-weight: 700;
        letter-spacing: -1px;
        color: var(--cor-principal);
        margin-bottom: 8px;
    }

    .header p {
        font-size: 14px;
        color: #666;
    }

    .content {
        padding: 35px 30px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--cor-principal);
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 14px;
        border: 1px solid #dcdcdc;
        border-radius: 10px;
        background: #fafafa;
        font-size: 14px;
        outline: none;
        transition: 0.2s ease;
        font-family: inherit;
        color: var(--cor-principal);
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: var(--cor-principal);
        background: white;
    }

    .form-group input::placeholder {
        color: #999;
    }

    .row-2cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .btn-cadastrar {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 10px;
        background: var(--cor-principal);
        color: white;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .btn-cadastrar:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .login-link {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #ececec;
        text-align: center;
    }

    .login-link a {
        text-decoration: none;
        color: var(--cor-principal);
        font-size: 14px;
        font-weight: 600;
        transition: 0.2s ease;
    }

    .login-link a:hover {
        opacity: 0.7;
    }

    .mensagem {
        padding: 14px;
        border-radius: 10px;
        margin-bottom: 20px;
        text-align: center;
        font-size: 14px;
    }

    .mensagem.sucesso {
        background: #f3f3f3;
        color: var(--cor-principal);
        border: 1px solid #dcdcdc;
    }

    .mensagem.erro {
        background: var(--cor-principal);
        color: white;
    }

    .icone {
        margin-right: 5px;
    }

    @media (max-width: 480px) {
        .content {
            padding: 25px 20px;
        }

        .header {
            padding: 28px 20px;
        }

        .header h1 {
            font-size: 24px;
        }

        .row-2cols {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cadastro de Morador</h1>
            <p>Preencha os dados abaixo para se cadastrar</p>
        </div>
        
        <div class="content">
            <?php if ($mensagem): ?>
                <div class="mensagem sucesso">
                    <span class="icone"></span> <?php echo $mensagem; ?>
                    <div style="font-size: 12px; margin-top: 5px;">Redirecionando para o login...</div>
                </div>
            <?php endif; ?>
            
            <?php if ($erro): ?>
                <div class="mensagem erro">
                    <span class="icone"></span> <?php echo $erro; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="cadastroForm">
                <div class="form-group">
                    <label>Selecione o Condomínio</label>
                    <select name="condominio_id" required>
                        <option value="">Selecione um condomínio...</option>
                        <?php while ($c = $condominios->fetch_assoc()) { ?>
                            <option value="<?= $c['id'] ?>" <?php echo (isset($_POST['condominio_id']) && $_POST['condominio_id'] == $c['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($c['nome']) ?> - <?= htmlspecialchars($c['regiao']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" placeholder="Digite seu nome completo" 
                           value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>CPF</label>
                    <input type="text" name="cpf" placeholder="Digite seu CPF" 
                           value="<?php echo isset($_POST['cpf']) ? htmlspecialchars($_POST['cpf']) : ''; ?>" required>
                </div>
                
                <div class="row-2cols">
                    <div class="form-group">
                        <label>Bloco</label>
                        <input type="text" name="bloco" placeholder="Ex: A, B, C" 
                               value="<?php echo isset($_POST['bloco']) ? htmlspecialchars($_POST['bloco']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Apartamento</label>
                        <input type="text" name="apartamento" placeholder="Número do apto" 
                               value="<?php echo isset($_POST['apartamento']) ? htmlspecialchars($_POST['apartamento']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" placeholder="(00) 00000-0000" 
                           value="<?php echo isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" placeholder="Digite sua senha" required>
                </div>
                
                <button type="submit" class="btn-cadastrar">Cadastrar Morador</button>
            </form>
            
            <div class="login-link">
                <a href="login_morador.php">Já tem cadastro? Faça login aqui</a>
            </div>
        </div>
    </div>
    
    <script>
        // Máscara para CPF
        document.querySelector('input[name="cpf"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                if (value.length > 9) {
                    value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                } else if (value.length > 6) {
                    value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3');
                } else if (value.length > 3) {
                    value = value.replace(/(\d{3})(\d{3})/, '$1.$2');
                } else if (value.length > 0) {
                    value = value.replace(/(\d{3})/, '$1');
                }
                e.target.value = value;
            }
        });
        
        // Máscara para Telefone
        document.querySelector('input[name="telefone"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                if (value.length > 10) {
                    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else if (value.length > 6) {
                    value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                } else if (value.length > 2) {
                    value = value.replace(/(\d{2})(\d{1,5})/, '($1) $2');
                } else if (value.length > 0) {
                    value = value.replace(/(\d{2})/, '($1');
                }
                e.target.value = value;
            }
        });
        
        // Validação antes de enviar
        document.getElementById('cadastroForm').addEventListener('submit', function(e) {
            const condominio = document.querySelector('select[name="condominio_id"]').value;
            const nome = document.querySelector('input[name="nome"]').value.trim();
            const cpf = document.querySelector('input[name="cpf"]').value.trim();
            const bloco = document.querySelector('input[name="bloco"]').value.trim();
            const apartamento = document.querySelector('input[name="apartamento"]').value.trim();
            const telefone = document.querySelector('input[name="telefone"]').value.trim();
            const senha = document.querySelector('input[name="senha"]').value;
            
            if (!condominio || !nome || !cpf || !bloco || !apartamento || !telefone || !senha) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos!');
                return false;
            }
            
            if (cpf.replace(/\D/g, '').length !== 11) {
                e.preventDefault();
                alert('CPF inválido! Deve conter 11 dígitos.');
                return false;
            }
            
            if (telefone.replace(/\D/g, '').length < 10 || telefone.replace(/\D/g, '').length > 11) {
                e.preventDefault();
                alert('Telefone inválido! Digite um número válido com DDD.');
                return false;
            }
            
            if (senha.length < 4) {
                e.preventDefault();
                alert('A senha deve ter no mínimo 4 caracteres!');
                return false;
            }
        });
    </script>
</body>
</html>