<?php
include("config.php");

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validação básica
    if (empty($_POST['nome']) || empty($_POST['regiao']) || empty($_POST['cpf']) || empty($_POST['senha'])) {
        $erro = "Todos os campos são obrigatórios!";
    } else {
        $nome = trim($_POST['nome']);
        $regiao = trim($_POST['regiao']);
        $cpf = trim($_POST['cpf']);
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);


$cpf = trim($_POST['cpf']);
$cpf = preg_replace('/[^0-9]/', '', $cpf); 
        
        // Verificar se CPF já existe
        $check_stmt = $conn->prepare("SELECT id FROM condominios WHERE cpf = ?");
        $check_stmt->bind_param("s", $cpf);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $erro = "CPF já cadastrado!";
        } else {
            $stmt = $conn->prepare("INSERT INTO condominios (nome, regiao, cpf, senha) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nome, $regiao, $cpf, $senha);
            
            if ($stmt->execute()) {
                $mensagem = "Cadastro realizado com sucesso!";
                // Redirecionar após 2 segundos
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login_condominio.php';
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
    <title>Cadastro de Condomínio</title>
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
        border: 1px solid #e5e5e5;
        border-radius: 14px;
        width: 100%;
        max-width: 430px;
        overflow: hidden;
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
        color: var(--cor-principal);
        margin-bottom: 8px;
        letter-spacing: -1px;
    }

    .header p {
        font-size: 14px;
        color: #666;
    }

    .content {
        padding: 35px 30px;
    }

    .form-group {
        margin-bottom: 22px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--cor-principal);
    }

    .form-group input {
        width: 100%;
        padding: 14px;
        border: 1px solid #dcdcdc;
        border-radius: 10px;
        background: #fafafa;
        font-size: 14px;
        transition: 0.2s ease;
        outline: none;
        font-family: inherit;
        color: var(--cor-principal);
    }

    .form-group input:focus {
        border-color: var(--cor-principal);
        background: white;
    }

    .form-group input::placeholder {
        color: #999;
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
        text-align: center;
        border-top: 1px solid #ececec;
        padding-top: 20px;
    }

    .login-link a {
        color: var(--cor-principal);
        text-decoration: none;
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
        background: #111111;
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
    }
</style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cadastro de Condomínio</h1>
            <p>Preencha os dados abaixo para se cadastrar</p>
        </div>
        
        <div class="content">
            <?php if ($mensagem): ?>
                <div class="mensagem sucesso">
                    <span class="icone">✅</span> <?php echo $mensagem; ?>
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
                    <label> Nome do Condomínio</label>
                    <input type="text" name="nome" placeholder="Digite o nome do condomínio" 
                           value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label> Região</label>
                    <input type="text" name="regiao" placeholder="Digite a região" 
                           value="<?php echo isset($_POST['regiao']) ? htmlspecialchars($_POST['regiao']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label> CPF</label>
                    <input type="text" name="cpf" placeholder="Digite o CPF" 
                           value="<?php echo isset($_POST['cpf']) ? htmlspecialchars($_POST['cpf']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" placeholder="Digite a senha" required>
                </div>
                
                <button type="submit" class="btn-cadastrar">cadastrar Condomínio</button>
            </form>
            
            <div class="login-link">
                <a href="login_condominio.php"> Já tem cadastro? Faça login aqui</a>
            </div>
        </div>
    </div>
    
    <script>
        // Máscara para CPF (opcional)
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
        
        // Validação adicional antes de enviar
        document.getElementById('cadastroForm').addEventListener('submit', function(e) {
            const nome = document.querySelector('input[name="nome"]').value.trim();
            const regiao = document.querySelector('input[name="regiao"]').value.trim();
            const cpf = document.querySelector('input[name="cpf"]').value.trim();
            const senha = document.querySelector('input[name="senha"]').value;
            
            if (!nome || !regiao || !cpf || !senha) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos!');
                return false;
            }
            
            if (cpf.replace(/\D/g, '').length !== 11) {
                e.preventDefault();
                alert('CPF inválido! Deve conter 11 dígitos.');
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